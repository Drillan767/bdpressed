<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\ComicRequest;
use App\Http\Controllers\Controller;
use App\Models\Comic;
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
        
    }
    
}
