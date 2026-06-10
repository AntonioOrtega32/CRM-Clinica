<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class adsubSubcategory extends Model
{
    use HasFactory;

    protected $table = 'ad_sub_subcategories';

    protected $fillable = [
        'subcategory_id',
        'name',
        'description',
        'amount_',
        'current',
    ];
}
