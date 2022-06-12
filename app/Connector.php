<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    protected $table = 'connectors';

    protected $fillable= ['connector', 'access_token', 'expires_in',
        'scope', 'refresh_token', 'api_id'];

    public function connectorCouponRequest()
    {
        return $this->hasMany('App\ConnectorCouponRequest','connector_id');
    }
}
