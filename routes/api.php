<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SpaController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ExternalController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\QuoteItemController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SolutionTypeController;
use App\Http\Controllers\Api\ApiConfigurationController;
use App\Http\Controllers\Api\SharedLinkController;

Route::prefix("external")->group(function () {
    Route::post("get-address-by-postcode", [ExternalController::class, "getAddressesByPostCode"]);
    Route::post("get-position-by-address", [ExternalController::class, "getPositionByAddress"]);
    Route::post("get-articles-from-cloud-market", [ExternalController::class, "getArticlesFromCloudMarket"]);
    Route::post("get-articles-from-shop-blocks", [ExternalController::class, "getArticlesFromShopBlocks"]);
});


Route::apiResources([
    'apis' => ApiConfigurationController::class,
    'accounts' => AccountController::class,
    'contacts' => ContactController::class,
    'products' => ProductController::class,
    'quotes' => QuoteController::class,
    'quote-items' => QuoteItemController::class,
    'solution-types' => SolutionTypeController::class,
    'permissions' => PermissionController::class,
    'brands' => BrandController::class,
    'prices' => PriceController::class,
    'suppliers' => SupplierController::class,
    'regions' => RegionController::class,
    'spas' => SpaController::class,
    'users' => UserController::class,
    'tokens' => TokenController::class,
    'shared-links' => SharedLinkController::class,
]);
