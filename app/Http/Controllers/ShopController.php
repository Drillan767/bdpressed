<?php

namespace App\Http\Controllers;

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
}
