<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deposito extends Model
{
    protected $table = 'fil180';

    public function scopeDisponible($q)
    {
        return $q->where('tipo', '=', 'D');
    }
}
