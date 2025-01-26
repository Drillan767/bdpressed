<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Settings\WebsiteSettings;

class VisitorsController extends Controller
{
    public function landing(WebsiteSettings $settings)
    {
        return Inertia::render('Visitors/Landing', [
            'description_url' => $settings->comics_image_url,
            'description_text' => $settings->comics_text,
        ]);
    }

    public function contact(WebsiteSettings $settings)
    {
        return Inertia::render('Visitors/Contact', [
            'description_url' => $settings->contact_image_url,
            'description_text' => $settings->contact_text,
        ]);
    }
}
