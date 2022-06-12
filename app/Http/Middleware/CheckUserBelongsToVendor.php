<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Api\VendorProductController;
use Closure;

class CheckUserBelongsToVendor
{
    protected $vendorProductController;

    public function __construct(VendorProductController $vendorProductController)
    {
        $this->vendorProductController = $vendorProductController;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->user()->vendor)
        {
            return response()->json(['message' => "The user not belongs to any vendor"], 401);
        }
        return $next($request);
    }
}
