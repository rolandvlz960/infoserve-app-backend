<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Printer;

class PrintersController extends Controller
{
    public function index()
    {
        return Printer::select(
            "CODIGO",
            "NOME",
            "IP",
            "PORTA"
        )->where('sr_deleted', '=', 0)
        ->get();
    }
}
