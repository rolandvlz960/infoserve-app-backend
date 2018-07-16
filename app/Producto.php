<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Producto extends Model
{
    protected $table = "fil010";
    protected $primaryKey = "digito";
    public $timestamps = false;

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
            DB::raw('dep_' . $dep . ' as cant')
            // 'bloqapp',
            // 'dep01',
            // 'bloq_dep01'
        );
    }

    public function scopeFiltrar($q, $prod)
    {
        if ($prod) {
            if (str_contains($prod, "cod:")) {
                $q = $q->where('digito', '=', str_replace("cod:", "", $prod));
            } else {
                $q = $q->where('digito', '=', $prod)
                    ->orWhere('descricao', 'like', '%'. $prod .'%');
            }
        }
        return $q;
    }
}
