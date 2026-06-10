<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    // Nombre de la tabla (Laravel lo inferiría, pero lo dejamos explícito)
    protected $table = 'movimientos_inventario';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'idProducto',
        'idBatch',
        'tipoMovimiento',
        'cantidad',
        'expedidoPor',
        'entregadoA',
        'fechaMovimiento',
        'ubicacion',
    ];

    /**
     * Relación con Inventario (un movimiento pertenece a un producto).
     */
    public function producto()
    {
        return $this->belongsTo(Inventario::class, 'idProducto');
    }
}
