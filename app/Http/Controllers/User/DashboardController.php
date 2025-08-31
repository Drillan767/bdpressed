<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\IllustrationService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request, OrderService $orderService): Response
    {
        $orders = Order::withCount('illustrations')
            ->with('payments', 'details:id,order_id,quantity')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        // Add final amounts to each order
        $orders->each(function ($order) use ($orderService) {
            $order->details_count = array_sum($order->details->pluck('quantity')->toArray());
            $order->final_amount = $orderService->getFinalAmount($order);
        });

        return Inertia::render('User/Dashboard', compact('orders'));
    }

    public function showOrder(
        string $reference,
        OrderService $orderService,
        IllustrationService $illustrationService
    ): Response {
        $rawOrder = Order::with('shippingAddress', 'billingAddress', 'details.product', 'illustrations', 'payments')
            ->where('reference', $reference)
            ->firstOrFail();

        $items = collect();

        // Add product items
        foreach ($rawOrder->details as $detail) {
            $items->push([
                'type' => 'product',
                'title' => $detail->product->name,
                'description' => $detail->product->quickDescription,
                'price' => $detail->product->price,
                'totalPrice' => $detail->quantity * $detail->product->price->euros(),
                'quantity' => $detail->quantity,
                'image' => $detail->product->promotedImage,
            ]);
        }

        // Add illustration items
        foreach ($rawOrder->illustrations as $key => $illustration) {
            $illustrationNumber = $key + 1;
            $items->push([
                'type' => 'illustration',
                'title' => "Illustration n°$illustrationNumber",
                'description' => $illustration->description ?: 'Illustration personnalisée',
                'quantity' => 1,
                'image' => '/assets/images/yell.png',
                'totalPrice' => (float) $illustration->price->euros(),
                'status' => $illustration->status,
                'details' => $illustrationService->getSingleIllustrationDetail($illustration),
            ]);
        }

        $estimatedFees = $orderService->calculateFees($rawOrder);

        $order = [
            'id' => $rawOrder->id,
            'total' => $rawOrder->total,
            'reference' => $rawOrder->reference,
            'status' => $rawOrder->status,
            'shipmentFees' => $rawOrder->shipmentFees,
            'created_at' => $rawOrder->created_at->format('d/m/Y à H:i'),
            'addionalInfos' => $rawOrder->additionalInfos,
            'itemCount' => $items->count(),
            'shippingAddress' => $rawOrder->shippingAddress,
            'billingAddress' => $rawOrder->billingAddress,
            'items' => $items->values()->toArray(),
            'estimatedFees' => $estimatedFees,
            'finalAmount' => $orderService->getFinalAmount($rawOrder),
        ];

        return Inertia::render('User/Order/Show', compact('order'));
    }

    public function paymentHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get all payments for user's orders (including illustration payments)
        $payments = OrderPayment::with(['order', 'illustration'])
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->get();

        // Transform payments for frontend display
        $paymentHistory = $payments->map(function ($payment) {
            $isIllustration = $payment->isForIllustration();

            // Determine item title
            if ($isIllustration && $payment->illustration) {
                $title = "Illustration #{$payment->illustration->order->reference}";
            } else {
                $title = "Commande #{$payment->order->reference}";
            }

            return [
                'id' => $payment->id,
                'order_reference' => $payment->order->reference,
                'title' => $title,
                'type' => $payment->type->value,
                'amount' => $payment->amount->formatted(),
                'status' => $payment->status->value,
                'paid_at' => $payment->paid_at?->format('d/m/Y à H:i'),
                'payment_link' => $payment->status->value === 'pending' ? $payment->stripe_payment_link : null,
                'is_illustration' => $isIllustration,
            ];
        });

        return response()->json($paymentHistory);
    }
}
