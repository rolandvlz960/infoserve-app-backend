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
            'data' => Pais::select(
                'NOME',
                'CODIGO'
            )->get(),
        ];
    }

    public function ciudades(Request $request)
    {
        return [
            'data' => $request->has('id_pais')
                ? Ciudad::where('id_pais', $request->id_pais)->select('CODIGO', 'NOME', 'id_pais')->get()
                : Ciudad::select('CODIGO', 'NOME', 'id_pais')->get(),
        ];
    }

}
