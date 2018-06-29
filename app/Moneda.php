<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    public $table = "fil300";
    public $timestamps = false;

    public function scopeDefaultSelect($q)
    {
        return $q->select(
            'moeda',
            'sigla',
            'nomeplural',
            'operacao',
            'mascara'
        )->where('ativo', 'S');
    }
}
