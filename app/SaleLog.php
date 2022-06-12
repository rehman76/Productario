<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleLog extends Model
{
    protected $fillable = ['message', 'sale_id'];

    public function saleOrder()
    {
        return $this->belongsTo('App\Sale', 'sale_id');
    }
}
