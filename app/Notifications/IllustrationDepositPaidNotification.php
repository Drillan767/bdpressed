<?php

namespace App\Notifications;

use App\Models\Illustration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IllustrationDepositPaidNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Illustration $illustration) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bédéprimée - Acompte reçu, travail commencé !')
            ->greeting('Bonjour!')
            ->line('Nous avons bien reçu votre acompte pour l\'illustration personnalisée.')
            ->line('Le travail sur votre illustration a maintenant commencé !')
            ->line('Vous recevrez une notification lorsque l\'illustration sera prête pour validation.')
            ->line('Merci pour votre confiance !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'illustration_id' => $this->illustration->id,
        ];
    }
}
