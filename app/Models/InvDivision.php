<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'inv_category_id', 'name', 'description', 'color', 'icon', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(InvCategory::class, 'inv_category_id');
    }

    public function groups()
    {
        return $this->hasMany(InvGroup::class, 'inv_division_id');
    }
}

