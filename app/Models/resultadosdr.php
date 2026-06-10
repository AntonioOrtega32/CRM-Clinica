<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class resultadosdr extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'color',
        'numero',
        'icono_svg',
    ];
}
