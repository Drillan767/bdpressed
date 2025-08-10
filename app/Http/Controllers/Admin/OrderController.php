<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Settings\WebsiteSettings;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Services\IllustrationService;
use App\Services\OrderStatusService;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Enums\OrderStatus;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with('guest', 'user')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'reference',
                'status',
                'total',
                'guest_id',
                'user_id',
                'created_at',
                'updated_at',
            ]);

        return Inertia::render('Admin/Orders/Index', compact('orders'));
    }

    public function show(string $reference, IllustrationService $illustrationService): Response
    {
        $websiteSettings = app(WebsiteSettings::class);
        $order = Order::with([
            'details.product:id,name,promotedImage,slug,price,weight',
            'illustrations'
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

        $totalWeight = $order->details->sum(function ($detail) {
            return $detail->product->weight;
        });

        $totalWeight += $order->illustrations->count() * $websiteSettings->illustration_weight;
        $totalWeight += $websiteSettings->shipping_default_weight;

        $order->illustrationsList = $illustrationService->getOrderDetail($order->illustrations);

        return Inertia::render('Admin/Orders/Show', compact('order', 'totalWeight'));
    }

    public function pendingOrders(): JsonResponse
    {
        $orders = Order::where('status', 'NEW')->count();
        return response()->json($orders);
    }

    public function updateStatus(
        Request $request,
        string $reference,
        OrderStatusService $orderStatusService,
        StripeService $stripeService
    ): RedirectResponse {
        $request->validate([
            'status' => 'required|string'
        ]);

        $order = Order::with('details.product', 'illustrations', 'user', 'guest')
            ->where('reference', $reference)
            ->firstOrFail();

        $newStatus = OrderStatus::from($request->status);

        // Notify the status service of the change
        $orderStatusService->changed($order, $newStatus);

        return back()->with('success', 'Order status updated successfully');
    }
}
