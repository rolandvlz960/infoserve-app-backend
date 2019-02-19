<?php

namespace App\Http\Controllers\API;

use App\Producto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BarcodeController extends Controller
{
    public function index(Request $request)
    {
        return Producto::select(
            'produto',
            'digito',
            'referencia',
            'descricao'
        )
            ->buscarCodigoBarra($request)
            ->first();
    }
}
