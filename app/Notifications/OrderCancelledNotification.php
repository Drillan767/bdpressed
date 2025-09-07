<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class OrderCancelledNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        private readonly Order $order,
        private readonly bool $refunded,
        private readonly string $reason
    )
    {}

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
        $orderRef = $this->order->reference;
        $cancellationReason = $this->reason ?? $this->order->getCancellationReason() ?? 'Non spécifiée';

        $message = new MailMessage()
            ->greeting('Bonjour !')
            ->line(new HtmlString("Vous recevez cet email car votre commande <b>#$orderRef</b> a été annulée pour la raison suivante :"))
            ->line(new HtmlString("<em>$cancellationReason</em>"));

        if ($this->refunded) {
            $message->line('Un remboursement a été initié et sera traité dans les prochains jours ouvrables.');
        }

        return $message
            ->action('Voir ma commande', url("/orders/{$this->order->reference}"))
            ->line('Merci de votre compréhension.');
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
