<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resultado extends Model
{
    use HasFactory;

 protected $fillable = [
    'titulo',
    'color',
    'numero',
    'icono_svg',
    'created_by',
    'updated_by',
];

}
