<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cobranza extends Model
{
    protected $table = "cobra_001";
    public $timestamps = false;

    protected $fillable = [
        'data',
        'doc',
        'seqserial',
        'serial',
        'registro',
        'id',
        'descricao',
        'vencsis',
        'vencimento',
        'valor',
        'valor_guar',
        'cam_guar',
        'cliente',
        'nome',
        'endereco',
        'shop',
        'email',
        'whatsapp',
        'filial',
        'sdigital',
        'fiscal',
        'codtipo',
        'tipo',
        'usuario',
        'nomeusuari',
        'userecebe',
        'nomereceb',
        'pago',
        'process',
        'previsao',
        'vencido',
        'obs',
        'link',
        'sr_recno',
        'sr_deleted',
    ];

    public function scopeDefaultSelect($q)
    {
        foreach ($this->attributes as $attribute) {
            $q->addSelect($attribute);
        }

        return $q;
    }
}
