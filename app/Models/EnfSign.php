<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnfSign extends Model
{
    protected $table = 'enf_signs';
    protected $fillable = ['num_med', 'step', 'clinic', 'url'];
}
