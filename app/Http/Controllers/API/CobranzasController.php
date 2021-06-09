<?php

namespace App\Http\Controllers\API;

use App\Cobranza;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CobranzasController extends Controller
{
    public function index(Request $request)
    {
        $cobranzas = Cobranza::where('sr_deleted', '=', '')
            ->where('usuario', '=', $request->ven)
            ->select(
                'data',
                'doc',
                'seqserial',
                'serial',
                'registro',
                'id',
                'descricao',
                'vencsis',
                'vencimento',
                'valor',
                'valor_guar',
                'cam_guar',
                'cliente',
                'nome',
                'endereco',
                'shop',
                'email',
                'whatsapp',
                'filial',
                'sdigital',
                'fiscal',
                'codtipo',
                'tipo',
                'usuario',
                'nomeusuari',
                'userecebe',
                'nomereceb',
                'pago',
                'process',
                'previsao',
                'vencido',
                'obs',
                'link',
                'sr_recno',
                'sr_deleted'
            )
            ->get();

        return $cobranzas;

        $res = [];
        foreach ($cobranzas as $cobranza) {
            if (!isset($res[$cobranza->cliente])) {
                $res[$cobranza->cliente] = [
                    'cliente' => [
                        'id' => $cobranza->cliente,
                        'name' => $cobranza->nome,
                        'address' => $cobranza->endereco,
                        'city' => $cobranza->cidade,
                        'ruc' => $cobranza->ruc,
                        'phone' => $cobranza->telefone,
                        'vendedor' => $cobranza->vendedor,
                    ],
                    'items' => [],
                ];
            }
//            if ($cobranza->geoloc) {
//                $coords = substr($cobranza->geoloc, strpos($cobranza->geoloc, ','));
//                $cobranza->longitude = $coords[0];
//                $cobranza->latitude = $coords[0];
//            } else {
//                $cobranza->longitude = '';
//                $cobranza->latitude = '';
//            }
            $res[$cobranza->cliente]['items'][] = $cobranza;
        }
        $res = array_values($res);

        return $res;
    }

    public function uploadCobranzas(Request $request)
    {
        foreach ($request->cobranzas as $cobranza) {
            Cobranza::whereSerial($cobranza['serial'])
                ->update([
                    'pago' => !is_null($cobranza['pago']) ? $cobranza['pago'] : '',
                ]);
        }

        return [
            'status' => 'ok',
        ];
    }
}
