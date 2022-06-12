<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TnCheckoutUrl extends Model
{
    protected $table = 'tn_checkout_urls';

    protected $fillable = [
        'url' , 'title','is_active', 'params','clicks','discount_percentage','contact_name', 'contact_last_name', 'contact_email',
    ];

    protected $casts = [
        'params' => 'array',
    ];

    public function tnCheckoutUrlPublications()
    {
        return $this->hasMany('App\TnCheckoutUrlPublication', 'tn_checkout_url_id', 'id');
    }
}
