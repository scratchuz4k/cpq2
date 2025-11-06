<?php

use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\ActivityController;
use App\Http\Controllers\Web\ApiController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmailHistoryController;
use App\Http\Controllers\Web\EmailTemplateController;
use App\Http\Controllers\Web\General\BlacklistController;
use App\Http\Controllers\Web\GroupAccountController;
use App\Http\Controllers\Web\GroupController;
use App\Http\Controllers\Web\ImportController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\QuoteController;
use App\Http\Controllers\Web\SharedLinkController;
use App\Http\Controllers\Web\SolutionTypeController;
use App\Http\Controllers\Web\SpaController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\TenantController;
use App\Http\Controllers\Web\TokenController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

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

Route::post('/quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/myaccount', [DashboardController::class, 'myAccount'])->name('myaccount');
    Route::get('/reporting', [DashboardController::class, 'reporting'])->name('reporting');

    Route::get('/tenant/{tenant}/make-current', [TenantController::class, 'switch'])->name('tenant.make-current');
    Route::get('/quotes/{quote}/preview', [QuoteController::class, 'preview'])->name('quotes.preview');

    Route::get('/quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');
    Route::get('/quotes/{quote}/configure', [QuoteController::class, 'configure'])->name('quotes.configure');
    Route::post('/quotes/{quote}/email', [QuoteController::class, 'email'])->name('quotes.email');

    Route::get('/orders/{order}/preview', [OrderController::class, 'preview'])->name('orders.preview');
    Route::get('/orders/{order}/download', [OrderController::class, 'download'])->name('orders.download');

    Route::get('/users/import', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/import', [UserController::class, 'importUsers'])->name('users.import');

    Route::resources([
        'email-templates' => EmailTemplateController::class,
        'email-history' => EmailHistoryController::class,
        'accounts' => AccountController::class,
        'categories' => CategoryController::class,
        'apis' => ApiController::class,
        'quotes' => QuoteController::class,
        'orders' => OrderController::class,
        'tenants' => TenantController::class,
        'products' => ProductController::class,
        'suppliers' => SupplierController::class,
        'spas' => SpaController::class,
        'users' => UserController::class,
        'tokens' => TokenController::class,
        'solution-types' => SolutionTypeController::class,
        'blacklists' => BlacklistController::class,
        'groups' => GroupController::class,
        'group-accounts' => GroupAccountController::class,
        'activities' => ActivityController::class,
        'roles' => RoleController::class,
        'imports' => ImportController::class,
    ]);

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
