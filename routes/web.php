<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\User\AddressesController;
use App\Http\Controllers\VisitorsController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::controller(VisitorsController::class)->group(function() {
    Route::get('/', 'landing')->name('landing');
    Route::get('/contact', 'contact')->name('contact');
});

Route::controller(ShopController::class)->group(function() {
    Route::get('/boutique', 'index')->name('shop.index');
    Route::get('/boutique/{slug}', 'show')->name('shop.show');
    Route::get('/checkout', 'checkout')->name('shop.checkout');
    Route::post('/checkout', 'order')->name('shop.order');
    Route::get('/merci', 'thankYou')->name('shop.thankYou');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/informations-personnelles', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::get('/administration', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        Route::prefix('/administration')->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::get('/articles', 'index')->name('products.index');
                Route::get('/article/api/{product}', 'showApi')->name('products.showApi');
                Route::get('/article/{slug}', 'show')->name('products.show');
                Route::post('/article', 'store')->name('products.store');
                Route::put('/article/{product}', 'update')->name('products.update');
                Route::delete('/article/{product}', 'destroy')->name('products.destroy');

                Route::post('/article/update-illustration/{product}', 'addMedia')->name('products.add-media');
                Route::delete('/article/remove-illustration/{product}', 'removeMedia')->name('products.remove-media');
            });

            Route::controller(OrderController::class)->group(function () {
                Route::get('/commandes', 'index')->name('orders.index');
                Route::get('/commandes/pending-orders', 'pendingOrders')->name('orders.pending');
                Route::get('/commande/{reference}', 'show')->name('orders.show');
            });
        });
    });

    Route::middleware('role:user')->prefix('/utilisateur')->group(function () {
        Route::get('', [UserDashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/commande/{reference}', [UserDashboardController::class, 'showOrder'])->name('user.order.show');

        Route::controller(AddressesController::class)->group(function () {
            Route::get('/adresses', 'index')->name('user.addresses.index');
        });
    });
});

require __DIR__.'/auth.php';
