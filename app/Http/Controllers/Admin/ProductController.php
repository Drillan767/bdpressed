<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::all(['id', 'name', 'slug', 'price', 'promotedImage', 'quickDescription']);
        return Inertia::render('Admin/Products/Index', compact('products'));
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        return response()->json(compact('product'));
    }

    public function store(Request $request): void
    {
        $product = new Product();
        $product->name = $request->get('name');
        $product->slug = Str::slug($request->get('name'));
        $product->weight = $request->get('weight');
        $product->price = $request->get('price');
        $product->quickDescription = $request->get('quickDescription');
        $product->description = $request->get('description');
        $product->promotedImage = '';
        $product->illustrations = '';
        $product->save();

        $promotedImage = $request->file('promotedImage');
        $promotedImage->storeAs("articles/$product->id", $promotedImage->getClientOriginalName());

        /** @var string[] $illustrationsPath */
        $illustrationsPath = [];

        foreach($request->file('illustrations') as $illustration) {
            Storage::putFileAs(
                "articles/{$product->id}",
                $illustration,
                $illustration->getClientOriginalName(),
            );

            $illustrationsPath[] = "articles/$product->id/{$illustration->getClientOriginalName()}";
        }

        $product->illustrations = $illustrationsPath;
        $product->promotedImage = "articles/$product->id/{$promotedImage->getClientOriginalName()}";;

        $product->save();
    }
}
