<?php

namespace App\Http\Controllers;

use App\Jobs\EvaluateSaleJob;
use Illuminate\Http\Request;

class SaleController extends Controller
{

    public function store(Request $request)
    {
        EvaluateSaleJob::dispatch($request->resource,$request->topic);
    }
}
