<?php

namespace App\Observers;

use App\Models\IllustrationPrice;

class IllustrationPriceObserver
{
    /**
     * Handle the IllustrationPrice "created" event.
     */
    public function created(IllustrationPrice $illustrationPrice): void
    {
        //
    }

    /**
     * Handle the IllustrationPrice "updated" event.
     */
    public function updated(IllustrationPrice $illustrationPrice): void
    {
        //
    }

    /**
     * Handle the IllustrationPrice "deleted" event.
     */
    public function deleted(IllustrationPrice $illustrationPrice): void
    {
        //
    }

    /**
     * Handle the IllustrationPrice "restored" event.
     */
    public function restored(IllustrationPrice $illustrationPrice): void
    {
        //
    }

    /**
     * Handle the IllustrationPrice "force deleted" event.
     */
    public function forceDeleted(IllustrationPrice $illustrationPrice): void
    {
        //
    }
}
