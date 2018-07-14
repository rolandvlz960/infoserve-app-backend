<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use DB;
use App\Nota;
use App\Producto;
use App\ItemNota;
use App\Cliente;

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
                // $cliente = Cliente::where('cliente', $request->cliente)->first();
                Log::info('-----CLIENTE-------' . json_encode($cliente));
            }
            foreach($request->items as $item) {
                $datos = [
                    'vendedor' => $request->vendedor,
                    'mobiped' => $nota,
                    'data' => DB::select("SELECT ADDDATE( encerra, INTERVAL 1 DAY) as data from fil120 order by data desc limit 1;")[0]->data,
                    'hora' => DB::select("SELECT TIME_FORMAT(CURTIME(), '%h:%i:%s') AS hora")[0]->hora,
                    'cliente' => $request->cliente,
                    'nome' => (is_null($cliente) || $turista) ? $request->nombre : $cliente->NOME,
                    'endereco' => (is_null($cliente) || $turista) ? $request->direccion : $cliente->ENDERECO,
                    'cidade' => (is_null($cliente) || $turista) ? $request->ciudad : $cliente->CIDADE,
                    'telefone' => (is_null($cliente) || $turista) ? $request->telefono : $cliente->FONE,
                    'ruc' => (is_null($cliente) || $turista) ? $request->ruc : $cliente->RUC,
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
        });
        $resultNota['nota'] .= "T";
        return $resultNota;
    }
}
