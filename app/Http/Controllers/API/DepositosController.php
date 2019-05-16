<?php

namespace App\Http\Controllers\API;

use App\Deposito;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepositosController extends Controller
{
    public function index(Request $request)
    {
        $q = Deposito::select(
            'deposito',
            'nome'
        )->orderBy('deposito', 'asc');
        if ($request->has('paraTransferencia')) {
            $q = $q->paraTransferencia();
        } else {
            $q = $q->disponible();
        }
        return $q->get();
    }
}
