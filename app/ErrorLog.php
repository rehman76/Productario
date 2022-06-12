<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    protected $table = 'error_logs';

     protected $fillable= ['is_resolved', 'body', 'errorable_type', 'errorable_id'];

    public function errorable()
    {
        return $this->morphTo();
    }
}
