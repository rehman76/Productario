<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class PublicationLog extends Model
{
    protected $table    = "publication_logs";
    protected $fillable = [
        'publication_id','stock', 'price' , 'message'
    ];

    public function publication()
    {
        return $this->belongsTo('App\Publication', 'publication_id');
    }

}
