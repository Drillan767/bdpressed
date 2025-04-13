<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\ComicRequest;
use App\Http\Controllers\Controller;

class ComicController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Comics/Index');
    }

    public function store(ComicRequest $request)
    {
        
    }
    
}
