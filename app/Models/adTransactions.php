<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class adTransactions extends Model
{
    use HasFactory;
    protected $table = 'ad_transactions';

    protected $fillable = [ 
        'name',
        'description',
        'payment_method_id',
        'amount',
        'date',
        'store',
        'cat_id',
        'current-status',
        'subcategory',
        'sub_subcategory',
        'clinic',
        'created_by',
    ];

    protected $casts = [
    'date' => 'datetime',
];

}
