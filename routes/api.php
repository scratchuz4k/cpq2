<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ExternalController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SolutionTypeController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SpaController;
use App\Http\Controllers\Auth\CreateTokenController;

Route::prefix("external")->group(function () {
    Route::post("get-address-by-postcode", [ExternalController::class, "getAddressesByPostCode"]);
    Route::post("get-position-by-address", [ExternalController::class, "getPositionByAddress"]);
    Route::get("get-articles-from-cloud-market", [ExternalController::class, "getArticlesFromCloudMarket"]);
    Route::post("get-articles-from-shop-blocks", [ExternalController::class, "getArticlesFromShopBlocks"]);
});


Route::apiResources([
    "products" => ProductController::class,
    "addresses" => AddressController::class,
    "orders" => OrderController::class,
    "order-items" => OrderItemController::class,
    "solution-types" => SolutionTypeController::class,
    "permissions" => PermissionController::class,
    "accounts" => AccountController::class,
    "brands" => BrandController::class,
    "suppliers" => SupplierController::class,
    "regions" => RegionController::class,
    "spas" => SpaController::class
]);

Route::controller(CreateTokenController::class)->group(function () {
    Route::post('create/token', 'create');
});
