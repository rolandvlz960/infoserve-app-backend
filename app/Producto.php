<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Producto extends Model
{
    protected $table = "fil010";
    protected $primaryKey = "digito";
    public $timestamps = false;

    public function fotoProducto()
    {
        return $this->hasOne('App\Foto', 'produto', 'produto');
    }

    public function scopeDefaultSelect($q, $dep)
    {
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

}
