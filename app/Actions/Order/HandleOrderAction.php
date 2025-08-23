<?php

namespace App\Actions\Order;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Http\Requests\OrderRequest;
use App\Models\Illustration;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Services\IllustrationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HandleOrderAction
{
    public function handle(OrderRequest $request, bool $guest, int $authId, array $addressesInfos): Order
    {
        $productIds = array_column($request->get('products'), 'id');
        $products = Product::whereIn('id', $productIds)->get(['id', 'price', 'weight']);

        [
            'shipping' => $shippingId,
            'billing' => $billingId,
            'same' => $useSame,
        ] = $addressesInfos;

        $illustrationService = new IllustrationService;
        [$totalPrice, $shipFee] = $this->definePrice($products, $request->get('products'), $illustrationService);

        $order = new Order;
        $order->total = $totalPrice;
        $order->shipmentFees = $shipFee;
        $order->reference = $this->defineReference();
        $order->additionalInfos = $request->get('user')['additionalInfos'];

        if ($guest) {
            $order->guest_id = $authId;
        } else {
            $order->user_id = $authId;
        }

        $order->shipping_address_id = $shippingId;
        $order->billing_address_id = $billingId ?? $shippingId;
        $order->useSameAddress = $useSame;
        $order->status = OrderStatus::NEW;

        $order->save();

        foreach ($request->get('products') as $product) {
            if ($product['type'] === 'item') {
                $this->handleItemOrder($product, $products, $order);
            } else {
                $this->handleIllustrationOrder($product['illustrationDetails'], $order, $illustrationService);
            }
        }

        return $order;
    }

    private function definePrice(Collection $products, array $referenceProducts, IllustrationService $illustrationService): array
    {
        $totalPrice = 0;
        $totalWeight = 0;

        foreach ($referenceProducts as $refProduct) {

            if ($refProduct['type'] === 'illustration') {
                $totalWeight += 15;
                // Calculate price server-side
                $totalPrice += $illustrationService->calculateIllustrationPrice($refProduct['illustrationDetails']);

            } else {
                $product = $products->firstWhere('id', $refProduct['id']);
                if ($product) {
                    $quantity = $refProduct['quantity'];
                    $totalPrice += $product->price->cents() * $quantity;
                    $totalWeight += $product->weight * $quantity;
                }
            }
        }

        // Convert shipping to cents
        $shipFee = $totalWeight > 400 ? 700 : 400; // 700 cents = €7, 400 cents = €4

        return [
            $totalPrice, // Only products + illustrations, no fees
            $shipFee,
        ];
    }

    /**
     * Ensures reference is unique.
     */
    private function defineReference(): string
    {
        $orders = Order::all(['reference']);

        $reference = strtoupper(Str::random(8));

        while ($orders->contains('reference', $reference)) {
            $reference = strtoupper(Str::random(8));
        }

        return $reference;
    }

    private function handleItemOrder($product, $products, $order): void
    {
        $orderDetail = new OrderDetail;
        $orderDetail->order_id = $order->id;
        $orderDetail->product_id = $product['id'];
        $orderDetail->quantity = $product['quantity'];
        $orderDetail->price = $products->firstWhere('id', $product['id'])->price->cents() * $product['quantity'];

        $orderDetail->save();
    }

    private function handleIllustrationOrder(array $details, $order, IllustrationService $illustrationService): void
    {
        $type = match ($details['illustrationType']) {
            'bust' => 'bust',
            'fl' => 'full_length',
            'animal' => 'animal',
        };

        $illustration = new Illustration;
        $illustration->type = $type;
        $illustration->nbHumans = $details['addedHuman'];
        $illustration->nbAnimals = $details['addedAnimal'];
        $illustration->pose = $details['pose'];
        $illustration->background = $details['background'];
        $illustration->price = $illustrationService->calculateIllustrationPrice($details);
        $illustration->print = $details['print'];
        $illustration->addTracking = $details['addTracking'];
        $illustration->status = IllustrationStatus::PENDING;
        $illustration->description = $details['description'];
        $illustration->order_id = $order->id;

        $illustration->save();
    }
}
