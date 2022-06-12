<?php

namespace App\Http\Controllers;

use App\Jobs\CategoriesImportFromWooCommerceJob;
use App\Services\WooCommerceService;
use Illuminate\Http\Request;
use Codexshaper\WooCommerce\Facades\Product as WooProduct; //Documentation: https://codexshaper.github.io/docs/laravel-woocommerce/
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Codexshaper\WooCommerce\Facades\WooCommerce;


class WooController extends Controller
{
    protected $wooCommerceService;
    public function __construct(WooCommerceService $wooCommerceService){
        $this->wooCommerceService = $wooCommerceService;
    }

    public function importProductCategories()
    {
        CategoriesImportFromWooCommerceJob::dispatch();

        return 'Job Successfully Dispatched';
    }
}
