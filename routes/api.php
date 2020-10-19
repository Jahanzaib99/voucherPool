<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\Api\CustomerOfferController;
use \App\Http\Controllers\Api\OffersController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'offer'], function () {
    Route::post('store', [OffersController::class, 'store']);
    Route::post('get', [OffersController::class, 'index']);
    Route::post('update', [OffersController::class, 'update']);
    Route::get('show/{offer_id}', [OffersController::class, 'show']);
    Route::delete('delete/{offer_id}', [OffersController::class, 'destroy']);
});

Route::post('generateVoucher', [CustomerOfferController::class, 'generateVoucher']);
Route::get('voucher/details', [CustomerOfferController::class, 'voucherDetails']);
Route::get('customer/vouchers', [CustomerOfferController::class, 'customerVouchers']);
