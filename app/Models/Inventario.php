<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventarios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'cantidad_paquete',
        'unidades',
        'stock',
        'cantidad_minima',
        'ubicacion',
        'fecha_vencimiento',
        'clinica',
        'caduca',
        'precio_unitario',
    ];
}
