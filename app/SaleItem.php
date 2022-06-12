<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'mla_id',
        'vendor_product_id',
        'vendor_id',
        'publication_id',
        'publication_snapshot',
        'vendor_product_snapshot',
        'publication_sale_price',
        'vendor_product_cost',
        'title',
        'qty',
        'sale_fee',
        'unit_price',
        'full_unit_price',
        'is_publish',
    ];

    protected $casts = [
        'publication_snapshot'  => 'array',
        'vendor_product_snapshot' => 'array'
    ];

    public function SnapShots()
    {
        return $this->hasOne('App\SaleItemSnapshot');
    }
}
