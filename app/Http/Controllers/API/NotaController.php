<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use DB;
use App\Nota;
use App\ItemNota;

class NotaController extends Controller
{
    public function save(Request $request)
    {
        DB::transaction(function() use($request) {
            // $nota = Nota::create([
            //     'nota' => DB::raw('nota + 1')
            // ]);
            
            // Solo para tests
            $nota = Nota::find(2288);
            $cliente = null;
            if ($request->turista == "n") {
                $cliente = Cliente::find($request->cliente);
            }
            foreach($request->items as $item) {
                $datos = [
                    'vendedor' => $request->vendedor,
                    'mobiped' => $nota->nota,
                    'data' => null,
                    'hora' => null,
                    'cliente' => $request->cliente,
                    'nome' => is_null($cliente) ? $request->nombre : $cliente->nome,
                    'endereco' => is_null($cliente) ? $request->direccion : $cliente->endereco,
                    'cidade' => is_null($cliente) ? $request->ciudad : $cliente->cidade,
                    'telefone' => is_null($cliente) ? $request->telefono : $cliente->fone,
                    'ruc' => is_null($cliente) ? $request->ruc : $cliente->ruc,
                    'produto' => $item['producto'],
                    'quantidade' => $item['cantidad'],
                    'preco' => $item['precio'],
                    // 'prazo',
                    // 'fotodoc1',
                    // 'fotodoc2'
                ];
                if ($request->has('fotodoc1')) {
                    $datos['fotodoc1'] = $request->fotodoc1;
                }
                if ($request->has('fotodoc2')) {
                    $datos['fotodoc2'] = $request->fotodoc2;
                }
                ItemNota::create($datos);
                // Actualizar fil010 con bloq_pend también
            }
        });
    }
}
