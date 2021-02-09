<?php

namespace App\Http\Controllers\API;

use App\FotoTurista;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FotoClienteController extends Controller
{
    const PHOTO_REQUEST_TABLE = 'fil020';

    public function checkFotoObligatoria(Request $request)
    {
        $fotoObligatoria = DB::table('fil050')->select('VLRFOTOCLI')->first()->VLRFOTOCLI > 0;
        if ($request->has('cliente')) {
            $sinFotoCliente = FotoTurista::where('cliente', $request->cliente)
                    ->where('foto1', '<>', '')
                    ->where('foto2', '<>', '')
                    ->count() == 0;
            $fotoObligatoria = $fotoObligatoria && $sinFotoCliente;
        }
        return [
            'data' => [
                'fotoObligatoria' => $fotoObligatoria
            ]
        ];
    }

    public function verificar($usuario)
    {
        $res = DB::table(self::PHOTO_REQUEST_TABLE)->select(
            'numero',
            'doccliefot',
            'codcliefot',
            'nomcliefot'
        )->where('numero', $usuario)->first();

        if (is_null($res)) {
            $res = [
                'numero' => null,
                'doccliefot' => null,
                'codcliefot' => null,
                'nomcliefot' => null
            ];
        }

        return [
            'data' => $res
        ];
    }

    public function actualizar($usuario, Request $request)
    {
        FotoTurista::create([
            'rg' => $request->doc,
            'foto1' => base64_decode($request->fotodoc1),
            'foto2' => base64_decode($request->fotodoc2),
            'cliente' => $request->cliente,
            'usuario' => $usuario,
        ]);

        DB::table(self::PHOTO_REQUEST_TABLE)
            ->where('numero', $usuario)
            ->update([
                'doccliefot' => null,
                'codcliefot' => null,
                'nomcliefot' => null
            ]);

        return [
            'status' => 'ok'
        ];
    }
}
