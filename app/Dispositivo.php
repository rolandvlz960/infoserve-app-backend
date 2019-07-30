<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{

    protected $table = "fil412";
    public $timestamps = false;
    protected $connection = "mysql-second";

    protected $fillable = [
        "ID",
        "VENDEDOR",
        "NOME",
        "DATA",
        "HORA",
        "STATUS",
        "AUTORIZA",
        "sr_recno",
        "sr_deleted",
    ];
}
