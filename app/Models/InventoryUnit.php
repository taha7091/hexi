<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'name', 'symbol', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function buyingItems()
    {
        return $this->hasMany(InventoryItem::class, 'buying_unit_id');
    }

    public function stockItems()
    {
        return $this->hasMany(InventoryItem::class, 'stock_unit_id');
    }

    public function usageItems()
    {
        return $this->hasMany(InventoryItem::class, 'usage_unit_id');
    }
}

