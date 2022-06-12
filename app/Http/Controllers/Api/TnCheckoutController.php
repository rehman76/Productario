<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TnCheckoutUrlResource;
use App\TnCheckoutUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TnCheckoutController extends Controller
{
    /**
     * @param $tnCheckoutUrl
     * @return TnCheckoutUrlResource
     */
    public function getTnCheckoutDetails(TnCheckoutUrl $tnCheckoutUrl)
    {
        $tnCheckoutUrl->increment('clicks');

        return new TnCheckoutUrlResource($tnCheckoutUrl);
    }
}
