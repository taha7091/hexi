<?php

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'user_id', 'supplier_name', 'total', 'ordered_at'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
