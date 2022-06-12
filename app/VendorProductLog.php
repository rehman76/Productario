<?php
/**
 * Created by PhpStorm.
 * User: aliraza
 * Date: 22/03/2021
 * Time: 6:51 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendorProductLog extends Model
{
    protected $table    = "vendor_product_logs";
    protected $fillable = [
        'vendor_id','vendor_product_id','stock', 'price','message'
    ];

    public function vendorProductLogs()
    {
        return $this->belongsTo('App\VendorProduct','vendor_product_id','id');
    }
}
