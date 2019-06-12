<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = "fil1001";
    public $timestamps = false;

    protected $fillable = [
        'vendedor',
        'mobiped',
        'mobiid',
        'data',
        'hora',
        'cliente',
        'nome',
        'endereco',
        'telefone',
        'ruc',
        'produto',
        'quantidade',
        'preco',
        'prazo',
        'prodkit',
        'operacao',
        'localizacion',
    ];
}
