<?php

namespace App\Http\Controllers\Admin;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Settings\WebsiteSettings;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Services\IllustrationService;
use App\Services\OrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Enums\OrderStatus;

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
    ): Response
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

        $order->illustrationsList = $illustrationService->getOrderDetail($order->illustrations);

        $estimatedFees = $orderService->calculateFees($order);

        $allowedStatuses = $order->getAvailableStatuses();

        return Inertia::render('Admin/Orders/Show', compact(
            'order',
            'allowedStatuses',
            'estimatedFees',
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
        OrderStatusService $orderStatusService,
    ): RedirectResponse
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        try {
            $order = Order::with('details.product', 'illustrations', 'user', 'guest', 'payments')
                ->where('reference', $reference)
                ->firstOrFail();

            $newStatus = OrderStatus::from($request->get('status'));

            $order->transitionTo($newStatus);

            // Notify the status service of the change
            $orderStatusService->changed($order, $newStatus);

            return back()->with('success', 'Order status updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update order status: ' . $e->getMessage());
        }
    }
}
