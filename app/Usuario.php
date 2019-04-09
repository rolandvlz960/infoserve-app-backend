<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $primaryKey = "NUMERO";
    protected $table = "fil020";
    public $timestamps = false;

    public function scopeDefaultSelect($q)
    {
        return $q->select(
            'NUMERO',
            'NOME',
            'SALDODEP as DEPOSITO',
            'SENHA'
        );
    }

    public function scopeGerente($q)
    {
        return $q->where('GERENTE', '=', 'S')
//            ->where('AUTABXCOST', '<>', '')
            ->where('ATIVO', '<>', 'N');
    }
}
