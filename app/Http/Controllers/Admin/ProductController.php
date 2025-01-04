<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
            'weight',
            'slug',
            'price',
            'stock',
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

    public function showApi(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function store(Request $request): void
    {
        $product = new Product();
        $product->name = $request->get('name');
        $product->slug = Str::slug($request->get('name'));
        $product->weight = $request->get('weight');
        $product->stock = $request->get('stock');
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

    public function update(Request $request, Product $product): void
    {
        $product->name = $request->get('name');
        $product->slug = Str::slug($request->get('name'));
        $product->weight = $request->get('weight');
        $product->stock = $request->get('stock');
        $product->price = $request->get('price');
        $product->quickDescription = $request->get('quickDescription');
        $product->description = $request->get('description');

        if ($request->hasFile('promotedImage')) {
            $realPath = $realPath = str_replace('/storage/', '', $product->promotedImage);
            Storage::delete($realPath);

            $promotedImage = $request->file('promotedImage');
            $promotedImage->storeAs("articles/$product->id", $promotedImage->getClientOriginalName());

            $product->promotedImage = "/storage/articles/$product->id/{$promotedImage->getClientOriginalName()}";
        }

        $product->save();
    }

    public function destroy(Product $product): void
    {
        Storage::disk('local')->deleteDirectory("articles/$product->id");
        $product->delete();

    }

    public function addMedia(Request $request, Product $product): JsonResponse
    {
        $illustrations = $product->illustrations;

        $illustrationsPaths = array_column($illustrations, 'path');

        foreach ($request->file('illustrations') as $illustration) {
            Storage::putFileAs(
                "articles/{$product->id}",
                $illustration,
                $illustration->getClientOriginalName(),
            );

            $illustrationsPaths[] = "/storage/articles/$product->id/{$illustration->getClientOriginalName()}";
        }

        $product->illustrations = $illustrationsPaths;
        $product->save();

        return response()->json(['illustrations' => $product->illustrations]);
    }

    public function removeMedia(Request $request, Product $product): JsonResponse
    {

        $realPath = str_replace('/storage/', '', $request->get('illustration'));
        $base = basename($realPath);
        $illustrationsList = array_column($product->illustrations, 'path');
        $index = array_search("/storage/articles/$product->id/$base", $illustrationsList);
        unset($illustrationsList[$index]);

        $product->illustrations = $illustrationsList;
        $product->save();

        Storage::delete($realPath);

        return response()->json(['illustrations' => $product->illustrations]);
    }
}
