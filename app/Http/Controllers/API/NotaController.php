<?php

namespace App\Http\Controllers\API;

use App\Bloqueo;
use App\Ciudad;
use App\FotoTurista;
use App\Jobs\PrintNota;
use App\Pais;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Intervention\Image\Facades\Image;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

use Illuminate\Support\Facades\DB;
use App\Nota;
use App\Usuario;
use App\Producto;
use App\ItemNota;
use App\Cliente;
use App\Moneda;
use Posprint\Printers\Bematech;

class NotaController extends Controller
{
    public function save(Request $request)
    {
        $resultNota = [
            'nota' => '',
            'fecha' => '',
            'hora' => '',
            'mensagem_1' => '',
            'mensagem_2' => '',
            'resumida' => ''
        ];

        $nota = null;

        DB::transaction(function() use (&$nota) {
            Nota::increment('NOTA', 1);
            $nota = Nota::max('nota');
        });
        $data = DB::select("SELECT ADDDATE( encerra, INTERVAL 1 DAY) as data from fil120 order by data desc limit 1;")[0]->data;

        $cliente = null;
        // Log::info('QERY'. json_encode($request->all()));
        $turista = $request->turista == "n";
        if ($turista) {
            $cliente = Cliente::where('cliente', $request->cliente)->first();
        } else {
            $cliente = Cliente::select('digito')->where('cliente', $request->cliente)->first();
        }

        $vendedor = json_decode(json_encode(Usuario::select('numero', 'nome', 'deposito')->find($request->vendedor)));

        DB::transaction(function() use($request, &$resultNota, $nota, $data, $turista, $cliente) {
            // Esto es nota = nota + 1

            $resultNota['nota'] = $nota;

            // Solo para tests
            // $nota = Nota::find(2288);

            $hora = DB::select("SELECT TIME_FORMAT(CURTIME(), '%h:%i:%s') AS hora")[0]->hora;

            $codCidade = $request->has('codciudad') ? $request->codciudad : 0;

            foreach($request->items as $item) {
                Log::info("item: " . json_encode($item));
                $producto = Producto::select('COMPOSTO')->where('produto', '=', $item['producto'])->first();
                $datos = [
                    'vendedor' => $request->vendedor,
                    'mobiped' => $nota,
                    'notas' => $nota,
                    'mobiid' => 0,
                    'mobicli' => 0,
                    'data' => $data,
                    'hora' => $hora,
                    'cliente' => $request->cliente,
                    'clinovo' => $turista ? 'S' : 'N',
                    'nome' => !$turista ? $request->nombre : $cliente->NOME,
                    'endereco' => !$turista ? $request->direccion : $cliente->ENDERECO,
                    'codcidade' => $codCidade,
                    'cidade' => !$turista ? $request->ciudad ?? '' : $cliente->CIDADE,
                    'telefone' => !$turista ? $request->telefono : $cliente->FONE,
                    'ruc' => !$turista ? '' : $cliente->RUC,
                    'doc' => !$turista ? $request->doc : $cliente->RG,
                    'deposito' => (int) $request->deposito,
                    'produto' => $item['producto'],
                    'prodkit' => $producto->COMPOSTO,
                    'quantidade' => $item['cantidad'],
                    'preco' => $item['precio'],
                    'prazo' => 0,
                    'ref_opera' => 0,
                    'autoriza' => 0,
                    'finalizar' => 0,
                    'sr_deleted' => 0,
                ];
                if (env('USE_FIL154_PHOTOS', false)) {
                    if (
                        $request->has('fotodoc1') && $request->fotodoc1 != '' && $request->fotodoc1 != null &&
                        $request->has('fotodoc2') && $request->fotodoc2 != '' && $request->fotodoc2 != null
                    ) {
                        FotoTurista::create([
                            'rg' => !$turista ? $request->doc : $cliente->RG,
                            'foto1' => base64_decode($request->fotodoc1),
                            'foto2' => base64_decode($request->fotodoc2),
                            'cliente' => $request->cliente,
                            'usuario' => $request->vendedor,
                            'flag' => '',
                        ]);
                    }
                } else {
                    if ($request->has('fotodoc1')) {
                        $datos['fotodoc1'] = base64_decode($request->fotodoc1);
                    }
                    if ($request->has('fotodoc2')) {
                        $datos['fotodoc2'] = base64_decode($request->fotodoc2);
                    }
                }
                $itemNota = ItemNota::create($datos);
                $resultNota['fecha'] = $itemNota->data;
                $resultNota['hora'] = $hora;

                Producto::where('produto', $itemNota['producto'])->update(['quant_pend' => DB::raw('quant_pend + 1')]);

                if (array_key_exists('idbloq', $item)) {
                    Log::info("Id bloq: " . $item['idbloq']);
                    Bloqueo::where('idbloq', $item['idbloq'])
                        ->update([
                            'horafim' => $hora,
                        ]);
                }
            }

        });

        if ($request->has('printerIp') && $request->printerIp !== '') {
            $clienteNota = (!$turista ? $cliente->digito . '-' . strtoupper($request->nombre) : $cliente->DIGITO . '-'  . strtoupper($cliente->NOME));
            $hora = $resultNota['fecha'] . ' ' . $resultNota['hora'];
            $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $hora);
            $this->printNota(
                $request->printerIp,
                $request->printerPort,
                $resultNota['nota'] . "T",
                $datetime->format('Y-m-d H:i'),
                $request->items,
                $clienteNota,
                $vendedor,
                $request->deposito
            );
        }

