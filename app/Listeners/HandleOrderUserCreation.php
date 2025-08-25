<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleOrderUserCreation implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        if ($event->accountCreated) {
            event(new Registered($event->order->user));
        }
    }
}
