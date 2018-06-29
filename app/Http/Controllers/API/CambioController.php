<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Cambio;

class CambioController extends Controller
{
    public function index()
    {
        return Cambio::defaultSelect()->first();
    }
}
