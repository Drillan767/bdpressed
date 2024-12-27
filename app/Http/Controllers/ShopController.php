<?php

namespace App\Http\Controllers;

use App\Actions\Order\RegisterClientAction;
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
        DB::transaction(function () use ($request) {
            // Create user and send verification email
            $user = (new RegisterClientAction())->handle($request);
        });

        /*
            {
                user: {
                    email: 'test@test.com',
                    password: 'password',
                    phone: '0123456789',
                },
                products: [
                    {
                        id: 1,
                        quantity: 1,
                    },
                    {
                        id: 2,
                        quantity: 1,
                    },
                ],
                // If user not logged in
                shippingAddress: {
                    firstName: 'test',
                    lastName: 'test',
                    street: 'test',
                    city: 'test',
                    zipCode: 'test',
                    country: 'test',
                    
                },
                billingAddress: {
                    firstName: 'test',
                    lastName: 'test',
                    street: 'test',
                    city: 'test',
                    zipCode: 'test',
                    country: 'test',
                },

                // If user logged in
                billing_id: 1,
                shipping_id: 1,
            }
        */
        dd($request);
        return redirect()->route('shop.thankYou');
    }

    public function thankYou(): Response
    {
        return Inertia::render('Visitors/Shop/ThankYou');
    }
}
