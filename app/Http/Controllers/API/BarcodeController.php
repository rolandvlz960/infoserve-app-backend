<?php

namespace App\Http\Controllers\API;

use App\Colecta;
use App\Producto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        Log::info('CODIGO DE BARRA: ' . $request->q);
        $dep = $request->has('dep') ? $request->dep : '01';
        $block = $request->has('block');
        $productos = Producto::select(
            'produto',
            'digito',
            'referencia',
            'descricao',
            'subrefere',
            'subrefer01'
        )
            ->buscarCodigoBarra($request);
        if ($block) {
            $productos = $productos->where("dep$dep", '>', 0)
                ->whereRaw("dep$dep-bloq_dep$dep >= 1");
        }
        $productos = $productos->get();
        if ($block) {
            Producto::whereIn('produto', $productos->pluck('produto')->toArray())
                ->update([
                    "bloq_dep$dep" => DB::raw("bloq_dep$dep + 1"),
                    'bloqapp' => DB::raw('bloqapp + 1')
                ]);
        }
        return $productos;
    }

    public function save(Request $request)
    {
        $transactionOk = false;
        DB::transaction(function() use($request, &$transactionOk) {
            $ultimaNota = Colecta::where('OPERACAO', '=', $request->operacion)->orderBy('NOTA', 'desc')->first();
            $numNota = 1;
            if (!is_null($ultimaNota)) {
                $numNota = $ultimaNota->NOTA + 1;
            }
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    Colecta::create([
                        'NOTA' => $numNota,
                        'PRODUTO' => $item['producto'],
                        'USUARIO' => $request->usuario,
                        'DEPOSITO' => $request->deposito,
                        'DESTINO' => $request->depositoTo,
                        'OPERACAO' => $request->operacion,
                        'QUANTIDADE' => $item['cantidad'],
                        'DATA' => DB::raw('NOW()'),
                        'HORA' => DB::raw("DATE_FORMAT(NOW(), '%H:%i:%s')"),
                    ]);
                }
            }
            $transactionOk = true;
        });
        if ($transactionOk) {
            return response(200);
        }
        return response(500);
    }
}
