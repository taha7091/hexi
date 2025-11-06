<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id', 'name', 'description', 'color', 'icon', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function divisions()
    {
        return $this->hasMany(InvDivision::class);
    }
}

