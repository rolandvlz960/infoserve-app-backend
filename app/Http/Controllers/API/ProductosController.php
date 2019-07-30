<?php

namespace App\Http\Controllers\API;

use App\Deposito;
use App\Dispositivo;
use App\Usuario;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;

use App\Producto;
use App\Foto;

use Illuminate\Support\Facades\DB;
use Image;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        $config = DB::table('fil120')->select(
            'precoapp',
            'fotosapp',
            'depapp',
            'estoqapp',
            'qtdapp',
            'kitapp'
        )->first();
        $page = $request->has('page') ? $request->page : 1;
        if (!$request->has('def_preco')) {
            $productos = Producto::defaultSelect($request->dep);
        } else {
            if (!$config->precoapp) {
                return response()->json([
                    'error' => 'precio-not-configured'
                ]);
            }
            $productos = Producto::defaultSelectPreco($request->dep, $config->precoapp);
        }
        if ($request->has('ve')) {
            $dispositivo = Dispositivo::where('id', '=', $request->key)
                ->select('AUTORIZA')
                ->first();
            if (is_null($dispositivo)) {
                $vendedor = Usuario::where('numero', '=', $request->ven)
                    ->select('NOME')
                    ->first();
                $dispositivo = Dispositivo::create([
                    "ID" => $request->key,
                    "VENDEDOR" => $request->ven,
                    "NOME" => $vendedor->NOME,
                    "DATA" => DB::select("SELECT CURDATE() AS data")[0]->data,
                    "HORA" => DB::select("SELECT TIME_FORMAT(CURTIME(), '%h:%i:%s') AS hora")[0]->hora,
                    "STATUS" => '.',
                    "AUTORIZA" => 'N',
                    "sr_deleted" => '',
                ]);
            }
            if ($dispositivo->AUTORIZA !== 'S') {
                return response()->json([
                    'error' => 'tablet-disabled'
                ]);
            }
            if ($config->estoqapp == 'S') {
                $productos = $productos->stockAvailable($config->depapp);
            }
            if ($config->kitapp === 2) {
                $productos = $productos->productosKit();
            }
        }
        $productos = $productos->filtrar($request->producto)
            ->filtrarTipo($request)
            ->where('COMPOSTO', '<>', 'S')
            ->orderBy('DESCRICAO', 'ASC');
        if (!$request->has('get-all')) {
            $productos = $productos->limit(20)
                ->skip(20 * ($page - 1));
        }
        $productos = $productos->get();

        $res = $productos->map(function ($item) use ($request, $config) {
            $item->foto = '';
            if (
                (!$request->has('get-all'))
                || ($request->has('get-all') && $config->fotosapp == 'S')
            ) {
                if (!is_null($item->fotoProducto)) {
                    $item->foto = url('api/productos/' . $item->produto . '/foto');
                }
            }
            unset($item->fotoProducto);
            return $item;
        });
        return [
            'data' => $res,
            'config' => [
                'qtdapp' => $config->qtdapp === 'S',
                'fotosapp' => $config->fotosapp === 'S',
            ],
            'query' => $request->has('producto') ? $request->producto : $request->q
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

    public function stocks($producto)
    {
        $depositos = Deposito::disponible()
            ->select('deposito', 'nome')
            ->orderBy('deposito', 'asc')
            ->get();
        return [
            'stocks' => Producto::stocksSelect($depositos->pluck('deposito')->toArray())
                ->where('produto', '=', $producto)
                ->first(),
            'depositos' => $depositos
        ];
    }

    public function incQtt($id, Request $request)
    {
        $dep = $request->dep;
        $cant = $request->has('cant') ? $request->cant : 1;
        $res = Producto::where('produto', $id)
        ->where("dep$dep", '>', 0)
        ->whereRaw("dep$dep-bloq_dep$dep >= $cant")
        ->update([
            "bloq_dep$dep" => DB::raw("bloq_dep$dep + " . $cant),
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
