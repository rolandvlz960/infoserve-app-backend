<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = "fil150";
    protected $primaryKey = "digito";
    public $timestamps = false;

    public function scopeDefaultSelect($q)
    {
        return $q->select(
            'cliente',
            'digito',
            'cli_pessoa',
            'nome',
            'ruc',
            'cidade',
            'rg',
            'fone',
            'endereco'
        );
    }

    /**
     * NACIONALIDAD
     * = 1 Turista
     * = 2 Paraguayo
     * = 3 Juridico unipersonal
     */
    public function scopeNacionalidad($q, $nacionalidad)
    {
        if ($nacionalidad == 1) {
            return $q->where('cli_pessoa', '=', 2);
        } else {
            return $q->where('cli_pessoa', '<>', 2);
        }
        return $q;
    }

    public function scopeDelVendedor($q, $vendedor)
    {
        return $q->where('vendedor', '=', $vendedor);
    }

    public function scopeFiltrar($q, $cliente)
    {
        if ($cliente) {
            if (is_numeric($cliente)) {
                $q = $q->where('cliente', '=', $cliente);
            } else {
                $q = $q->where('nome', 'like', '%'. $cliente .'%');
            }
        }
        return $q;
    }
}
