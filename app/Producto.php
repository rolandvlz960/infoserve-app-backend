<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = "fil010";
    protected $primaryKey = "digito";
    public $timestamps = false;

    public function scopeDefaultSelect($q)
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
            'preco_f'
        );
    }

    public function scopeFiltrar($q, $prod)
    {
        if ($prod) {
            if (str_contains($prod, "cod:")) {
                $q = $q->where('produto', '=', str_replace("cod:", "", $prod));
            } else {
                $q = $q->where('produto', '=', $prod)
                    ->orWhere('descricao', 'like', '%'. $prod .'%');
            }
        }
        return $q;
    }
}
