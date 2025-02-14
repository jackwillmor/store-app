<?php

use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/shops', 'as' => 'shops.'], function () {
    Route::post('/', [ShopController::class, 'store']);
    Route::get('/nearby', [ShopController::class, 'getNearbyShops']);
    Route::get('/delivering', [ShopController::class, 'getShopsDeliveringToPostcode']);
});
