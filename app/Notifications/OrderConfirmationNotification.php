<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class OrderConfirmationNotification extends Notification
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
            ->subject('Commande confirmée !')
            ->lines([
                'Merci énormément pour votre commande !',
                'Comme indiqué sur le site, vous n\'aurez à payer qu\'au moment où la commande sera prête pour l\'expédition.',
                'Vous recevrez alors un email vous permettant de payer votre commande.',
            ])
            ->line('Détail de votre commande :')
            ->line(new HtmlString($this->orderTable($this->order)))
            ->linesIf(
                $this->order->guest()->exists(), [
                    'Vos informations personnelles seront supprimées automatiquement si la commande est annulée ou 2 semaines après qu\'elle soit terminée.',
                    'Si vous souhaitez les conserver, il vous suffira de créer un compte en utilisant la même adresse e-mail que celle utilisée pour la commande.',
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
            //
        ];
    }

    private function orderTable(Order $order): string
    {
        $order->load('details.product');
        $table = '<table style="width: 100%">';
        $table .= '<tr><th style="text-align: left">Nom</th><th style="text-align: right">Quantité</th><th style="text-align: right">Prix</th></tr>';

        foreach ($order->details as $detail) {
            $table .= "<tr><td>{$detail->product->name}</td><td style='text-align: right'>{$detail->quantity}</td><td style='text-align: right'>".Number::currency($detail->price->euros(), 'EUR', 'fr').'</td></tr>';
        }

        foreach ($order->illustrations as $i => $illustration) {
            $nb = $i + 1;
            $table .= "<tr><td>Illustration n°$nb</td><td style='text-align: right'>1</td><td style='text-align: right'>".Number::currency($illustration->price->euros(), 'EUR', 'fr').'</td></tr>';
        }

        $totalFees = $order->shipmentFees->euros() + $order->stripeFees->euros();

        // Fees
        $table .= '<tr><td colspan="2" style="text-align: left">Frais de livraison (estimés)</td><td style="text-align: right">'.Number::currency($totalFees, 'EUR', 'fr').'</td></tr>';

        $total = $totalFees + $order->total->euros();
        // Total
        $table .= '<tr style="font-weight: bold"><td colspan="2" style="text-align: left">Total</td><td style="text-align: right">'.Number::currency($total, 'EUR', 'fr').'</td></tr>';

        $table .= '</table>';

        return $table;
    }
}
