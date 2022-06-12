<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicationVendorProductConnectionLog extends Model
{
    protected $table = 'publication_vendor_product_logs';

    protected $fillable = ['action','vendor_product_id', 'publication_id', 'user_id'];

    public function publication()
    {
        return $this->belongsTo('App\Publication', 'publication_id');
    }

    public function vendorProduct()
    {
        return $this->belongsTo('App\VendorProduct','vendor_product_id','id');
    }

    public function actionBy()
    {
        return $this->belongsTo('App\User','user_id','id');
    }


}
