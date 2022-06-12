<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleItemSnapshot extends Model
{
    protected $fillable = ['mla_id', 'sale_order_id', 'publication','vendor'];

    protected $casts = [
        'publication'  => 'array',
        'vendor' => 'array'
    ];

    public function SaleItem()
    {
        return $this->hasOne('App\SaleItem');
    }
}
