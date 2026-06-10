<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'usuario_id',
        'tipo_movimiento',
        'descripcion',
        'tabla_afectada',
        'registro_id',
        'ip',
        'user_agent',
    ];

    public function usuario() {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
