<?php

namespace App\Http\Controllers\API;

use App\CondicaoPagamento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Cliente;
use Illuminate\Support\Facades\DB;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        $res = Cliente::defaultSelect()
            ->orderBy('NOME', 'ASC');
        if (!$request->has('get-all')) {
            $page = $request->has('page') ? $request->page : 1;
            $res = $res->nacionalidad($request->nacionalidad)
                ->filtrar($request->cliente)
                ->limit(20)
                ->skip(20 * ($page - 1));
        } else {
            if (DB::table('fil120')->select('clivendapp')->first()->clivendapp == 'S') {
                $res = $res->delVendedor($request->vendedor);
            }
        }
        $res = $res->get();
        foreach ($res as $cliente) {
            $condPagamentos = CondicaoPagamento::deCliente($cliente->cliente)->pluck('codcondpag');
	    // iOS TEST
	    $cliente->condV = 1;
            foreach ($condPagamentos as $condPagamento) {
                $condAttrName = "cond" . $condPagamento;
                $cliente->$condAttrName = 1;
            }
        }
        return [
            'data' => $res,
            'query' => $request->cliente
        ];
    }
}
