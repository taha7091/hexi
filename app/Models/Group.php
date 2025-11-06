<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id',
        'name',
        'description',
        'is_active',
        'color',
        'icon',
        'sort_order',
        'pos_x',
        'pos_y',
        'width',
        'height'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pos_x' => 'integer',
        'pos_y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function category()
    {
        return $this->hasOneThrough(Category::class, Division::class, 'id', 'id', 'division_id', 'category_id');
    }

    public function screens()
    {
        return $this->hasMany(Screen::class);
    }
}
