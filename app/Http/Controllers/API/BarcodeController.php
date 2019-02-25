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
        return Producto::select(
            'produto',
            'digito',
            'referencia',
            'descricao',
            'subrefere',
            'subrefer01'
        )
            ->buscarCodigoBarra($request)
            ->get();
    }

    public function save(Request $request)
    {
        $transactionOk = false;
        DB::transaction(function() use($request, &$transactionOk) {
            $ultimaNota = Colecta::orderBy('NOTA', 'desc')->first();
            $numNota = 1;
            if (!is_null($ultimaNota)) {
                $numNota = $ultimaNota->NOTA + 1;
            }
            if ($request->has('items')) {
                foreach ($request->items as $item) {
                    Colecta::create([
                        'NOTA' => $numNota + 1,
                        'PRODUTO' => $item['producto'],
                        'USUARIO' => $request->usuario,
                        'DEPOSITO' => $request->deposito,
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
