<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\StripeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class ProductObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $stripeService = new StripeService();
        $stripeService->createProduct($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $stripeService = new StripeService();
        $stripeService->updateProduct($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $stripeService = new StripeService();
        $stripeService->deleteProduct($product);
    }
}
