<?php

namespace App\Http\Controllers;

use App\Actions\Order\RegisterClientAction;
use App\Actions\Order\HandleGuestAction;
use App\Actions\Order\HandleOrderAction;
use App\Actions\Order\HandleAddressesAction;
use App\Http\Requests\OrderRequest;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    public function index(): Response
    {
        $products = Product::all([
            'id',
            'name',
            'slug',
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
        return Inertia::render('Visitors/Shop/Checkout');
    }

    public function order(OrderRequest $request)
    {
        $guest = $request->get('user')['guest'];

        if ($guest) {
            $clientId = (new HandleGuestAction())->handle($request);
        } else {
            $clientId = (new RegisterClientAction())->handle($request);
        }

        $addressesInfos = (new HandleAddressesAction($guest, $clientId))->handle($request);

        (new HandleOrderAction())->handle($request, $guest, $clientId, $addressesInfos);

        /*DB::transaction(function () use ($request) {
            // Create user and send verification email

        });*/

        return redirect()->route('shop.thankYou');
    }

    public function thankYou(): Response
    {
        return Inertia::render('Visitors/Shop/ThankYou');
    }
}
