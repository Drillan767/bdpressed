<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VisitorsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});*/

Route::controller(VisitorsController::class)->group(function() {
    Route::get('/', 'landing')->name('landing');
    Route::get('/boutique', 'shop')->name('shop');
    Route::get('/contact', 'contact')->name('contact');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::get('/administration', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::prefix('/administration')->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::get('/articles', 'index')->name('products.index');
                Route::get('/article/{slug}', 'show')->name('products.show');
                Route::post('/article', 'store')->name('products.store');
                Route::post('/article/update-illustration/{product}', 'addMedia')->name('products.add-media');
                Route::delete('/article/remove-illustration/{product}', 'removeMedia')->name('products.remove-media');
            });
        });
    });
});

require __DIR__.'/auth.php';
