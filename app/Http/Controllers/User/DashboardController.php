<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\IllustrationService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        // $orders = Order::where(['user_id' => $request->user()->id])->get(['id', 'reference', 'status', 'total', 'created_at', 'updated_at']);
        $orders = Order::with('shippingAddress', 'billingAddress', 'details.product', 'illustrations')
            ->where('user_id', $request->user()->id)
            ->get();

        return Inertia::render('User/Dashboard', compact('orders'));
    }

    public function showOrder(string $reference, IllustrationService $illustrationService): Response
    {
        $order = Order::with('shippingAddress', 'billingAddress', 'details.product', 'illustrations')
            ->where('reference', $reference)
            ->firstOrFail();

        $order->illustrationsList = $illustrationService->getOrderDetail($order->illustrations);

        return Inertia::render('User/Order/Show', compact('order'));
    }
}
