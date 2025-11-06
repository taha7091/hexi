<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id','supplier_id','location_id','invoice_number','invoice_date',
        'subtotal','tax_amount','total_amount','status','notes'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'total_amount' => 'decimal:4',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function location() { return $this->belongsTo(InventoryLocation::class, 'location_id'); }
    public function items() { return $this->hasMany(InventoryPurchaseItem::class); }
}

