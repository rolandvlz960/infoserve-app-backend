<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Cliente;

class ClientesController extends Controller
{
    public function index(Request $request)
    {
        return Cliente::defaultSelect()
            ->nacionalidad($request->nacionalidad)
            ->filtrar($request->cliente)
            ->get();
    }
}
