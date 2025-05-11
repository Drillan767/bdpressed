<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\CreateComicRequest;
use App\Http\Controllers\Controller;
use App\Models\Comic;
use App\Models\ComicPage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


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
        dd($request);
        $comic = Comic::where('slug', $slug)->firstOrFail();

        $comic->update($request->all());
    }

    public function togglePublish(Comic $comic)
    {
        $status = $comic->is_published ? false : true;
        $comic->is_published = !$comic->is_published;
        $comic->save();

        return redirect()->back()->with('success', "Bédé " . ($status ? 'publiée' : 'dépubliée') . " avec succès");
    }

}
