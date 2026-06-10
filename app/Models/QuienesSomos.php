<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuienesSomos extends Model
{
    use HasFactory;

    protected $table = 'quienes_somos';

    // Campos asignables
    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen',
        'posicion',
        'created_by',
        'updated_by',
    ];

   

}
