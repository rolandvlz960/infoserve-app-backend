<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

use DB;
use App\Nota;
use App\Usuario;
use App\Producto;
use App\ItemNota;
use App\Cliente;
use App\Moneda;

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
        DB::transaction(function() use($request, &$resultNota) {
            // Esto es nota = nota + 1
            // DB::unprepared("lock tables fil120 write");
            $nota = Nota::max('nota') + 1;
            $resultNota['nota'] = $nota;
            DB::insert("UPDATE `fil120` SET NOTA = ?;", [$nota]);
            // DB::unprepared("unlock tables");
            
            // Solo para tests
            // $nota = Nota::find(2288);
            $cliente = null;
            // Log::info('QERY'. json_encode($request->all()));
            $turista = $request->turista == "n";
            if ($turista) {
                $cliente = Cliente::where('cliente', $request->cliente)->first();
                Log::info("---CLIENTE---" . json_encode($cliente));
            } else {
                $cliente = Cliente::select('digito')->where('cliente', $request->cliente)->first();
            }
            $vendedor = Usuario::select('deposito')->find($request->vendedor);
            foreach($request->items as $item) {
                $datos = [
                    'vendedor' => $request->vendedor,
                    'mobiped' => $nota,
                    'notas' => $nota,
                    'mobiid' => 0,
                    'mobicli' => 0,
                    'data' => DB::select("SELECT ADDDATE( encerra, INTERVAL 1 DAY) as data from fil120 order by data desc limit 1;")[0]->data,
                    'hora' => DB::select("SELECT TIME_FORMAT(CURTIME(), '%h:%i:%s') AS hora")[0]->hora,
                    'cliente' => $request->cliente,
                    'clinovo' => $turista ? 'S' : 'N',
                    'nome' => !$turista ? $request->nombre : $cliente->NOME,
                    'endereco' => !$turista ? $request->direccion : $cliente->ENDERECO,
                    'codcidade' => 0,
                    'cidade' => !$turista ? $request->ciudad : $cliente->CIDADE,
                    'telefone' => !$turista ? $request->telefono : $cliente->FONE,
                    'ruc' => !$turista ? '' : $cliente->RUC,
                    'doc' => !$turista ? $request->doc : $cliente->RG,
                    'deposito' => isset($vendedor->DEPOSITO) ? $vendedor->DEPOSITO : $vendedor->deposito,
                    'produto' => $item['producto'],
                    'prodkit' => 'N',
                    'quantidade' => $item['cantidad'],
                    'preco' => $item['precio'],
                    'prazo' => 0,
                    'ref_opera' => 0,
                    'autoriza' => 0,
                    'finalizar' => 0,
                    'sr_deleted' => 0,
                    // 'fotodoc1',
                    // 'fotodoc2'
                ];
                if ($request->has('fotodoc1')) {
                    $datos['fotodoc1'] = base64_decode($request->fotodoc1);
                }
                if ($request->has('fotodoc2')) {
                    $datos['fotodoc2'] = base64_decode($request->fotodoc2);
                }
                $item = ItemNota::create($datos);
                $resultNota['fecha'] = $item->data;
                $resultNota['hora'] = $item->hora;

                Producto::where('produto', $item['producto'])->update(['quant_pend' => DB::raw('quant_pend + 1')]);
            }
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
                    Usuario::select('numero', 'nome', 'deposito')->find($request->vendedor)
                );
            }
        });
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
        $this->printNota(
            $request->printerIp,
            $request->printerPort,
            $request->nota,
            $request->fecha,
            $request->items,
            $request->cliente,
            Usuario::select('numero', 'nome', 'deposito')->find($request->usuario)
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
        $usuario
    ) {
        $connector = new NetworkPrintConnector($printerIp, $printerPort);
        $printer = new Printer($connector);
        $total = 0;
        $items = [];
        $cant = 0;
        $mensagens = DB::table('FIL050')->select('MENSAGEM_1', 'MENSAGEM_2', 'NTRESTABLET')->first();
        if ($mensagens->NTRESTABLET == 'S') {
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("Numero: " . $nota . "\n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->close();
        } else {
            $sigla = Moneda::first()->SIGLA;
            $datetime = Carbon::createFromFormat("Y-m-d H:i", $fecha);
            $mensagens = DB::table('FIL050')->select('MENSAGEM_1', 'MENSAGEM_2')->first();
            foreach($receivedItems as $item) {
                $res = Producto::select('digito', 'descricao')->where('produto', $item['producto'])->first();
                $items[] = [
                    $res->digito . "    " . $res->descricao,
                    $item['cantidad'] . ' x ' . $sigla . " " . number_format($item['precio'], 2) . "    " . $sigla . ' ' . number_format($item['precio'] * $item['cantidad'], 2)
                ];
                $cant += $item['cantidad'];
                $total = $total + ( $item['precio'] * $item['cantidad'] );
            }
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("------------------------------------------------\n");
            $printer->text("VENTAS\n");
            $printer->text("PEDIDO DE VENTAS\n");
            $printer->text("Fecha de emision: " . $datetime->format('d/m/Y H:i') . "\n");
            $printer->text("Cliente: " . $cliente . "\n");
            $printer->text("Usuario: " . $usuario->numero . "-" . $usuario->nome . "\n");
            $printer->text("Numero: " . $nota . "\n");
            $printer->text("Deposito: " . $usuario->deposito . "\n");
            $printer->text("================================================\n");
            $printer->text("Codigo    Descrip.\n");
            $printer->text("Cant    Precio    Total\n");
            foreach($items as $item) {
                $printer->text($item[0] . "\n");
                $printer->text($item[1] . "\n");
            }
            $printer->text("------------------------------------------------\n");
            $printer->text("Total: " . $sigla . " " . number_format($total, 2) . "    Items: " . $cant . "\n");
            $printer->text("------------------------------------------------\n");
            $printer->text("Total: " . $sigla . " " . number_format($total, 2) . "\n");
            $printer->text("Desc: 0\n");
            $printer->text("Total: " . $sigla . " " . number_format($total, 2) . "\n");
            $printer->text("================================================\n");
            $printer->text($mensagens->MENSAGEM_1 . "\n");
            $printer->text($mensagens->MENSAGEM_2 . "\n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->text("                       \n");
            $printer->feed(1);
            $this->cut($printerIp, $printerPort);
            $printer->close();
        }
    }

    private function cut($host, $port)
    {
        $connector = new \Posprint\Connectors\Network($host, $port);
        $printer = new \Posprint\Printers\Bematech($connector);
        $printer->lineFeed(2);
        $printer->cut();
        $printer->send();
        $printer->close();
    }

    /**
     * Búsqueda de datos de formulario por num de cédula
     */
    public function findByDoc($doc)
    {
        return ItemNota::select(
            'nome',
            'endereco',
            'cidade',
            'telefone'
        )->where('doc', '=', $doc)->orderBy('sr_recno', 'desc')->first();
    }
}
