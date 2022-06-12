<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicationMarketingImage extends Model
{
    protected $fillable = [
        'image_url', 'updated_at', 'created_at'
    ];
}
