<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_purchase_id','inventory_item_id','quantity_buying','unit_cost','line_total'
    ];

    protected $casts = [
        'quantity_buying' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'line_total' => 'decimal:4',
    ];

    public function purchase() { return $this->belongsTo(InventoryPurchase::class, 'inventory_purchase_id'); }
    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
}

