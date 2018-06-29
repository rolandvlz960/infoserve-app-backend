<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Moneda;

class MonedasController extends Controller
{
    public function index()
    {
        return Moneda::defaultSelect()->get();
    }
}
