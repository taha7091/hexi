<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreenItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'screen_id',
        'product_id',
        'screen_group_id',
        'type',
        'display_name',
        'background_color',
        'text_color',
        'grid_x',
        'grid_y',
        'width',
        'height',
        'custom_properties',
        'is_active'
    ];

    protected $casts = [
        'custom_properties' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Get the screen that owns the screen item
     */
    public function screen(): BelongsTo
    {
        return $this->belongsTo(Screen::class);
    }

    /**
     * Get the product associated with this screen item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the screen group associated with this screen item
     */
    public function screenGroup(): BelongsTo
    {
        return $this->belongsTo(ScreenGroup::class);
    }

    /**
     * Scope to get active screen items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get screen items by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get screen items for a specific screen
     */
    public function scopeForScreen($query, $screenId)
    {
        return $query->where('screen_id', $screenId);
    }

    /**
     * Check if this item overlaps with another item's position
     */
    public function overlapsWithPosition($x, $y, $width = 1, $height = 1)
    {
        return !(
            $x >= $this->grid_x + $this->width ||
            $x + $width <= $this->grid_x ||
            $y >= $this->grid_y + $this->height ||
            $y + $height <= $this->grid_y
        );
    }

    /**
     * Get the display text for this item
     */
    public function getDisplayText()
    {
        if ($this->display_name) {
            return $this->display_name;
        }

        if ($this->product) {
            return $this->product->name;
        }

        if ($this->screenGroup) {
            return $this->screenGroup->name;
        }

        return 'Unknown Item';
    }

    /**
     * Get the background color for this item
     */
    public function getBackgroundColor()
    {
        if ($this->background_color) {
            return $this->background_color;
        }

        if ($this->screenGroup) {
            return $this->screenGroup->color;
        }

        return '#007bff'; // Default blue
    }
}
