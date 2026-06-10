<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class blogdr extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'contenido',
        'fecha',
        'imagen',
    ];
}
