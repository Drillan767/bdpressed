<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\ComicRequest;
use App\Http\Controllers\Controller;
use App\Models\Comic;
use App\Models\ComicPage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class ComicController extends Controller
{
    public function index(): Response
    {
        $comics = Comic::all();
        return Inertia::render('Admin/Comics/Index', compact('comics'));
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Comics/Create');
    }

    public function store(ComicRequest $request)
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

        $comic->preview = "storage/comics/{$comic->id}/preview.{$preview->getClientOriginalExtension()}";
        $comic->save();

        foreach ($request->file('images') as $i => $image) {
            $fileName = 'page-' . ($i + 1) . '.' . $image->getClientOriginalExtension();

            ComicPage::create([
                'comic_id' => $comic->id,
                'image' => "storage/comics/{$comic->id}/{$fileName}",
                'order' => $i + 1,
            ]);

            Storage::putFileAs(
                "comics/{$comic->id}",
                $image,
                $fileName,
            );
        }

        return redirect()->route('comics.index')->with('success', 'Bédé créée avec succès');
    }
    
}
