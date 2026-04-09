<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
});
