<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeria extends Model
{
    use HasFactory;

    protected $fillable = [
        'imagen', // ruta en storage/public
        'titulo',
        'descripcion',
        'tipo'
    ];
}
