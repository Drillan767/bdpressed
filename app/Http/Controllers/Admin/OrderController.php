<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
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

    public function show(): Response
    {
        return Inertia::render('Admin/Orders/Show');
    }

    public function pendingOrders(): JsonResponse
    {
        $orders = Order::where('status', 'NEW')->count();
        return response()->json($orders);
    }
}
