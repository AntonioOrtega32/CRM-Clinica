<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class servicios extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'detalle',
        'descripcion',
        'imagen',
    ];
}
