<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BundlePublication extends Model
{
    protected $table = 'bundle_publications';

    protected $fillable = [
        'bundle_id',
        'publication_name',
        'publication_id', 'qty', 'price',
    ];

    public static function label()
    {
        return 'Publish Product';
    }

    public static function singularlabel()
    {
        return 'Publish Products';
    }

    public function bundle()
    {
        return $this->belongsTo('App\Publication', 'bundle_id', 'id');
    }

    public function publication()
    {
        return $this->belongsTo('App\Publication', 'publication_id', 'id');
    }
}
