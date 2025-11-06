<?php

class ReturnTransaction extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = ['sale_id', 'user_id', 'amount', 'reason', 'returned_at'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

