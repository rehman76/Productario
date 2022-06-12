<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WooSyncActionLog extends Model
{
    protected $table = 'woo_sync_action_logs';

    protected $fillable = [
        'created_by','started_at', 'ended_at', 'status', 'progress_percentage', 'message'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime'

    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
