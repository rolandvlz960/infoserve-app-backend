<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Usuario;

class UsuariosController extends Controller
{
    public function login(Request $request)
    {
        try {
            return Usuario::defaultSelect()
                ->where('USUARIO', '=', $request->username)
                // ->where('SENHA', '=', $request->password)
                ->where('sr_deleted', '<>', 'T')
                ->where('STATUS_NT', '=', '')
                ->firstOrFail();
        } catch(ModelNotFoundException $e) {
            abort(404);
        }
    }
}