        $mensagens = DB::table('FIL050')->select('MENSAGEM_1', 'MENSAGEM_2', 'NTRESTABLET')->first();
        $resultNota['mensagem_1'] .= $mensagens->MENSAGEM_1;
        $resultNota['mensagem_2'] .= $mensagens->MENSAGEM_2;
        $resultNota['resumida'] .= $mensagens->NTRESTABLET;
        $resultNota['nota'] .= "T";
        return $resultNota;
    }

    /**
     * Recepción e impresión de nota de forma remota.
     */
    public function reprint(Request $request)
    {
        $idVendedor = $request->has('vendedor') ? $request->vendedor : $request->usuario;
        $usuario = json_decode(json_encode(Usuario::select('numero', 'nome', 'deposito')->find($idVendedor)));
        $this->printNota(
            $request->printerIp,
            $request->printerPort,
            $request->nota,
            $request->fecha,
            $request->items,
            $request->cliente,
            $usuario,
            $request->deposito
        );
        return [
            'msg' => 'OK'
        ];
    }

    /**
     * Imprimir nota de forma remota.
     */
    private function printNota(
        $printerIp,
        $printerPort,
        $nota,
        $fecha,
        $receivedItems,
        $cliente,
        $usuario,
        $deposito
    ) {
        PrintNota::dispatch(
            $printerIp,
            $printerPort,
            $nota,
            $fecha,
            $receivedItems,
            $cliente,
            $usuario,
            $deposito
        );
    }

    /**
     * Búsqueda de datos de formulario por num de cédula
     */
    public function findByDoc($doc)
    {
        $item = ItemNota::select(
            'nome',
            'endereco',
            'codcidade',
            'cidade',
            'telefone'
        )->where('doc', '=', $doc)->orderBy('sr_recno', 'desc')->first();
        if (!is_null($item) && $item->codcidade != 0) {
            $ciudad = Ciudad::select('id_pais')->whereCodigo($item->codcidade)->first();
            if (!is_null($ciudad)) {
                $item->codpais = $ciudad->id_pais;
            }
        }

        return $item;
    }

    public function fotodocByDoc($doc, $num)
    {
        $field = 'fotodoc' . $num;
        $item = ItemNota::select(
            $field
        )->where('doc', '=', $doc)->orderBy('sr_recno', 'desc')->first();
        if (!is_null($item)) {
            $image = Image::make($item->$field);

            return $image->response('data-url');
        }
        return '';
    }
}
