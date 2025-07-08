<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\CreateComicRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Comic;
use App\Models\ComicPage;
use Illuminate\Support\Str;

class ComicController extends Controller
{
    public function index(): Response
    {
        $comics = Comic::withCount('pages')->get();
        return Inertia::render('Admin/Comics/Index', compact('comics'));
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Comics/Create');
    }

    public function store(CreateComicRequest $request)
    {
        $comic = Comic::create([
            'title' => $request->get('title'),
            'slug' => Str::slug($request->get('title')),
            'preview' => '',
            'description' => $request->get('description'),
            'instagram_url' => $request->get('instagram_url'),
            'is_published' => $request->get('is_published'),
        ]);

        $preview = $request->file('preview');

        Storage::putFileAs(
            "comics/{$comic->id}",
            $preview,
            'preview.' . $preview->getClientOriginalExtension(),
        );

        $comic->save();

        foreach ($request->file('images') as $i => $image) {
            $fileName = 'page-' . ($i + 1) . '.' . $image->getClientOriginalExtension();

            ComicPage::create([
                'comic_id' => $comic->id,
                'image' => "/storage/comics/{$comic->id}/{$fileName}",
                'order' => $i + 1,
            ]);

            Storage::putFileAs(
                "comics/{$comic->id}",
                $image,
                $fileName,
            );
        }

        return redirect()->route('admin.comics.index')->with('success', 'Bédé créée avec succès');
    }

    public function edit(string $slug): Response
    {
        $comic = Comic::where('slug', $slug)
            ->with(['pages' => function($query) {
                $query->orderBy('order');
            }])
            ->firstOrFail();

        return Inertia::render('Admin/Comics/Edit', compact('comic'));
    }

    public function update(Request $request, string $slug)
    {
        $comic = Comic::where('slug', $slug)->firstOrFail();

        // Update comic basic info
        $comic->update([
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'instagram_url' => $request->get('instagram_url'),
        ]);

        // Handle preview image update
        if ($request->hasFile('preview')) {
            $preview = $request->file('preview');
            $previewPath = "/storage/comics/{$comic->id}/preview." . $preview->getClientOriginalExtension();

            Storage::putFileAs(
                "comics/{$comic->id}",
                $preview,
                'preview.' . $preview->getClientOriginalExtension(),
            );

            $comic->update(['preview' => $previewPath]);
        }

        // Handle deleted pages
        if ($request->has('deleted_pages')) {
            $deletedPageIds = $request->get('deleted_pages');
            ComicPage::whereIn('id', $deletedPageIds)->delete();
        }

        // Handle existing pages reordering
        if ($request->has('existing_images')) {
            $existingImages = $request->get('existing_images');
            $existingImagesOrder = $request->get('existing_images_order');

            foreach ($existingImages as $index => $pageId) {
                ComicPage::where('id', $pageId)->update([
                    'order' => $existingImagesOrder[$index]
                ]);
            }
        }

        // Handle new images
        if ($request->hasFile('new_images')) {
            $newImages = $request->file('new_images');
            $newImagesOrder = $request->get('new_images_order', []);

            foreach ($newImages as $index => $image) {
                $fileName = 'page-' . time() . '-' . $index . '.' . $image->getClientOriginalExtension();
                $order = $newImagesOrder[$index] ?? ($comic->pages()->max('order') + 1);

                ComicPage::create([
                    'comic_id' => $comic->id,
                    'image' => "/storage/comics/{$comic->id}/{$fileName}",
                    'order' => $order,
                ]);

                Storage::putFileAs(
                    "comics/{$comic->id}",
                    $image,
                    $fileName,
                );
            }
        }

        return redirect()->route('admin.comics.index')->with('success', 'Bédé mise à jour avec succès');
    }

    public function togglePublish(string $slug)
    {
        $comic = Comic::where('slug', $slug)->firstOrFail();
        $status = $comic->is_published ? false : true;
        $comic->is_published = !$comic->is_published;
        $comic->save();

        return redirect()->back()->with('success', "Bédé " . ($status ? 'publiée' : 'dépubliée') . " avec succès");
    }

    public function destroy(string $slug)
    {
        $comic = Comic::where('slug', $slug)->firstOrFail();

        // Delete all pages
        $comic->pages()->delete();

        // Delete all files in the comic's directory
        Storage::deleteDirectory("comics/{$comic->id}");

        // Delete the comic
        $comic->delete();

        return redirect()->route('admin.comics.index')->with('success', 'Bédé supprimée avec succès');
    }

}
