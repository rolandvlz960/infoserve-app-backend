<?php

namespace App\Http\Controllers\API;

use App\Dispositivo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function test(Request $request)
    {
        if ($request->has('ve')) {
            if ($request->has('k')) {
                Dispositivo::insert([
                    'id' => $request->k
                ]);
            }
            if (DB::table('fil120')->select('tipoapp')->first()->tipoapp != 1) {
                return 'TESTOK';
            } else {
                return 'DISABLED';
            }
        }
        return 'TESTOK';
    }
}
