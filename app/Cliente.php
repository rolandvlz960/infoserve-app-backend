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
            'nome'
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
        if ($nacionalidad == 2) {
            return $q->where('cli_pessoa', '=', 1);
        } else if ($nacionalidad == 1 || $nacionalidad == 3) {
            return $q->where('cli_pessoa', '<>', 2);
        }
        return $q;
    }

    public function scopeFiltrar($q, $cliente)
    {
        if ($cliente) {
            if (str_contains($cliente, "cod:")) {
                $q = $q->where('cliente', '=', str_replace("cod:", "", $cliente));
            } else {
                $q = $q->where('cliente', '=', $cliente)
                ->orWhere('nome', 'like', '%'. $cliente .'%');
            }
        }
        return $q;
    }
}
