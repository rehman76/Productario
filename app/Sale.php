<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = "sales";
    protected $fillable = [
        'order_id', 'status', 'date_created', 'total_amount','sale_label',
        'profit','ml_commissions','expense','taxes','shipping_cost', 'attributes'
    ];
    protected $casts = [
        'date_created' => 'date',
        'attributes' => 'array'
    ];

    public function publications()
    {
        return $this->belongsToMany('App\Publication')->withPivot('price',
            'sale_price', 'iva', 'updated_at', 'created_at', 'other_taxes');
    }

    public function buyer()
    {
        return $this->hasOne('App\Buyer');
    }

    public function SaleHasItems()
    {
        return $this->hasMany('App\SaleItem');
    }

    public function saleLogs()
    {
        return $this->hasMany('App\SaleLog');
    }

    public function saleHasItem()
    {
        return $this->hasOne('App\SaleItem');
    }

    public function SaleHasPayment()
    {
        return $this->hasMany('App\SalePayment');
    }
    public function SaleHasBuyer()
    {
        return $this->hasOne('App\SaleBuyer');
    }

    public function snapShot()
    {
        return $this->hasOne('App\SaleItemSnapshot', 'sale_order_id');
    }

    public function client()
    {
        return $this->belongsTo('App\User')->where('type', 'cliente');
    }


}
