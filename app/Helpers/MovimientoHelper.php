<?php

namespace App\Helpers;

use App\Models\Movimiento;
use Illuminate\Support\Facades\Auth;

class MovimientoHelper
{
    /**
     * Registrar un movimiento en la base de datos.
     *
     * @param string $tipo Tipo de movimiento (crear, actualizar, eliminar, etc.)
     * @param string|null $descripcion Descripción del movimiento
     * @param string|null $tabla Tabla afectada
     * @param int|null $registro_id ID del registro afectado
     */
    public static function registrar($tipo, $descripcion = null, $tabla = null, $registro_id = null)
    {
        Movimiento::create([
            'usuario_id' => Auth::id(), // Usuario logueado
            'tipo_movimiento' => $tipo,
            'descripcion' => $descripcion,
            'tabla_afectada' => $tabla,
            'registro_id' => $registro_id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
