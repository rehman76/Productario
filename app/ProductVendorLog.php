<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductVendorLog extends Model
{
    protected $table    = "vendor_product_logs";
    protected $fillable = [
        'cost','iva', 'quantity', 'discount', 'other_taxes', 'updated_at', 'created_at'
    ];
}
