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
    public function index(Request $request, IllustrationService $illustrationService): Response
    {
        // $orders = Order::where(['user_id' => $request->user()->id])->get(['id', 'reference', 'status', 'total', 'created_at', 'updated_at']);
        $orders = Order::withCount('details', 'illustrations')
            ->where('user_id', $request->user()->id)
            ->get();

        return Inertia::render('User/Dashboard', compact('orders'));
    }

    public function showOrder(string $reference, IllustrationService $illustrationService): Response
    {
        $rawOrder = Order::with('shippingAddress', 'billingAddress', 'details.product', 'illustrations')
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
                'totalPrice' => (float) $illustration->price,
                'status' => $illustration->status,
                'details' => $illustrationService->getSingleIllustrationDetail($illustration),
            ]);
        }

        $order = [
            'id' => $rawOrder->id,
            'total' => $rawOrder->total,
            'reference' => $rawOrder->reference,
            'status' => $rawOrder->status,
            'shipmentFees' => $rawOrder->shipmentFees,
            'stripeFees' => $rawOrder->stripeFees,
            'created_at' => $rawOrder->created_at->format('d/m/Y à H:i'),
            'addionalInfos' => $rawOrder->additionalInfos,
            'itemCount' => $items->count(),
            'shippingAddress' => $rawOrder->shippingAddress,
            'billingAddress' => $rawOrder->billingAddress,
            'items' => $items->values()->toArray(),
        ];

        return Inertia::render('User/Order/Show', compact('order'));
    }
}
