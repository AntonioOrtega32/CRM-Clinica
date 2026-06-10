<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'apellido',
        'puesto',
        'departamento',
        'fecha_ingreso',
        'estatus',
        'telefono',
        'fecha_nacimiento',
        'direccion',
        'identificacion',
        'emergencia_nombre',
        'emergencia_telefono',
        'emergencia2_nombre',
        'emergencia2_telefono',
        'notas',
        'foto',
        'clinica', // importante si filtras por clínica
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // RELACIÓN CON VACACIONES
    public function holidays()
    {
        return $this->hasMany(Holiday::class, 'employee_id');
    }

    public function lastHoliday()
    {
        return $this->hasOne(Holiday::class, 'employee_id')->latest('id');
    }
}
