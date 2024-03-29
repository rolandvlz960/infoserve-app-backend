<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Http\Request;

class Producto extends Model
{
    protected $table = "fil010";
    protected $primaryKey = "digito";
    public $timestamps = false;
//    protected $connection = "mysql-second";

    public function fotoProducto()
    {
        return $this->hasOne('App\Foto', 'produto', 'produto');
    }

    public function subitems()
    {
        return $this->hasMany(Subproducto::class, 'PRODUTO', 'PRODUTO');
    }

    public function scopeDefaultSelect($q, $dep)
    {
        $dep = $dep < 10 ? ( "0" . intval($dep) ) : intval($dep);
        return $q->select(
            'produto',
            'digito',
            'referencia',
            'descricao',
            'preco_a',
            'preco_b',
            'preco_c',
            'preco_e',
            'preco_f',
            'taxado_a',
            'taxado_b',
            'taxado_c',
            'taxado_e',
            'taxado_f',
            DB::raw('dep' . $dep . ' - bloq_dep' . $dep . ' as ctd')
            // 'bloqapp',
            // 'dep01',
            // 'bloq_dep01'
        );
    }

    public function scopeStockAvailable($q, $dep)
    {
        $depStr = $dep < 10 ? ( "0" . intval($dep) ) : intval($dep);
        return $q->where('dep' . $depStr, '>', 0);
    }

    public function scopeDefaultSelectPreco($q, $dep, $preco)
    {
        $dep = $dep < 10 ? ( "0" . intval($dep) ) : intval($dep);
        return $q->select(
            'produto',
            'digito',
            'referencia',
            'descricao',
            DB::raw("$preco as preco"),
            DB::raw('dep' . $dep . ' - bloq_dep' . $dep . ' as ctd')
            // 'bloqapp',
            // 'dep01',
            // 'bloq_dep01'
        );
    }

    public function scopeStocksSelect($q, $depositos)
    {
        foreach ($depositos as $index => $deposito) {
            $deposito = $deposito < 10 ? '0' . intval($deposito) : intval($deposito);
            $q = $q->addSelect(DB::raw('dep' . $deposito . ' - bloq_dep' . $deposito . ' as ctd_' . $deposito));
        }
        return $q;
    }

    public function scopeProductosKit($q)
    {
        return $q->where('composto', '=', 'S');
    }

    public function scopeFiltrar($q, $prod)
    {
        if ($prod) {
            if (is_numeric($prod)) {
                $q = $q->where('digito', '=', $prod);
            } else {
                $q = $q->where('descricao', 'like', '%'. $prod .'%');
            }
        }
        return $q;
    }

    public function scopeFiltrarTipo($q, Request $request)
    {
        if ($request->has('type')) {
            $filterTypes = [
                'codigo' => 'digito',
                'descripcion' => 'descricao',
                'referencia' => 'referencia',
            ];
            $query = $request->q;
            $filtroSeleccionado = $request->has('type') ? $request->type : "codigo";
            $operator = $filtroSeleccionado == 'codigo' ? '=' : 'like';
            $like = $filtroSeleccionado == 'codigo' ? '' : '%';
            $q = $q->where($filterTypes[$filtroSeleccionado], $operator, $like . $query . $like);
        }
        return $q;
    }

    public function scopeBuscarCodigoBarra($q, Request $request)
    {
        if ($request->has('q')) {
            $q = $q->where('referencia', '=', $request->q)
                ->orWhere('subrefere', '=', $request->q)
                ->orWhere('subrefer01', '=', $request->q);
        }
        return $q;
    }

}
