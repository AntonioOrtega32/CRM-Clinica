<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class encabezado extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'subtitulo',
        'imagen',
        'video_horizontal',
        'video_vertical',
        'contenido'
    ];
}
