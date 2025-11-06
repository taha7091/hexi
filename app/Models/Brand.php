<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'description'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    // Users are related through branches, not directly to brands
    public function users()
    {
        return $this->hasManyThrough(User::class, Branch::class);
    }

    public function posLimits()
    {
        return $this->hasMany(PosLimit::class);
    }
}
