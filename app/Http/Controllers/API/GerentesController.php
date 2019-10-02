<?php

namespace App\Http\Controllers\API;

use App\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GerentesController extends Controller
{
    public function index()
    {
        return Usuario::select(
            'NUMERO',
            'NOME'
        )
            ->gerente()
            ->where('sr_deleted', '<>', 'T')
            ->get();
    }
}
