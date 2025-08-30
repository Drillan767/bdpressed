<?php

namespace App\Notifications;

use App\Models\Illustration;
use App\Models\OrderPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IllustrationFinalLinkNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Illustration $illustration,
        private readonly OrderPayment $payment
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bédéprimée - Paiement final pour votre illustration')
            ->greeting('Bonjour!')
            ->line('Votre illustration personnalisée est terminée et approuvée !')
            ->line('Pour recevoir l\'illustration finale, veuillez procéder au paiement final.')
            ->line('Montant restant: '.$this->payment->amount->formatted())
            ->action('Paiement final', url($this->payment->stripe_payment_link))
            ->line('Une fois le paiement reçu, vous recevrez votre illustration.')
            ->line('Merci pour votre confiance !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'illustration_id' => $this->illustration->id,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount->cents(),
        ];
    }
}
