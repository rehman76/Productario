<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $table = 'sync_logs';

    protected $fillable= ['connector_id', 'publication_id', 'attributes', 'message'];

    protected $casts = [
        'attributes' => 'array',
    ];


    public function publication()
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    public function connector()
    {
        return $this->belongsTo(Connector::class, 'connector_id');
    }
}
