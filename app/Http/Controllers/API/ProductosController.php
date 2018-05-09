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
    public function index(Request $request)
    {
        $productos = Producto::defaultSelect()
            ->filtrar($request->producto)
            ->get();
        return $productos->map(function($item) {
            $item->foto = url('api/productos/' . $item->produto . '/foto');
            return $item;
        });
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
            return redirect()->to(url('img/no_foto.png'));
        }
    }
}
