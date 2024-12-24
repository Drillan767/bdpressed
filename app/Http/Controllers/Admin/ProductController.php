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
        $products = Product::all([
            'id',
            'name',
            'slug',
            'price',
            'promotedImage',
            'quickDescription',
            'created_at',
            'updated_at',
        ]);
        return Inertia::render('Admin/Products/Index', compact('products'));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();
        return Inertia::render('Admin/Products/Show', compact('product'));
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

            $illustrationsPath[] = "/storage/articles/$product->id/{$illustration->getClientOriginalName()}";
        }

        $product->illustrations = $illustrationsPath;
        $product->promotedImage = "/storage/articles/$product->id/{$promotedImage->getClientOriginalName()}";

        $product->save();
    }

    public function addMedia(Request $request, Product $product): JsonResponse
    {
        $illustrations = $product->illustrations;
        $newImage = $request->file('illustration');
        $newImage->storeAs("articles/$product->id", $newImage->getClientOriginalName());

        $illustrations[] = "/storage/articles/$product->id/{$newImage->getClientOriginalName()}";
        $product->illustrations = $illustrations;

        $product->save();

        return response()->json(compact('product'));
    }

    public function removeMedia()
    {

    }
}
