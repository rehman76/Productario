<?php

use Bakerkretzmar\NovaSettingsTool\Http\Controllers\SettingsToolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'checkUserBelongsToVendor', 'checkVendorAccountStatus'])->group(function () {
    Route::apiResource('/products',Api\VendorProductController::class)->only(['store','destroy','update']);
    Route::post('/products/bulk', [App\Http\Controllers\Api\VendorProductController::class, 'bulkUpdateOrCreateProduct']);
    Route::delete('/products/bulk/delete',[App\Http\Controllers\Api\VendorProductController::class, 'deleteBulkProductsBySku']);
//    Route::apiResource('/products', 'Api\ProductController');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('users','Api\UserController@store');
});
Route::get('/tn_checkout_details/{tnCheckoutUrl}', 'Api\TnCheckoutController@getTnCheckoutDetails');


Route::post('mercadolibre/callbacks', 'SaleController@store');
