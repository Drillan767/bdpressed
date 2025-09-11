<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShippingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    protected string $trackingNumber;

    public function __construct(Order $order, string $trackingNumber)
    {
        $this->order = $order;
        $this->trackingNumber = $trackingNumber;
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
            ->subject('Commande expédiée - '.$this->order->reference)
            ->greeting('Bonjour,')
            ->line('Excellente nouvelle ! Votre commande a été expédiée.')
            ->line('**Référence de commande :** '.$this->order->reference)
            ->line('**Numéro de suivi :** '.$this->trackingNumber)
            ->line('Vous pouvez suivre votre colis sur le site de La Poste avec le numéro de suivi ci-dessus.')
            ->action('Suivre mon colis', 'https://www.laposte.fr/particulier/outils/suivre-vos-envois?code='.$this->trackingNumber)
            ->line('Merci pour votre commande !')
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
            'tracking_number' => $this->trackingNumber,
        ];
    }
}
