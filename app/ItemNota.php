<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemNota extends Model
{
    protected $table = "fil1003";
    protected $fillable = [
        'vendedor',
        'mobiid',
        'mobicli',
        'mobiped',
        'data',
        'hora',
        'cliente',
        'clinovo',
        'nome',
        'endereco',
        'codcidade',
        'cidade',
        'telefone',
        'ruc',
        'produto',
        'quantidade',
        'preco',
        'prazo',
        'fotodoc1',
        'fotodoc2',
        'doc',
        'deposito',
    ];
    protected $primaryKey = "digito";
    public $timestamps = false;

    public function scopeDefaultSelect($q)
    {
        return $q->select(
            'vendedor',
            'mobiped',
            'data',
            'hora',
            'cliente',
            'nome',
            'endereco',
            'cidade',
            'telefone',
            'ruc',
            'produto',
            'quantidade',
            'preco',
            'prazo',
            'fotodoc1',
            'fotodoc2'
        );
    }
}
