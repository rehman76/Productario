<?php

namespace App\Http\Middleware;

use App\Services\Constants;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckVendorAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::user()->vendor['account_status'] == 1)
        {
            return $next($request);
        }

            return response()->json(['message'=>"Your account has been suspended"],403);
    }
}
