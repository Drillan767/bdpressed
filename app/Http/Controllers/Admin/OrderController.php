<?php

namespace App\Http\Controllers\Admin;

use Inertia\Inertia;
use Inertia\Response;
use App\Models\Illustration;
use Illuminate\Support\Collection;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Settings\IllustrationSettings;

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
            'illustrations'
        )
            ->where('reference', $reference)
            ->firstOrFail();

        $totalWeight = $order->details->sum(function ($detail) {
            return $detail->product->weight;
        });

        dd($this->getOrderDetail($order->illustrations));

        return Inertia::render('Admin/Orders/Show', compact('order', 'totalWeight'));
    }

    public function pendingOrders(): JsonResponse
    {
        $orders = Order::where('status', 'NEW')->count();
        return response()->json($orders);
    }

    private function getOrderDetail(Collection $illustrations): array
    {
        $settings = app(IllustrationSettings::class);

        dd($settings, $illustrations);
    }
}
