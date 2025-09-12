<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderPaymentLinkNotification extends Notification
{
    use Queueable, HasFactory;

    /**
     * Create a new notification instance.
     */
    public function __construct(private readonly string $paymentLink)
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
        return (new MailMessage)
            ->subject('Bédéprimée - Lien de paiement')
            ->greeting('Bonjour!')
            ->line('Votre commande est enfin prête à être envoyée !')
            ->line('Pour finaliser votre commande, veuillez cliquer sur le lien ci-dessous:')
            ->action('Accéder au paiement', url($this->paymentLink))
            ->line('Merci encore pour la commande !');
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
