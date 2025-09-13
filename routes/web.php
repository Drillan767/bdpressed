<?php

use App\Http\Controllers\Admin\ComicController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IllustrationsController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\User\AddressesController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\VisitorsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Health check route for deployment
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()]);
});

// Stripe webhook - must be outside auth and CSRF middleware
Route::post('/stripe/webhook', [WebhookController::class, 'handleStripe'])
    ->withoutMiddleware(['web'])
    ->name('stripe.webhook');

/** Debug purpose
Route::get('/notification', function () {
     $mail = App\Notifications\NewOrderNotification::class;
     $order = App\Models\Order::find(3);

     return new $mail($order)->toMail(\App\Models\User::find(2));
});
 */
Route::controller(VisitorsController::class)->group(function () {
    Route::get('/', 'landing')->name('landing');
    Route::get('/contact', 'contact')->name('contact');
    Route::get('/comic/{comic}', 'comicDetail')->name('comic.details');
    Route::get('/paiement-effectue', 'paymentSuccess')->name('payment.success');
});

Route::controller(ShopController::class)->middleware('holiday_mode')->group(function () {
    Route::get('/boutique', 'index')->name('shop.index');
    Route::get('/boutique/illustration', 'illustration')->name('shop.illustration');
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

        Route::prefix('/administration/statistics')->group(function () {
            Route::get('/financial', [AdminDashboardController::class, 'getFinancialStatistics'])->name('admin.statistics.financial');
            Route::get('/business-performance', [AdminDashboardController::class, 'getBusinessPerformanceStatistics'])->name('admin.statistics.business-performance');
            Route::get('/products-comics', [AdminDashboardController::class, 'getProductsAndComicsStatistics'])->name('admin.statistics.products-comics');
            Route::get('/customer-analytics', [AdminDashboardController::class, 'getCustomerAnalytics'])->name('admin.statistics.customer-analytics');
            Route::get('/operational', [AdminDashboardController::class, 'getOperationalStatistics'])->name('admin.statistics.operational');
            Route::get('/all', [AdminDashboardController::class, 'getAllStatistics'])->name('admin.statistics.all');
        });

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
                Route::post('/commande/{reference}/update-status', 'updateStatus')->name('orders.update-status');
            });

            Route::controller(IllustrationsController::class)->group(function () {
                Route::get('/illustrations', 'index')->name('admin.illustrations.index');
                Route::get('/illustration/{illustration}', 'show')->name('admin.illustrations.show');
                Route::post('/illustration/{illustration}/update-status', 'updateStatus')->name('admin.illustrations.update-status');
            });

            Route::controller(ComicController::class)->group(function () {
                Route::get('/comics', 'index')->name('admin.comics.index');
                Route::get('/comic/nouveau', 'create')->name('admin.comics.create');
                Route::get('/comic/{slug}', 'edit')->name('admin.comics.edit');
                Route::post('/comic', 'store')->name('admin.comics.store');
                Route::put('/comic/{slug}', 'update')->name('admin.comics.update');
                Route::delete('/comic/{slug}', 'destroy')->name('admin.comics.destroy');
                Route::post('/comic/{slug}/toggle-publish', 'togglePublish')->name('admin.comics.toggle-publish');
            });

            Route::controller(SettingsController::class)->prefix('/parametres')->group(function () {
                Route::get('/site', 'website')->name('settings.website');
                Route::get('/illustration', 'illustration')->name('settings.illustration');
                Route::post('/website', 'updateWebsite')->name('settings.website.update');
                Route::post('/illustration', 'updateIllustration')->name('settings.illustration.update');
            });
        });
    });

    Route::middleware('role:user')->prefix('/utilisateur')->group(function () {
        Route::get('', [UserDashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/commande/{reference}', [UserDashboardController::class, 'showOrder'])->name('user.order.show');

        Route::controller(AddressesController::class)->group(function () {
            Route::get('/adresses', 'index')->name('user.addresses.index');
            Route::post('/address', 'store')->name('user.addresses.store');
            Route::put('/address/{address}', 'update')->name('user.addresses.update');
            Route::delete('/address/{address}', 'destroy')->name('user.addresses.destroy');
            Route::post('/addresses/default', 'updateDefaultAddress')->name('user.addresses.update-default');
        });
    });
});

require __DIR__.'/auth.php';
