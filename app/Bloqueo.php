<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bloqueo extends Model
{
    protected $table = "fil694";
    protected $primaryKey = "digito";
    public $timestamps = false;

    protected $fillable = [
            'idbloq',
            'usuario',
            'hora',
            'horafim',
            'data',
            'nome',
            'produto',
            'quantidade',
            'deposito',
            'status',
            'qtdedesb',
            'operacao',
            'sr_deleted',
    ];
}
