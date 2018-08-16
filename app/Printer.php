<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Printer extends Model
{
    public $table = "fil341";

    protected $fillable = [
        "CODIGO",
        "NOME",
        "IP",
        "PORTA",
        "sr_deleted"
    ];
}
