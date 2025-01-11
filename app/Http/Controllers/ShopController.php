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

        return Inertia::render('Visitors/Shop/Checkout', compact('addresses'));
    }

    public function order(OrderRequest $request)
    {
        // TODO: handle request if user is already authenticated.

        $guest = $request->get('user')['guest'];

        if ($guest) {
            $clientId = (new HandleGuestAction())->handle($request);
        } else {
            $clientId = (new RegisterClientAction())->handle($request);
        }

        $addressesInfos = (new HandleAddressesAction($guest, $clientId))->handle($request);

        $order = (new HandleOrderAction())->handle($request, $guest, $clientId, $addressesInfos);

        // TODO: render 2nd parameter dynamic once we have a way to know if the user is already registered.
        event(new OrderCreated($order, !$guest));

        return redirect()->route('shop.thankYou');
    }

    public function thankYou(): Response
    {
        return Inertia::render('Visitors/Shop/ThankYou');
    }
}
