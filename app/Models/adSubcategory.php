<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class adSubcategory extends Model
{
    use HasFactory;

    protected $table = 'ad_subcategories';


    protected $fillable = [
        'category_id',
        'name',
        'description',
        'amount_',
        'current',
    ];
}
