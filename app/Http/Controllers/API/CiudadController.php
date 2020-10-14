<?php

namespace App\Http\Controllers\API;

use App\Ciudad;
use App\Pais;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CiudadController extends Controller
{
    public function paises()
    {
        return [
            'data' => Pais::all(),
        ];
    }

    public function ciudades()
    {
        return [
            'data' => Ciudad::all(),
        ];
    }

}
