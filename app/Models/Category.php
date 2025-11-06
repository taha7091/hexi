<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'name',
        'description',
        'is_active',
        'color',
        'icon',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
