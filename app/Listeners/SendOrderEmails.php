<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderConfirmationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendOrderEmails implements ShouldQueue
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

        if ($event->order->user) {
            $event->order->user->notify(new NewOrderNotification($event->order));
        }

        if ($event->order->guest) {
            Notification::route('mail', $event->order->guest->email)->notify(new OrderConfirmationNotification($event->order));
        }

        Notification::route('mail', config('mail.admin'))->notify(new NewOrderNotification($event->order));
    }
}
