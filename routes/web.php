<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PriceController;
use App\Http\Controllers\Web\TenantController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\RegionController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\SpaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/myaccount', [DashboardController::class, 'myAccount'])->name('myaccount');


    Route::get('/tenant/{tenant}/make-current', [TenantController::class, 'switch'])->name('tenant.make-current');

    Route::get('/orders/{order}/preview', [OrderController::class, 'preview'])->name('orders.preview');
    Route::get('/orders/{order}/download', [OrderController::class, 'download'])->name('orders.download');
    Route::get('/orders/{order}/configure', [OrderController::class, 'configure'])->name('orders.configure');

    Route::post('/users/import', [UserController::class, 'importUsers'])->name('users.import');

    Route::resources([
        'prices' => PriceController::class,
        'orders' => OrderController::class,
        'accounts' => AccountController::class,
        'tenant' => TenantController::class,
        'brands' => BrandController::class,
        'products' => ProductController::class,
        'suppliers' => SupplierController::class,
        'regions' => RegionController::class,
        'spas' => SpaController::class,
        'users' => UserController::class
    ]);

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});


require __DIR__ . '/auth.php';
