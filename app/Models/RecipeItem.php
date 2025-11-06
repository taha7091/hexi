<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'inventory_item_id', 'usage_quantity', 'notes'
    ];

    protected $casts = [
        'usage_quantity' => 'decimal:4',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
}

