<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class NewOrderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $totalFees = $this->order->shipmentFees->euros() + $this->order->stripeFees->euros();
        $total = $totalFees + $this->order->total->euros();
        $formattedPrice = Number::currency($total, 'EUR', 'fr');

        return (new MailMessage)
            ->subject('Nouvelle commande !')
            ->greeting('Dinguerie ! 😱')
            ->line('Une nouvelle commande a été passée sur le site !')
            ->line(
                new HtmlString("Il y en a tout pour <b>$formattedPrice</b>, frais de port et de transaction compris.")
            )
            ->action('Voir la commande', route('orders.show', $this->order->reference))
            ->salutation('La bise et à bientôt !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
