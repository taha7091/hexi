<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id','user_id','inventory_item_id','location_id','adjustment_type',
        'quantity_before','quantity_after','adjustment_amount','reason','notes'
    ];

    protected $casts = [
        'quantity_before' => 'decimal:6',
        'quantity_after' => 'decimal:6',
        'adjustment_amount' => 'decimal:6',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function inventoryItem() { return $this->belongsTo(InventoryItem::class); }
    public function location() { return $this->belongsTo(InventoryLocation::class, 'location_id'); }

    // Human-friendly adjustment number like ADJ-000123
    public function getNumberAttribute(): string
    {
        return 'ADJ-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

}

