<?php

namespace App\Http\Controllers\API;

use App\Cliente;
use App\CondicaoPagamento;
use App\Http\Controllers\Controller;
use App\Pedido;
use App\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidosController extends Controller
{
    public function send(Request $request)
    {
        $return = null;
        DB::transaction(function () use ($request, &$return) {
            $nota = Pedido::max('mobiped');
            if (is_null($nota)) {
                $nota = 0;
            }
            $nota++;
            Log::info('REQ: ' . json_encode($request->all()));
            foreach ($request->pedidos as $pedido) {
                $condPagamento = CondicaoPagamento::deCliente($pedido['cliente'])->first();
                if (!is_null($condPagamento)) {
                    $condPagamento = $condPagamento->codcondpag;
                } else {
                    $condPagamento = 0;
                }
                Log::info('PROD: ' . $pedido['id_producto']);
                $cliente = Cliente::select(
                    'cliente',
                    'digito',
                    'cli_pessoa',
                    'nome',
                    'ruc',
                    'cidade',
                    'rg',
                    'fone',
                    'endereco'
                )->where('cliente', '=', $pedido['cliente'])->first();
                Log::info('CLIENTE: ' . json_encode($cliente));
                $produto = Producto::select(
                    'produto',
                    'digito',
                    'composto'
                )->where('produto', '=', $pedido['id_producto'])->first();
                Pedido::create([
                    'vendedor' => $pedido['id_vendedor'],
                    'mobiped' => $nota,
                    'mobiid' => $nota . '-' . $pedido['id_vendedor'],
                    'data' => DB::raw('NOW()'),
                    'hora' => DB::raw("DATE_FORMAT(NOW(), '%H:%i:%s')"),
                    'cliente' => $cliente->cliente,
                    'nome' => $cliente->nome,
                    'endereco' => $cliente->endereco,
                    'telefone' => $cliente->fone,
                    'ruc' => $cliente->ruc,
                    'produto' => $produto->digito,
                    'quantidade' => $pedido['cantidad'],
                    'preco' => $pedido['precio'],
                    'prazo' => $condPagamento,
                    'prodkit' => $produto->composto,
                    'operacao' => $pedido['tipo'],
                    'localizacion' => substr($pedido['latitud'], 0, 9) . "," . substr($pedido['longitud'], 0, 10),
                ]);
            }
            $return = $nota;
        });
        if (!is_null($return)) {
            return [
                'status' => 'success',
                'nota' => $return
            ];
        } else {
            return [
                'status' => 'fail'
            ];
        }
    }
}
