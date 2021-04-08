<?php

namespace App\Http\Controllers\API;

use App\Nota;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebsiteController extends Controller
{
    public function importPedido($id)
    {
        $client = new Client();
        $url = env('WEBSITE_BASE_URL') . "/api/pedidos/$id/exportar";
        $hash = hash_hmac('SHA256', $id, env('WEBSITE_PEDIDO_HMAC_KEY'));
        try {
            $res = $client->get($url, [
                'headers' => [
                    'Authorization' => $hash
                ]
            ]);
        } catch (ClientException $e) {
            if ($e->getCode() == 401) {
                return [
                    'status' => 'error',
                    'message' => 'Error de autenticación. Hash incorrecto'
                ];
            }
            return [
                'status' => 'error',
                'message' => 'Error inesperado'
            ];
        }

        $nipreapp = Nota::select('nipreapp')->first()->nipreapp;

        return [
            'nota' => json_decode($res->getBody()->getContents()),
            'nivelpreco' => strtolower($nipreapp),
        ];
    }
}
