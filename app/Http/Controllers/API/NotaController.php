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
            'hora' => ''
        ];
        DB::transaction(function() use($request, &$resultNota) {
            // Esto es nota = nota + 1
            DB::unprepared("lock tables fil120 write");
            $nota = Nota::max('nota') + 1;
            $resultNota['nota'] = $nota;
            DB::insert("INSERT INTO `fil120` VALUES ($nota,9,1110,77,0,0,'2016-12-31',NULL,'09/2016','2015-04-12','',0,13,34,27,213,0,0.000000,0,1,'2008-01-08','','','','×Îçþè\nùß1Ä§','?í7•#ºòÔ’gTÄkÑg¬¬','¯ö÷î€rÙßg','','','','','','',215,0,6,0,0,27,0,95,0,24,0,8,10,0,0,2,3,0,0,0,0,0,0,0,17,0,0,0,0,0,0,'','','','','',0.00,'+dep01',0,'+dep01+dep02','+dep02','+dep10','+dep15','+dep20',0,0,'N','N','N','N','N','N','N',13,0,'','','','N','N','N','N','','','','','','','','S','','','','','','','','','','N','','','','','','','','','','','','','',0,'','','','','','','','','','','',0,'','','','','','','','',0,'',0.000,NULL,NULL,8000.00,0.00,0,0,'','','',0.00,2,0,'','N','','',0,1,0,0,0,0,0,'','','',0,'',0,'','',0,'','','','',0,'S',0,0,0,0,'','','','',0,0,'','','','',0,0,'','',0,'','',0,'',1,'','','','','','','','',0,0,0,0,'','',null,'','1.39.00.35F',0,0,0,'',0,0,0,'','','','','','','',0,0,0,0,'','','','',0,'','','');", []);
            DB::unprepared("unlock tables");
            
            // Solo para tests
            // $nota = Nota::find(2288);
            $cliente = null;
            // Log::info('QERY'. json_encode($request->all()));
            $turista = $request->turista == "n";
            if ($turista) {
                $cliente = Cliente::where('cliente', $request->cliente)->first();
                Log::info('-----CLIENTE-------' . json_encode($cliente));
            }
            foreach($request->items as $item) {
                $datos = [
                    'vendedor' => $request->vendedor,
                    'mobiped' => $nota,
                    'data' => DB::select("SELECT ADDDATE( encerra, INTERVAL 1 DAY) as data from fil120 order by data desc limit 1;")[0]->data,
                    'hora' => DB::select("SELECT TIME_FORMAT(CURTIME(), '%h:%i:%s') AS hora")[0]->hora,
                    'cliente' => $request->cliente,
                    'nome' => !$turista ? $request->nombre : $cliente->NOME,
                    'endereco' => !$turista ? $request->direccion : $cliente->ENDERECO,
                    'cidade' => !$turista ? $request->ciudad : $cliente->CIDADE,
                    'telefone' => !$turista ? $request->telefono : $cliente->FONE,
                    'ruc' => !$turista ? $request->ruc : $cliente->RUC,
                    'produto' => $item['producto'],
                    'quantidade' => $item['cantidad'],
                    'preco' => $item['precio']
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
                Log::info("ITEM:" . json_encode($item));
                $resultNota['fecha'] = $item->data;
                $resultNota['hora'] = $item->hora;

                Producto::where('produto', $item['producto'])->update(['quant_pend' => DB::raw('quant_pend + 1')]);
            }
            if ($request->has('printerIp') && $request->printerIp !== '') {
                $clienteNota = (!$turista ? $request->nombre : $cliente->NOME);
                $hora = $resultNota['fecha'] . ' ' . $resultNota['hora'];
                Log::info('----HORA STRING---' . $hora);
                $datetime = Carbon::createFromFormat('Y-m-d H:i:s', $hora);
                $this->printNota(
                    $request->printerIp,
                    $request->printerPort,
                    $resultNota['nota'],
                    $datetime->format('Y-m-d H:i'),
                    $request->items,
                    $clienteNota
                );
            }
        });
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
            $request->cliente
        );
        return [
            'msg' => 'OK'
        ];
    }

    /**
     * Imprimir nota de forma remota.
     */
    private function printNota($printerIp, $printerPort, $nota, $fecha, $receivedItems, $cliente)
    {
        Log::info('-------FECHA HORA-----' . $fecha);
        $connector = new NetworkPrintConnector($printerIp, $printerPort);
        $printer = new Printer($connector);
        $total = 0;
        $items = [];
        $sigla = Moneda::first()->SIGLA;
        $datetime = Carbon::createFromFormat("Y-m-d H:i", $fecha);
        foreach($receivedItems as $item) {
            $items[] = [
                Producto::select('descricao')->where('produto', $item['producto'])->first()->descricao,
                $item['cantidad'] . ' x ' . $sigla . " " . $item['precio'] . "        " . $sigla . ' ' . ($item['precio'] * $item['cantidad'])
            ];
            $total = $total + ( $item['precio'] * $item['cantidad'] );
        }
        $printer->text("                       \n");
        $printer->text("                       \n");
        $printer->text("                       \n");
        $printer->text("NUMERO DE BOLETA: " . $nota . "T\n");
        $printer->text("CLIENTE: " . $cliente . "\n");
        $printer->text("TOTAL: " . $sigla . " " . $total . "\n");
        $printer->text("FECHA: " . $datetime->format('d/m/Y H:i') . "\n");
        $printer->text("------------------------------------------------\n");
        foreach($items as $item) {
            $printer->text($item[0] . "\n");
            $printer->text($item[1] . "\n");
        }
        $printer->text("                       \n");
        $printer->text("                       \n");
        $printer->text("                       \n");
        $printer->text("                       \n");
        $printer->text("                       \n");
        $printer->cut();
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
        )->where('ruc', '=', $doc)->orderBy('sr_recno', 'desc')->first();
    }
}
