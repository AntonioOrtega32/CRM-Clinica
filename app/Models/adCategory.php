<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class adCategory extends Model
{
    use HasFactory;

    protected $table = 'ad_categories';

    protected $fillable = [
        'name',
        'amount',
        'current',
        'visible',
    ];
}
