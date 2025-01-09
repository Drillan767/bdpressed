<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::where(['user_id' => $request->user()->id])->get(['id', 'reference', 'status', 'total', 'created_at', 'updated_at']);

        return Inertia::render('User/Dashboard', compact('orders'));
    }
}
