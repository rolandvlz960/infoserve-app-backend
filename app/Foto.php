<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = "fil019";

    public function scopeDelProducto($q, $id)
    {
        return $q->where('produto', $id);
    }
}
