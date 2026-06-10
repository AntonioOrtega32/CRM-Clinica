<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invCategories extends Model
{
    use HasFactory;

    protected $table = 'inv_categories';

    protected $fillable = [
        'name',
        'description',
    ];
}
