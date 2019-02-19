<?php

namespace App\Http\Controllers\API;

use App\Producto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
            'descricao'
        )
            ->buscarCodigoBarra($request)
            ->get();
    }

    public function save(Request $request)
    {
        return response(200);
    }
}
