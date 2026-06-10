<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdInventoryItem extends Model
{
    use HasFactory;

    protected $table = 'ad_inventory_items';

    protected $fillable = [
        'name',
        'description',
        'category',
        'quantity_package',
        'unit',
        'stock',
        'pending_purchase',
        'minimum_required',
        'location',
        'has_expiry',
        'clinic',
        'expiry_date',
        'manual_price',
    ];

    protected $casts = [
        'has_expiry' => 'boolean',
        'expiry_date' => 'date',
        'stock' => 'float',
        'manual_price' => 'decimal:2',
    ];
}
