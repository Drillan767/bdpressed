<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancellationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    protected string $reason;

    public function __construct(Order $order, string $reason)
    {
        $this->order = $order;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
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
        return (new MailMessage)
            ->subject('Commande annulée - '.$this->order->reference)
            ->greeting('Bonjour,')
            ->line('Nous vous informons que votre commande a été annulée.')
            ->line('**Référence de commande :** '.$this->order->reference)
            ->line('**Raison de l\'annulation :** '.$this->reason)
            ->line('Si cette annulation fait suite à un paiement, le remboursement sera traité dans les meilleurs délais.')
            ->line('Pour toute question, n\'hésitez pas à nous contacter.')
            ->salutation('L\'équipe BD Pressed');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_reference' => $this->order->reference,
            'reason' => $this->reason,
        ];
    }
}
