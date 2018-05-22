<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = "fil150";
    protected $primaryKey = "digito";
    public $timestamps = false;

    public function scopeDefaultSelect($q)
    {
        return $q->select(
            'cliente',
            'digito',
            'nome'
        );
    }

    public function scopeFiltrar($q, $cliente)
    {
        if ($cliente) {
            if (str_contains($cliente, "cod:")) {
                $q = $q->where('cliente', '=', str_replace("cod:", "", $cliente));
            } else {
                $q = $q->where('cliente', '=', $cliente)
                ->orWhere('nome', 'like', '%'. $cliente .'%');
            }
        }
        return $q;
    }
}
