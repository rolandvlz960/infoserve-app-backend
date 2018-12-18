<?php

namespace App\Http\Controllers\API;

use App\Deposito;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepositosController extends Controller
{
    public function index()
    {
        return Deposito::select(
            'deposito',
            'nome'
        )->disponible()
            ->orderBy('deposito', 'asc')
            ->get();
    }
}
