<?php

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'message', 'read', 'notified_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

