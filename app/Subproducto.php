<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Subproducto extends Model
{
    protected $table = "fil040";

    public function productoPadre()
    {
        return $this->belongsTo(Producto::class, 'PRODUTO', 'PRODUTO');
    }

    public function item()
    {
        return $this->belongsTo(Producto::class, 'SUBITEM', 'PRODUTO');
    }
}
