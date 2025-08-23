<?php

namespace App\Observers;

use App\Models\IllustrationPrice;
use App\Services\StripeService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class IllustrationPriceObserver implements ShouldHandleEventsAfterCommit
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Handle the IllustrationPrice "created" event.
     */
    public function created(IllustrationPrice $illustrationPrice): void
    {
        $this->stripeService->handleIllustrationPriceCreation($illustrationPrice);
    }

    /**
     * Handle the IllustrationPrice "updated" event.
     */
    public function updated(IllustrationPrice $illustrationPrice): void
    {
        $this->stripeService->handleIllustrationPriceUpdate($illustrationPrice);
    }

    /**
     * Handle the IllustrationPrice "deleted" event.
     */
    public function deleted(IllustrationPrice $illustrationPrice): void
    {
        $this->stripeService->handleIllustrationPriceDeletion($illustrationPrice);
    }

    /**
     * Handle the IllustrationPrice "restored" event.
     */
    public function restored(IllustrationPrice $illustrationPrice): void
    {
        // Re-create the product in Stripe if needed
        $this->stripeService->handleIllustrationPriceCreation($illustrationPrice);
    }

    /**
     * Handle the IllustrationPrice "force deleted" event.
     */
    public function forceDeleted(IllustrationPrice $illustrationPrice): void
    {
        $this->stripeService->handleIllustrationPriceDeletion($illustrationPrice);
    }
}
