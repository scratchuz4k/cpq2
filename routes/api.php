<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\Cloudaudit\OrganizationController;
use App\Http\Controllers\Api\Cloudmarket\AddressController as CloudmarketAddressController;
use App\Http\Controllers\Api\Cloudmarket\ProductController as CloudmarketProductController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EmailHistoryController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\ExternalController;
use App\Http\Controllers\Api\General\BlacklistController;
use App\Http\Controllers\Api\GroupAccountController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PriceController;
use App\Http\Controllers\Api\PricePlanController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\QuoteItemController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SharedLinkController;
use App\Http\Controllers\Api\ShopBlocks\BrandController as ShopBlocksBrandController;
use App\Http\Controllers\Api\ShopBlocks\CategoryController as ShopBlocksCategoryController;
use App\Http\Controllers\Api\ShopBlocks\DimensionController;
use App\Http\Controllers\Api\ShopBlocks\DimensionTypeController;
use App\Http\Controllers\Api\ShopBlocks\ModelController as ShopBlocksModelController;
use App\Http\Controllers\Api\ShopBlocks\ProductController as ShopBlocksProductController;
use App\Http\Controllers\Api\ShopBlocks\ProductDimensionController;
use App\Http\Controllers\Api\SolutionTypeController;
use App\Http\Controllers\Api\SpaController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\IntegrateAuthController;
use Illuminate\Support\Facades\Route;

Route::post('generate-auth-token', [IntegrateAuthController::class, 'createToken']);

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::prefix('shop-blocks')->group(callback: function () {
        Route::get('dimensions', [DimensionController::class, 'index']);
        Route::get('dimension-types', [DimensionTypeController::class, 'index']);
        Route::get('models', [ShopBlocksModelController::class, 'index']);
        Route::get('brands', [ShopBlocksBrandController::class, 'index']);
        Route::get('products', [ShopBlocksProductController::class, 'index']);
        Route::get('products/{article}', [ShopBlocksProductController::class, 'show']);
        Route::get('product-dimensions', [ProductDimensionController::class, 'index']);
        Route::get('categories', [ShopBlocksCategoryController::class, 'index']);
    });

    Route::prefix('cloudmarket')->group(callback: function () {
        Route::get('address', [CloudmarketAddressController::class, 'index']);
        Route::get('products', [CloudmarketProductController::class, 'index']);
        Route::get('products/failover', [CloudmarketProductController::class, 'failover']);
        Route::get('products/addons', [CloudmarketProductController::class, 'addons']);
        Route::get('products/options', [CloudmarketProductController::class, 'options']);
        Route::get('products/assignments', [CloudmarketProductController::class, 'assignments']);
        Route::get('products/save-quote', [CloudmarketProductController::class, 'saveQuote']);
    });

    Route::prefix('cloudaudit')->group(callback: function () {
        Route::apiResource('organizations', OrganizationController::class);
    });

    Route::prefix('external')->group(function () {
        Route::post('get-address-by-postcode', [ExternalController::class, 'getAddressesByPostCode']);
        Route::post('get-position-by-address', [ExternalController::class, 'getPositionByAddress']);
    });

    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/export', [ReportController::class, 'export']);
        Route::get('/columns', [ReportController::class, 'getColumns']);
    });

    Route::apiResources([
        'apis' => ApiController::class,
        'activity' => ActivityController::class,
        'price-plans' => PricePlanController::class,
        'accounts' => AccountController::class,
        'contacts' => ContactController::class,
        'categories' => CategoryController::class,
        'products' => ProductController::class,
        'quotes' => QuoteController::class,
        'quote-items' => QuoteItemController::class,
        'orders' => OrderController::class,
        'order-items' => OrderItemController::class,
        'solution-types' => SolutionTypeController::class,
        'permissions' => PermissionController::class,
        'prices' => PriceController::class,
        'suppliers' => SupplierController::class,
        'spas' => SpaController::class,
        'users' => UserController::class,
        'tokens' => TokenController::class,
        'shared-links' => SharedLinkController::class,
        'resources' => ResourceController::class,
        'tenants' => TenantController::class,
        'blacklists' => BlacklistController::class,
        'email-templates' => EmailTemplateController::class,
        'email-history' => EmailHistoryController::class,
        'groups' => GroupController::class,
        'group-accounts' => GroupAccountController::class,
        'permissions' => PermissionController::class,
        'roles' => RoleController::class,
        'imports' => ImportController::class,
    ]);
});
