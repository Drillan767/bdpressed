<?php

namespace App\Notifications;

use App\Models\Illustration;
use App\Models\OrderPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IllustrationDepositLinkNotification extends Notification
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
            ->subject('Bédéprimée - Acompte pour votre illustration personnalisée')
            ->greeting('Bonjour!')
            ->line("Votre demande d'illustration personnalisée a été acceptée par l'artiste !")
            ->line('Pour commencer le travail, un acompte de 50% est requis.')
            ->line('Montant de l\'acompte: '.$this->payment->amount->formatted())
            ->action('Payer l\'acompte', url($this->payment->stripe_payment_link))
            ->line('Une fois l\'acompte reçu, le travail sur votre illustration commencera.')
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
