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
            'descricao'
        );
    }

    public function scopeFiltrar($q, $prod)
    {
        if ($prod) {
            $q = $q->where('produto', '=', $prod)
            ->orWhere('descricao', 'like', '%'. $prod .'%');
        }
        return $q;
    }
}
