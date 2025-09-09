<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class PaymentConfirmationNotification extends Notification implements ShouldQueue
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
            ->subject("Paiement confirmé - Commande {$this->order->reference}")
            ->greeting('Merci pour votre paiement !')
            ->lines([
                'Nous avons bien reçu votre paiement pour la commande '.$this->order->reference.'.',
                'Votre commande va maintenant être expédiée dans les plus brefs délais.',
                'Vous recevrez un email de confirmation d\'expédition avec le numéro de suivi une fois votre commande envoyée.',
            ])
            ->line('Récapitulatif de votre commande :')
            ->line(new HtmlString($this->orderSummary()))
            ->action('Voir ma commande', url('/dashboard/commandes'))
            ->line('Merci de votre confiance !');
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
        ];
    }

    private function orderSummary(): string
    {
        $this->order->load('details.product');
        $html = '<div style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">';
        $html .= '<h3 style="margin-top: 0;">Commande '.$this->order->reference.'</h3>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';

        foreach ($this->order->details as $detail) {
            $html .= '<tr>';
            $html .= '<td style="padding: 5px;">'.$detail->product->name.'</td>';
            $html .= '<td style="padding: 5px; text-align: center;">'.$detail->quantity.'</td>';
            $html .= '<td style="padding: 5px; text-align: right;">'.Number::currency($detail->price->euros() * $detail->quantity, 'EUR', 'fr').'</td>';
            $html .= '</tr>';
        }

        if ($this->order->illustrations->count() > 0) {
            foreach ($this->order->illustrations as $i => $illustration) {
                $html .= '<tr>';
                $html .= '<td style="padding: 5px;">Illustration n°'.($i + 1).'</td>';
                $html .= '<td style="padding: 5px; text-align: center;">1</td>';
                $html .= '<td style="padding: 5px; text-align: right;">'.Number::currency($illustration->price->euros(), 'EUR', 'fr').'</td>';
                $html .= '</tr>';
            }
        }

        $totalFees = $this->order->shipmentFees->euros() + $this->order->stripeFees->euros();
        if ($totalFees > 0) {
            $html .= '<tr>';
            $html .= '<td colspan="2" style="padding: 5px; border-top: 1px solid #ddd;">Frais de port et paiement</td>';
            $html .= '<td style="padding: 5px; text-align: right; border-top: 1px solid #ddd;">'.Number::currency($totalFees, 'EUR', 'fr').'</td>';
            $html .= '</tr>';
        }

        $html .= '<tr style="font-weight: bold; border-top: 2px solid #333;">';
        $html .= '<td colspan="2" style="padding: 10px 5px;">Total payé</td>';
        $html .= '<td style="padding: 10px 5px; text-align: right;">'.Number::currency($this->order->total->euros(), 'EUR', 'fr').'</td>';
        $html .= '</tr>';

        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
