<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Settings\WebsiteSettings;
use Inertia\Response;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function website(WebsiteSettings $settings): Response
    {
        return Inertia::render('Admin/Settings/Website', compact('settings'));
    }

    public function illustrations()
    {

    }

    public function updateWebsite(Request $request, WebsiteSettings $settings)
    {
        $settings->comics_text = $request->get('comics_text');
        $settings->save();

        return redirect()->back();
    }
}
