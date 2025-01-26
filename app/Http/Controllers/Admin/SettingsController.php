<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\WebsiteSettingsRequest;
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

    public function updateWebsite(WebsiteSettingsRequest $request, WebsiteSettings $settings)
    {
        $settings->comics_text = $request->get('comics_text');
        $settings->shop_title = $request->get('shop_title');
        $settings->shop_subtitle = $request->get('shop_subtitle');
        $settings->contact_text = $request->get('contact_text');

        if ($request->hasFile('comics_image_url')) {
            // Not defaut config.
            if (str_contains($settings->comics_image_url, '/storage')) {
                $realPath = $realPath = str_replace('/storage/', '', $settings->comics_image_url);
                Storage::delete($realPath);

                $file = $request->file('comics_image_url');
                $file->storeAs('settings', $file->getClientOriginalName());

                $settings->comics_image_url = "/storage/{$file->getClientOriginalName()}";
            }
        }

        $settings->save();

        return redirect()->back()->with('success', 'Paramètres enregistrés');
    }
}
