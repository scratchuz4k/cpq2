<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ExternalController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\QuoteItemController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\SharedLinkController;
use App\Http\Controllers\Api\SolutionTypeController;
use App\Http\Controllers\Api\SpaController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('external')->group(function () {
    Route::post('get-address-by-postcode', [ExternalController::class, 'getAddressesByPostCode']);
    Route::post('get-position-by-address', [ExternalController::class, 'getPositionByAddress']);
    Route::post('get-articles-from-cloud-market', [ExternalController::class, 'getArticlesFromCloudMarket']);
    Route::post('get-articles-from-shop-blocks', [ExternalController::class, 'getArticlesFromShopBlocks']);
});

Route::get('/quotes/{quote}/create_order', [QuoteController::class, 'createOrder'])->name('quotes.order');

Route::apiResources([
    'apis' => ApiController::class,
    'accounts' => AccountController::class,
    'contacts' => ContactController::class,
    'categories' => CategoryController::class,
    'products' => ProductController::class,
    'quotes' => QuoteController::class,
    'quote-items' => QuoteItemController::class,
    'quoteitems' => QuoteItemController::class,
    'orders' => OrderController::class,
    'order-items' => OrderItemController::class,
    'solution-types' => SolutionTypeController::class,
    'permissions' => PermissionController::class,
    'brands' => BrandController::class,
    'merchants' => MerchantController::class,
    'prices' => PriceController::class,
    'suppliers' => SupplierController::class,
    'regions' => RegionController::class,
    'spas' => SpaController::class,
    'users' => UserController::class,
    'tokens' => TokenController::class,
    'shared-links' => SharedLinkController::class,
    'resources' => ResourceController::class,
    'tenants' => TenantController::class,
    'email-templates' => EmailTemplateController::class,
]);
