<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        if (app()->environment() === 'testing') {
            return;
        }

        new StripeService()->createProduct($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        new StripeService()->updateProduct($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        new StripeService()->deleteProduct($product);
    }
}
