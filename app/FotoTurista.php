<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FotoTurista extends Model
{
    protected $table = "fil154";

    protected $fillable = [
        'rg',
        'foto1',
        'foto2',
        'cliente',
        'usuario',
        'flag',
    ];

    protected $primaryKey = "rg";
    public $timestamps = false;

    public function scopeDelProducto($q, $id)
    {
        return $q->where('produto', $id);
    }
}
