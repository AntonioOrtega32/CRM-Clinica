<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $table = 'sa_leads'; // nombre de la tabla

    protected $fillable = [
        'contact_id',
        'first_name',
        'last_name',
        'clinic',
        'origin',
        'phone',
        'interested_in',
        'stage',
        'semaforo',
        'quali',
        'link',
        'notes',
        'seller',
        'last_activity',
        'evaluator',
        'fecha_abierta', // nuevo campo booleano
    ];

    protected $casts = [
        'fecha_abierta' => 'boolean',
        'created_at' => 'datetime',
        'last_activity' => 'datetime',
    ];

    public $timestamps = true; // ya que tienes created_at y last_activity
}
