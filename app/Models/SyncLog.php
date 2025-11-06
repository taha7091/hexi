<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = ['pos_device_id', 'synced_at', 'details'];

    public function posDevice()
    {
        return $this->belongsTo(PosDevice::class);
    }
}
