<?php

use App\Http\Controllers\Api\OrderItemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ExternalController;
use App\Http\Controllers\Api\SolutionTypeController;

Route::prefix("external")->group(function () {
    Route::post("get-address-by-postcode", [ExternalController::class, "getAddressesByPostCode"]);
    Route::post("get-position-by-address", [ExternalController::class, "getPositionByAddress"]);
    Route::post("get-articles-from-shop-blocks", [ExternalController::class, "getArticlesFromShopBlock"]);
});


Route::apiResources([
    "articles" => ArticleController::class,
    "addresses" => AddressController::class,
    "orders" => OrderController::class,
    "order-items" => OrderItemController::class,
    "solution-types" => SolutionTypeController::class,
]);
