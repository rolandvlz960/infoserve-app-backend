<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;

use App\Producto;
use App\Foto;

use Image;

class ProductosController extends Controller
{
    public function index()
    {
        return Producto::defaultSelect()->get();
    }

    public function foto($id)
    {
        try {
            $foto = Foto::delProducto($id)
            ->select('foto1')
            ->firstOrFail();

            $image = Image::make($foto->foto1);

            return $image->response('jpg');

        } catch(ModelNotFoundException $e) {
            return "";
        }
    }
}
