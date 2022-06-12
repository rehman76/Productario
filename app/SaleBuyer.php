<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleBuyer extends Model
{
    protected $table = "sale_buyers";
    protected $fillable = [
        'buyer_id',
        'sale_id',
        'first_name',
        'last_name',
        'nick_name',
        'email',
    ];
}
