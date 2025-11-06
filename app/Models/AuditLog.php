<?php

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'action', 'table_name', 'record_id', 'changes', 'ip_address', 'logged_at'];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
