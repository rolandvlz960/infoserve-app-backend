<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moneda extends Model
{
    public $table = "fil300";
    public $timestamps = false;

    public function scopeDefaultSelect($q, $all = false)
    {
        $q = $q->select(
            'moeda',
            'sigla',
            'nomeplural',
            'operacao',
            'mascara'
        );

        if ($all) {
            $q = $q->orderBy('moeda', 'asc');
        } else {
            $q = $q->where('moeda', 1);
        }

        return $q;
    }
}
