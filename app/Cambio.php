<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cambio extends Model
{
    public $table = "fil070";
    public $timestamps = false;

    public function scopeDefaultSelect($q)
    {
        return $q->select(
            'padrxm01',
            'padrxm02',
            'padrxm03',
            'padrxm04',
            'padrxm05',
            'padrxm06',
            'padrxm07'
        )->orderBy('data', 'desc')
        ->orderBy('hora', 'desc');
    }
}
