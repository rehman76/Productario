<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicationPremiumProduct extends Model
{
    protected $fillable = [
        'publication_id', 'mla_id', 'price'
    ];

    public function publication()
    {
        return $this->belongsTo('App\Publication', 'publication_id');
    }
}
