<?php

namespace App\Notifications;

use App\Models\Illustration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IllustrationCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Illustration $illustration) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject('Bédéprimée - Votre illustration est terminée !')
            ->greeting('Bonjour!')
            ->line('Votre illustration personnalisée est maintenant terminée !')
            ->line('Nous avons bien reçu le paiement final.')
            ->line('Vous pouvez maintenant télécharger votre illustration.');

        // If it's a print order, mention shipping
        if ($this->illustration->print) {
            $mailMessage->line('Votre illustration sera également imprimée et expédiée selon vos préférences.');
        }

        $mailMessage->line('Merci d\'avoir choisi Bédéprimée pour votre illustration personnalisée !');

        return $mailMessage;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'illustration_id' => $this->illustration->id,
        ];
    }
}
