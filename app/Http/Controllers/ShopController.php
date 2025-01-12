<?php

namespace App\Http\Controllers;

use App\Actions\Order\RegisterClientAction;
use App\Actions\Order\HandleGuestAction;
use App\Actions\Order\HandleOrderAction;
use App\Actions\Order\HandleAddressesAction;
use App\Events\OrderCreated;
use App\Http\Requests\OrderRequest;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(): Response
    {
        $products = Product::where('stock', '>', 0)->get([
            'id',
            'name',
            'slug',
            'stock',
            'price',
            'weight',
            'promotedImage',
            'quickDescription',
        ]);

        return Inertia::render('Visitors/Shop/Index', compact('products'));
    }

    public function show(string $slug): Response
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        return Inertia::render('Visitors/Shop/Show', compact('product'));
    }

    public function checkout(): Response
    {
        $addresses = Address::where([
            'user_id' => Auth::id(),
            'guest_id' => null,
        ])->get();

        return Inertia::render('Visitors/Shop/Checkout', [
            'user_addresses' => $addresses,
        ]);
    }

    public function order(OrderRequest $request)
    {
        if ($request->user()) {
            if ($request->has('addresses.shippingId')) {
                $shippingId = $request->get('addresses')['shippingId'];
                $billingId = $request->get('addresses')['billingId'];

                $order = (new HandleOrderAction())->handle($request, false, $request->user()->id, [
                    'shipping' => $shippingId,
                    'billing' => $billingId,
                    'same' => $shippingId === $billingId,
                ]);
            } else {
                $addressesInfos = (new HandleAddressesAction(false, $request->user()->id))->handle($request);
                $order = (new HandleOrderAction())->handle($request, false, $request->user()->id, $addressesInfos);
            }

            event(new OrderCreated($order, false));
        } else {
            $guest = $request->get('user')['guest'];

            $clientId = $guest
                ? (new HandleGuestAction())->handle($request)
                : (new RegisterClientAction())->handle($request);

            $addressesInfos = (new HandleAddressesAction($guest, $clientId))->handle($request);

            $order = (new HandleOrderAction())->handle($request, $guest, $clientId, $addressesInfos);

            event(new OrderCreated($order, !$guest));
        }

        return redirect()->route('shop.thankYou');
    }

    public function thankYou(): Response
    {
        return Inertia::render('Visitors/Shop/ThankYou');
    }
}
