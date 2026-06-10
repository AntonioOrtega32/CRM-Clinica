<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasoExito extends Model
{
     protected $table = 'casos_exito';

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen',
    ];
}
