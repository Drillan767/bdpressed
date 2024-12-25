<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class VisitorsController extends Controller
{
    public function landing()
    {
        return Inertia::render('Visitors/Landing');
    }

    public function contact()
    {
        return Inertia::render('Visitors/Contact');
    }
}
