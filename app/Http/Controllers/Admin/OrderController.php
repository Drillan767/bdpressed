<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Inertia\Response;
use App\Settings\WebsiteSettings;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Services\IllustrationService;
use Illuminate\Http\JsonResponse;

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
}
