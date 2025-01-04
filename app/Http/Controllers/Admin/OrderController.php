<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with('guest', 'user')->get([
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

    public function show(string $reference): Response
    {
        $order = Order::with(
            'guest.shippingAddress',
            'guest.billingAddress',
            'user.shippingAddress',
            'user.billingAddress',
            'details.product:id,name,promotedImage,slug,price,weight',
        )
            ->where('reference', $reference)
            ->firstOrFail();

        $totalWeight = $order->details->sum(function ($detail) {
            return $detail->product->weight;
        });

        return Inertia::render('Admin/Orders/Show', compact('order', 'totalWeight'));
    }

    public function pendingOrders(): JsonResponse
    {
        $orders = Order::where('status', 'NEW')->count();
        return response()->json($orders);
    }
}
