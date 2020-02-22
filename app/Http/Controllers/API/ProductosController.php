<?php

namespace App\Http\Controllers\API;

use App\Bloqueo;
use App\Deposito;
use App\Dispositivo;
use App\Nota;
use App\Usuario;
use Carbon\Carbon;
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
                ->select(
                    'AUTORIZA'
                )
                ->first();
            if (is_null($dispositivo)) {
                $vendedor = Usuario::where('numero', '=', $request->ven)
                    ->select('NOME')
                    ->first();
                Dispositivo::create([
                    "ID" => $request->key,
                    "VENDEDOR" => $request->ven,
                    "NOME" => $vendedor->NOME,
                    "DATA" => DB::select("SELECT CURDATE() AS data")[0]->data,
                    "HORA" => DB::select("SELECT TIME_FORMAT(CURTIME(), '%h:%i:%s') AS hora")[0]->hora,
                    "STATUS" => '.',
                    "AUTORIZA" => 'N',
                    "sr_deleted" => '',
                ]);
                $dispositivo = Dispositivo::where('id', '=', $request->key)
                    ->select('AUTORIZA')
                    ->first();
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
        if ($request->has('barcode')) {
            $productos = $productos->buscarCodigoBarra($request);
        }
        $productos = $productos->filtrar($request->producto)
            ->filtrarTipo($request)
//            ->where('COMPOSTO', '<>', 'S')
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
        $res = 0;
        $idBloqueo = $request->idbloq;
        $idUsuario = $request->usuario;
        $usuario = Usuario::where('numero', '=', $idUsuario)->select('nome')->first();
        $producto = Producto::select(
            'PRODUTO',
            'COMPOSTO'
        )
            ->where('produto', $id)
            ->first();
        if($producto->COMPOSTO == 'S') {
            $subitems = $producto->subitems;
            DB::beginTransaction();
            $count = 0;
            foreach ($subitems as $subitem) {
                $itemCant = $subitem->QUANTIDADE;
                $count += Producto::where('produto', $subitem->SUBITEM)
                    ->where("dep$dep", '>', 0)
                    ->whereRaw("dep$dep-bloq_dep$dep >= " . ( $cant * $itemCant ))
                    ->update([
                        "bloq_dep$dep" => DB::raw("bloq_dep$dep + " . ( $cant * $itemCant )),
                        'bloqapp' => DB::raw('bloqapp + ' . ( $cant * $itemCant ))
                    ]);
            }
            if ($count != $subitems->count()) {
                DB::rollBack();
                return [
                    'success' => 'no'
                ];
            }

            $res = $count;
            DB::commit();
        } else {
            $res = Producto::where('produto', $id)
                ->where("dep$dep", '>', 0)
                ->whereRaw("dep$dep-bloq_dep$dep >= $cant")
                ->update([
                    "bloq_dep$dep" => DB::raw("bloq_dep$dep + " . $cant),
                    'bloqapp' => DB::raw('bloqapp + 1')
                ]);
        }

        if ($res != 0) {
            if ($idBloqueo == 0) {
                $lastBloqueo = Bloqueo::max('idbloq');
                $fechaEncerra = Nota::select('encerra')->first()->encerra;
                $idBloqueo = !is_null($lastBloqueo) ? ( $lastBloqueo + 1 ) : 1;
                $producto = Producto::where('produto', $id)->select('digito')->first();
                Bloqueo::create([
                    'idbloq' => $idBloqueo,
                    'usuario' => $idUsuario,
                    'hora' => DB::raw('time_format(NOW(), "%H:%i:%s")'),
                    'horafim' => '',
                    'data' => Carbon::createFromFormat('Y-m-d', $fechaEncerra)->addDay()->format('Y-m-d'),
                    'nome' => $usuario->nome,
                    'produto' => $producto->digito,
                    'quantidade' => $cant,
                    'deposito' => $dep,
                    'status' => '',
                    'qtdedesb' => 0,
                    'operacao' => 1,
                    'sr_deleted' => '',
                ]);
            } else {
                Bloqueo::where('idbloq', '=', $idBloqueo)->increment('quantidade', $cant);
            }
        }

        return [
            'success' => $res == 0 ? 'no' : 'yes',
            'idbloq' => $idBloqueo
        ];
    }

    public function decQtt($id, Request $request)
    {
        $dep = $request->dep;
        $idBloqueo = $request->idbloq;
        $idUsuario = $request->usuario;
        $usuario = Usuario::where('numero', '=', $idUsuario)->select('nome')->first();
        $numDeleted = $request->has('numDeleted') ? $request->numDeleted : 1;
        $producto = Producto::select(
            'PRODUTO',
            'COMPOSTO'
        )
            ->where('produto', $id)
            ->first();
        if($producto->COMPOSTO == 'S') {
            $subitems = $producto->subitems;
            DB::beginTransaction();
            $count = 0;
            foreach ($subitems as $subitem) {
                $itemCant = $subitem->QUANTIDADE;
                $count += Producto::where('produto', $subitem->SUBITEM)
                    ->where("bloq_dep$dep", '>', 0)
                    ->update([
                        "bloq_dep$dep" => DB::raw("bloq_dep$dep - " . ( $numDeleted * $itemCant )),
                        'bloqapp' => DB::raw("bloqapp - " . ( $numDeleted * $itemCant ))
                    ]);
            }
            if ($count != $subitems->count()) {
                DB::rollBack();
                return [
                    'success' => 'no'
                ];
            }

            $res = $count;
            DB::commit();
        } else {
            $res = Producto::where('produto', $id)
                ->where("bloq_dep$dep", '>', 0)
                ->update([
                    "bloq_dep$dep" => DB::raw("bloq_dep$dep - $numDeleted"),
                    'bloqapp' => DB::raw("bloqapp - $numDeleted")
                ]);
        }

        $updated = Bloqueo::where('idbloq', '=', $idBloqueo)->where('quantidade', '>', $numDeleted)->decrement('quantidade', $numDeleted);

        if (!$updated) {
            Bloqueo::where('idbloq', '=', $idBloqueo)->update([
                "quantidade" => 0,
                'horafim' => DB::raw('time_format(NOW(), "%H:%i:%s")')
            ]);
        }

        return [
            'success' => $res == 0 ? 'no' : 'yes',
            'idbloq' => $idBloqueo
        ];
    }
}
