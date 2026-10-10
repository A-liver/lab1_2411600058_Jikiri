<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryTransactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\InventoryApiController;
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('transactions', InventoryTransactionController::class)
    ->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::get('/products-export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('products', ProductController::class);
    
    Route::post('/dashboard/simulate', [DashboardController::class, 'simulate'])->name('dashboard.simulate');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/dashboard/stats', [InventoryApiController::class, 'stats'])->name('dashboard.stats');
        Route::get('/products/low-stock', [InventoryApiController::class, 'lowStock'])->name('products.lowStock');
        Route::get('/products/{product}', [InventoryApiController::class, 'product'])->name('products.show');
        Route::get('/transactions/recent', [InventoryApiController::class, 'recent'])->name('transactions.recent');
    });
});

require __DIR__ . '/auth.php';