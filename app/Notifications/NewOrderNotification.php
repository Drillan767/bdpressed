<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use NumberFormatter;

class NewOrderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Order $order,
    )
    {
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
        $total = new NumberFormatter('fr_FR', NumberFormatter::CURRENCY);
        $totalPrice = $this->order->total + $this->order->total * 0.015 + 0.25;
        $formattedPrice = $total->formatCurrency($totalPrice, 'EUR');

        return (new MailMessage)
            ->subject('Nouvelle commande !')
            ->greeting('Dinguerie ! 😱')
            ->line('Une nouvelle commande a été passée sur le site !')
            ->line(
                new HtmlString("Il y en a tout pour <b>$formattedPrice</b>, frais de port et de transaction compris.")
            )
            ->action('Voir la commande', route('orders.show', $this->order->reference))
            ->salutation('La bise et à bientôt !')
        ;
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
