<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConnectorCouponRequest extends Model
{
    protected $fillable = [
        'connector_id', 'prefix', 'number_of_coupons', 'file_path', 'discount_percentage', 'max_usage'
    ];
}
