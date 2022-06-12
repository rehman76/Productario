<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TnCheckoutUrlPublication extends Model
{
    protected $table = 'tn_checkout_url_publications';

    protected $fillable = [
        'tn_checkout_url_id',
        'publication_id',
        'tiendanube_variant_id',
        'publication_name',
        'qty',
    ];

    public function publication()
    {
        return $this->belongsTo('App\Publication', 'publication_id', 'id');
    }
}
