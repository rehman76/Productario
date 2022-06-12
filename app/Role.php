<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table    = "users";
    protected $fillable = [
        'name', 'updated_at', 'created_at'
    ];

    public function users()
    {
        return $this->hasMany('App\User');
    }
}
