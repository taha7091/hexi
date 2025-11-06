<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'company_id',
        'layout_config',
        'grid_rows',
        'grid_columns',
        'background_color',
        'is_active',
        'is_default'
    ];

    protected $casts = [
        'layout_config' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    /**
     * Get the company that owns the screen
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the screen items for this screen
     */
    public function screenItems(): HasMany
    {
        return $this->hasMany(ScreenItem::class);
    }

    /**
     * Get active screen items
     */
    public function activeScreenItems(): HasMany
    {
        return $this->hasMany(ScreenItem::class)->where('is_active', true);
    }

    /**
     * Scope to get active screens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get screens for a specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get the default screen for a company
     */
    public static function getDefaultForCompany($companyId)
    {
        return static::where('company_id', $companyId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set this screen as default (and unset others)
     */
    public function setAsDefault()
    {
        // Unset other default screens for this company
        static::where('company_id', $this->company_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this screen as default
        $this->update(['is_default' => true]);
    }

    /**
     * Get the layout as a grid array
     */
    public function getLayoutGrid()
    {
        $grid = [];

        // Initialize empty grid
        for ($y = 0; $y < $this->grid_rows; $y++) {
            for ($x = 0; $x < $this->grid_columns; $x++) {
                $grid[$y][$x] = null;
            }
        }

        // Fill grid with screen items
        foreach ($this->activeScreenItems as $item) {
            for ($y = $item->grid_y; $y < $item->grid_y + $item->height; $y++) {
                for ($x = $item->grid_x; $x < $item->grid_x + $item->width; $x++) {
                    if (isset($grid[$y][$x])) {
                        $grid[$y][$x] = $item;
                    }
                }
            }
        }

        return $grid;
    }
}
