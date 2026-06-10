<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Empleado;

class Holiday extends Model
{
    use HasFactory;

    protected $table = 'ad_holidays';

    protected $fillable = [
        'employee_id',
        'start',
        'end',
        'notes',
        'approved_by',
    ];

    public $timestamps = false;

    // Relación original
    public function employee()
    {
        return $this->belongsTo(Empleado::class, 'employee_id');
    }

    // Alias opcional si quieres usar $holiday->empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'employee_id');
    }
}
