<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Settings\WebsiteSettings;
use Illuminate\Http\JsonResponse;
use App\Models\Comic;

class VisitorsController extends Controller
{
    public function landing(WebsiteSettings $settings)
    {
        $comics = Comic::where('is_published', true)->get([
            'id',
            'title',
            'preview',
        ]);

        return Inertia::render('Visitors/Landing', [
            'description_url' => $settings->comics_image_url,
            'description_text' => $settings->comics_text,
            'comics' => $comics,
        ]);
    }

    public function comicDetail(Comic $comic): JsonResponse
    {
        $comic->load('pages');

        return response()->json($comic);
    }

    public function contact(WebsiteSettings $settings)
    {
        return Inertia::render('Visitors/Contact', [
            'description_url' => $settings->contact_image_url,
            'description_text' => $settings->contact_text,
        ]);
    }

    public function paymentSuccess()
    {
        return Inertia::render('User/Payment/Success');
    }
}
