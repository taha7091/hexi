<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'adjustment_type',
        'quantity_before',
        'quantity_after',
        'adjustment_amount',
        'reason',
        'notes'
    ];

    protected $casts = [
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'adjustment_amount' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the product that was adjusted
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who made the adjustment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the adjustment type label
     */
    public function getAdjustmentTypeLabelAttribute()
    {
        return match($this->adjustment_type) {
            'add' => 'Stock Added',
            'subtract' => 'Stock Removed',
            'set' => 'Stock Set',
            default => ucfirst($this->adjustment_type)
        };
    }

    /**
     * Get the adjustment amount with sign
     */
    public function getSignedAdjustmentAttribute()
    {
        return $this->adjustment_amount >= 0 ? '+' . $this->adjustment_amount : $this->adjustment_amount;
    }
}
