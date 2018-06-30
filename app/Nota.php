<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = "fil120";
    protected $fillable = [
        "nota"
    ];
    protected $primaryKey = "nota";
    public $timestamps = false;

    public function scopeDefaultSelect($q)
    {
        return $q->select(
            'nota'
        );
    }
}
