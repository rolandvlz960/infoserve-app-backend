<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;

use App\Producto;
use App\Foto;

use DB;
use Image;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->has('page') ? $request->page : 1;
        $productos = Producto::defaultSelect($request->dep)
            ->filtrar($request->producto)
            ->where('COMPOSTO', '<>', 'S')
            ->orderBy('DESCRICAO', 'ASC')
            ->limit(20)
            ->skip(20 * ($page - 1))
            ->get();
        $res = $productos->map(function($item) {
            $item->foto = '';
            if (!is_null($item->fotoProducto) && $item->foto1 != '') {
                $item->foto = url('api/productos/' . $item->produto . '/foto');
            }
            unset($item->fotoProducto);
            return $item;
        });
        return [
            'data' => $res,
            'query' => $request->producto
        ];
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
            return '';
        }
    }

    public function incQtt($id, Request $request)
    {
        $dep = $request->dep;
        $res = Producto::where('produto', $id)
        ->where("dep$dep", '>', 0)
        ->whereRaw("bloq_dep$dep < dep$dep")
        ->update([
            "bloq_dep$dep" => DB::raw("bloq_dep$dep + 1"),
            'bloqapp' => DB::raw('bloqapp + 1')
        ]);
        return [
            'success' => $res == 1 ? 'yes' : 'no'
        ];
    }

    public function decQtt($id, Request $request)
    {
        $dep = $request->dep;
        $numDeleted = $request->has('numDeleted') ? $request->numDeleted : 1;
        $res = Producto::where('produto', $id)
        ->where("bloq_dep$dep", '>', 0)
        ->update([
            "bloq_dep$dep" => DB::raw("bloq_dep$dep - $numDeleted"),
            'bloqapp' => DB::raw("bloqapp - $numDeleted")
        ]);
        return [
            'success' => $res == 1 ? 'yes' : 'no'
        ];
    }
}
