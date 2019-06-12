<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CondicaoPagamento extends Model
{
    protected $table = "fil161";
    protected $fillable = [
        "codcliente",
        "codcondpag",
    ];

    public function scopeDeCliente($q, $idCliente)
    {
        return $q->where('codcliente', $idCliente);
    }
}
