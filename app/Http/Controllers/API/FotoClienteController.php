<?php

namespace App\Http\Controllers\API;

use App\FotoTurista;
use App\Usuario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class FotoClienteController extends Controller
{
    public function checkFotoObligatoria(Request $request)
    {
        $fotoObligatoria = DB::table('fil050')->select('VLRFOTOCLI')->first()->VLRFOTOCLI > 0;

        return [
            'data' => [
                'fotoObligatoria' => $fotoObligatoria
            ]
        ];
    }

    public function fotodocByCliente($doc, $num)
    {
        $field = 'foto' . $num;
        $item = FotoTurista::select(
            $field
        )->where('rg', '=', $doc)
            ->where('sr_deleted', '=', null)
            ->orderBy('sr_recno', 'desc')
            ->first();
        if (!is_null($item)) {
            $image = Image::make($item->$field);

            return $image->response('data-url');
        }
        return '';
    }

    public function verificar($usuario)
    {
        $res = Usuario::select(
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
        Log::info("Foto cliente actualizar req: " . json_encode($request->except('fotodoc1', 'fotodoc2')));
        if (!$request->has('dontUpdateFil154') || !$request->dontUpdateFil154) {
            FotoTurista::where('rg', $request->doc)
                ->where('sr_deleted', '=', null)
                ->update([
                    'sr_deleted' => 'T',
                    'usuariodel' => $usuario
                ]);
            FotoTurista::create([
                'rg' => is_null($request->doc) ? '' : $request->doc,
                'foto1' => base64_decode($request->fotodoc1),
                'foto2' => base64_decode($request->fotodoc2),
                'cliente' => $request->cliente,
                'usuario' => $usuario,
                'flag' => '',
                'usuariodel' => 0,
            ]);
        }

        if (!$request->has('dontUpdateFil020') || !$request->dontUpdateFil020) {
            Log::info('Update Fil020 present');
            Usuario::where('numero', $usuario)
                ->update([
                    'doccliefot' => '',
                    'codcliefot' => 0,
                    'nomcliefot' => ''
                ]);
        }


        return [
            'status' => 'ok'
        ];
    }
}
