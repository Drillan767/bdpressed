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
        // dd($product->getOriginal('price')->cents / 100);
        return response()->json([
            ... $product->toArray(),
            'price' => $product->getOriginal('price')->cents / 100]);
    }

    public function store(Request $request): void
    {
        $product = new Product();
        $product->name = $request->get('name');
        $product->slug = Str::slug($request->get('name'));
        $product->weight = $request->get('weight');
        $product->stock = $request->get('stock');
        $product->price = $request->get('price') * 100;
        $product->quickDescription = $request->get('quickDescription');
        $product->description = $request->get('description');
        $product->promotedImage = '';
        $product->illustrations = [];
        $product->save();

        $promotedImage = $request->file('promotedImage');
        $promotedImage->storeAs("articles/$product->id", $promotedImage->getClientOriginalName());

        /** @var string[] $illustrationsPath */
        $illustrationsPath = [];

        if ($request->hasFile('illustrations')) {
            foreach($request->file('illustrations') as $illustration) {
                Storage::putFileAs(
                    "articles/{$product->id}",
                    $illustration,
                    $illustration->getClientOriginalName(),
                );

                $illustrationsPath[] = "/storage/articles/$product->id/{$illustration->getClientOriginalName()}";
            }
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
        $product->price = $request->get('price') * 100;
        $product->quickDescription = $request->get('quickDescription');
        $product->description = $request->get('description');

        if ($request->hasFile('promotedImage')) {
            $realPath = $realPath = str_replace('/storage/', '', $product->promotedImage);
            Storage::delete($realPath);

            $promotedImage = $request->file('promotedImage');
            $promotedImage->storeAs("articles/$product->id", $promotedImage->getClientOriginalName());

            $product->promotedImage = "/storage/articles/$product->id/{$promotedImage->getClientOriginalName()}";
        }

        // Handle illustrations if they are provided
        if ($request->hasFile('illustrations')) {
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
        // Get current illustrations or initialize as empty array if null
        $illustrations = $product->illustrations ?? [];

        // Extract paths from illustrations if they exist
        $illustrationsPaths = is_array($illustrations) ? array_column($illustrations, 'path') : [];

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

        // Get current illustrations or initialize as empty array if null
        $illustrations = $product->illustrations ?? [];

        // Extract paths from illustrations if they exist
        $illustrationsList = is_array($illustrations) ? array_column($illustrations, 'path') : [];

        $index = array_search("/storage/articles/$product->id/$base", $illustrationsList);
        if ($index !== false) {
            unset($illustrationsList[$index]);
        }

        $product->illustrations = array_values($illustrationsList); // Re-index the array
        $product->save();

        Storage::delete($realPath);

        return response()->json(['illustrations' => $product->illustrations]);
    }
}
