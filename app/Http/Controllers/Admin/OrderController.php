<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\IllustrationService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(OrderService $orderService): Response
    {
        $orders = Order::with(['guest', 'user', 'payments'])
            ->orderByDesc('created_at')
            ->get([
                'id',
                'reference',
                'status',
                'total',
                'shipmentFees',
                'guest_id',
                'user_id',
                'created_at',
                'updated_at',
            ]);

        // Add final amounts to each order
        $orders->each(function ($order) use ($orderService) {
            $order->final_amount = $orderService->getFinalAmount($order);
        });

        return Inertia::render('Admin/Orders/Index', compact('orders'));
    }

    public function show(
        string $reference,
        IllustrationService $illustrationService,
        OrderService $orderService
    ): Response {
        $order = Order::with([
            'details.product:id,name,promotedImage,slug,price,weight',
            'illustrations',
            'statusChanges',
            'payments',
        ])
            ->where('reference', $reference)
            ->firstOrFail();

        // Load guest or user relations based on which one exists
        if ($order->guest_id) {
            $order->load('guest.shippingAddress', 'guest.billingAddress');
            $order->client = $order->guest;
            unset($order->guest);
        } else {
            $order->load('user.shippingAddress', 'user.billingAddress');
            $order->client = $order->user;
            unset($order->user);
        }

        $order->illustrationsList = $illustrationService->getOrderDetail($order->illustrations);

        $estimatedFees = $orderService->calculateFees($order);

        $allowedStatuses = $order->getAvailableStatuses();
        $isIllustrationOnly = $order->isIllustrationOnlyOrder();

        $paymentHistory = OrderPayment::with(['order', 'illustration'])
            ->where('order_id', $order->id)
            ->whereNull('illustration_id') // Exclude illustration payments
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($payment) {
                $isIllustration = $payment->isForIllustration();

                // Determine item title
                if ($isIllustration && $payment->illustration) {
                    $title = "Illustration #{$payment->illustration->order->reference}";
                } else {
                    $title = "Commande #{$payment->order->reference}";
                }

                return [
                    'id' => $payment->id,
                    'title' => $title,
                    'type' => $payment->type->value,
                    'amount' => $payment->amount->formatted(),
                    'status' => $payment->status->value,
                    'paid_at' => $payment->paid_at?->format('d/m/Y à H:i'),
                    'payment_link' => $payment->status->value === 'pending' ? $payment->stripe_payment_link : null,
                    'is_illustration' => $isIllustration,
                ];
            });

        return Inertia::render('Admin/Orders/Show', compact(
            'order',
            'allowedStatuses',
            'estimatedFees',
            'isIllustrationOnly',
            'paymentHistory',
        ));
    }

    public function pendingOrders(): JsonResponse
    {
        $orders = Order::where('status', 'NEW')->count();

        return response()->json($orders);
    }

    public function updateStatus(
        Request $request,
        string $reference,
    ): RedirectResponse {
        $request->validate([
            'status' => 'required|string',
            'payload' => 'required_if:status,SHIPPED,CANCELLED',
        ]);

        try {
            $order = Order::with('details.product', 'illustrations', 'user', 'guest', 'payments')
                ->where('reference', $reference)
                ->firstOrFail();

            $newStatus = OrderStatus::from($request->get('status'));

            // Get additional context from the request (from status change warning component)
            $payload = $request->get('payload');

            $context = [
                'triggered_by' => 'manual',
                'reason' => $payload ?? 'Status changed manually',
                'cancellation_reason' => $newStatus === OrderStatus::CANCELLED ? $payload : null,
                'tracking_number' => $newStatus === OrderStatus::SHIPPED ? $payload : null,
                'user_id' => auth()->id(),
            ];

            // The transition will automatically trigger all registered actions
            $order->transitionTo($newStatus, $context);

            return back()->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order status: '.$e->getMessage());
        }
    }
}
