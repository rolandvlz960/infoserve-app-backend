<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Cliente;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->has('page') ? $request->page : 1;
        $res = Cliente::defaultSelect()
            ->nacionalidad($request->nacionalidad)
            ->filtrar($request->cliente)
            ->orderBy('NOME', 'ASC')
            ->limit(20)
            ->skip(20 * ($page - 1))
            ->get();
        return [
            'data' => $res,
            'query' => $request->cliente
        ];
    }
}
