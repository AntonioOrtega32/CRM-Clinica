<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentProducts extends Model
{
    use HasFactory;

    protected $table = 'treatment_products';

    protected $fillable = [
        'treatment_type',
        'product_id',
        'quantity',
        'clinic',
    ];
}
