<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'inv_division_id', 'name', 'description', 'color', 'icon', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function division()
    {
        return $this->belongsTo(InvDivision::class, 'inv_division_id');
    }

    public function category()
    {
        return $this->hasOneThrough(InvCategory::class, InvDivision::class, 'id', 'id', 'inv_division_id', 'inv_category_id');
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'inv_group_id');
    }
}

