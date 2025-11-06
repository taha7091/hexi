<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'category_id',
        'division_id',
        'name',
        'description',
        'price',
        'cost_price',
        'sku',
        'barcode',
        'stock_quantity',
        'min_stock_level',
        'is_active',
        'image_url',
        'color',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'is_active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Check if product is low on stock
     */
    public function isLowStock()
    {
        if (!$this->min_stock_level) {
            return false;
        }

        return ($this->stock_quantity ?? 0) <= $this->min_stock_level;
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock()
    {
        return ($this->stock_quantity ?? 0) <= 0;
    }

    /**
     * Get profit margin
     */
    public function getProfitMargin()
    {
        if (!$this->cost_price || $this->cost_price <= 0) {
            return null;
        }

        return (($this->price - $this->cost_price) / $this->cost_price) * 100;
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_level')
                    ->whereNotNull('min_stock_level');
    }

    /**
     * Scope for out of stock products
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    // Recipe relationship (sales item -> inventory items)
    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class);
    }
}
