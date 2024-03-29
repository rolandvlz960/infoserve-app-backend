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
        'notas',
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
        'prodkit',
        'quantidade',
        'preco',
        'prazo',
        'fotodoc1',
        'fotodoc2',
        'doc',
        'deposito',
        'ref_opera',
        'autoriza',
        'finalizar',
        'sr_deleted',
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
