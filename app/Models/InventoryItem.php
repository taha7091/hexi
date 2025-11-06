<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'brand_id', 'inv_category_id', 'inv_division_id', 'inv_group_id',
        'supplier_id', 'location_id', 'code', 'name', 'barcode',
        'buying_unit_id', 'stock_unit_id', 'usage_unit_id',
        'buy_to_stock_factor', 'stock_to_usage_factor',
        'cost_price', 'current_stock', 'minimum_stock', 'is_active', 'notes'
    ];

    protected $casts = [
        'buy_to_stock_factor' => 'decimal:6',
        'stock_to_usage_factor' => 'decimal:6',
        'cost_price' => 'decimal:4',
        'current_stock' => 'decimal:6',
        'minimum_stock' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function brand() { return $this->belongsTo(Brand::class); }

    public function category() { return $this->belongsTo(InvCategory::class, 'inv_category_id'); }
    public function division() { return $this->belongsTo(InvDivision::class, 'inv_division_id'); }
    public function group() { return $this->belongsTo(InvGroup::class, 'inv_group_id'); }

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function location() { return $this->belongsTo(InventoryLocation::class, 'location_id'); }

    public function buyingUnit() { return $this->belongsTo(InventoryUnit::class, 'buying_unit_id'); }
    public function stockUnit() { return $this->belongsTo(InventoryUnit::class, 'stock_unit_id'); }
    public function usageUnit() { return $this->belongsTo(InventoryUnit::class, 'usage_unit_id'); }

    // Products (sales items) that use this inventory item via recipes
    public function usedInProducts()
    {
        return $this->belongsToMany(Product::class, 'recipe_items', 'inventory_item_id', 'product_id');
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}

