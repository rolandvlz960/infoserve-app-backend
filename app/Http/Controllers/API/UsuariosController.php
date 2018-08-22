<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Usuario;
use App\Nota;

class UsuariosController extends Controller
{
    public function login(Request $request)
    {
        if (Nota::first()->STATUS_NT != '') {
            abort(404);
            return null;
        }
        try {
            return Usuario::defaultSelect()
                ->where('USUARIO', '=', $request->username)
                // ->where('SENHA', '=', $request->password)
                ->where('sr_deleted', '<>', 'T')
                ->firstOrFail();
        } catch(ModelNotFoundException $e) {
            abort(404);
        }
    }
}
