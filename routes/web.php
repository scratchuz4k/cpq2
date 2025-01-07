<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\SpaController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PriceController;
use App\Http\Controllers\Web\QuoteController;
use App\Http\Controllers\Web\TokenController;
use App\Http\Controllers\Web\RegionController;
use App\Http\Controllers\Web\TenantController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\ApiConfigurationController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\SharedLinkController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/myaccount', [DashboardController::class, 'myAccount'])->name('myaccount');


    Route::get('/tenant/{tenant}/make-current', [TenantController::class, 'switch'])->name('tenant.make-current');

    Route::get('/quotes/{quote}/preview', [QuoteController::class, 'preview'])->name('quotes.preview');
    Route::get('/quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');
    Route::get('/quotes/{quote}/configure', [QuoteController::class, 'configure'])->name('quotes.configure');

    Route::get('/orders/{order}/preview', [OrderController::class, 'preview'])->name('orders.preview');
    Route::get('/orders/{order}/download', [OrderController::class, 'download'])->name('orders.download');

    Route::post('/users/import', [UserController::class, 'importUsers'])->name('users.import');

    Route::resources([
        'apis' => ApiConfigurationController::class,
        'prices' => PriceController::class,
        'quotes' => QuoteController::class,
        'orders' => OrderController::class,
        'accounts' => AccountController::class,
        'tenant' => TenantController::class,
        'brands' => BrandController::class,
        'products' => ProductController::class,
        'suppliers' => SupplierController::class,
        'regions' => RegionController::class,
        'spas' => SpaController::class,
        'users' => UserController::class,
        'tokens' => TokenController::class,
    ]);

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();

        // Check cache
        cache()->put('health_check', 'ok', 1);
        $cacheStatus = cache()->get('health_check') === 'ok';

        // Check storage
        $storageStatus = is_writable(storage_path('app'));

        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'cache' => $cacheStatus ? 'working' : 'not working',
            'storage' => $storageStatus ? 'writable' : 'not writable',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
        ], 500);
    }
})->name('health');

Route::get('/s/{token}', [SharedLinkController::class, 'show']);
Route::post('/s/{token}', [SharedLinkController::class, 'verifyPassword']);

require __DIR__ . '/auth.php';
