<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Illustration;
use Inertia\Inertia;
use Inertia\Response;

class IllustrationsController extends Controller
{
    public function index(): Response
    {
        $illustrations = Illustration::all();
        return Inertia::render('Admin/Illustrations/Index', compact('illustrations'));  
    }

    public function show(Illustration $illustration): Response
    {
        return Inertia::render('Admin/Illustrations/Show', compact('illustration'));
    }
}