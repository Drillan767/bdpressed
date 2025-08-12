<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;

class AdminPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Order $order)
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
            ->subject("Nouveau paiement reçu - Commande {$this->order->reference}")
            ->greeting('Nouveau paiement confirmé !')
            ->lines([
                'Une nouvelle commande a été payée et est maintenant prête à être préparée.',
                'Référence de la commande : ' . $this->order->reference,
                'Montant payé : ' . $this->order->total->formatted(),
                'Date du paiement : ' . $this->order->paid_at,
            ])
            ->action('Voir la commande', url('/administration/commandes/' . $this->order->reference))
            ->lines([
                'N\'oubliez pas de mettre à jour le statut de la commande une fois celle-ci préparée et expédiée.',
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_reference' => $this->order->reference,
            'total' => $this->order->total,
            'paid_at' => $this->order->paid_at,
        ];
    }
}
