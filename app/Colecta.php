<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Colecta extends Model
{
    protected $table = "fil1004";
    public $timestamps = false;

    protected $fillable = [
        'NOTA',
        'PRODUTO',
        'USUARIO',
        'DEPOSITO',
        'OPERACAO',
        'QUANTIDADE',
        'DATA',
        'HORA',
    ];
}
