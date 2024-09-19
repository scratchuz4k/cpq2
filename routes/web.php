<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\DashboardController;


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('/orders/{order}/download', [OrderController::class, 'download'])->name('orders.download');
    Route::get('/orders/{order}/configure', [OrderController::class, 'configure'])->name('orders.configure');
    Route::resources([
        'orders' => OrderController::class,
        'account' => AccountController::class
    ]);

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});


require __DIR__ . '/auth.php';
