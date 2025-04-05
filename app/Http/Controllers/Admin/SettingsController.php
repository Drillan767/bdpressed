<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\IllustrationSettingsRequest;
use App\Http\Requests\WebsiteSettingsRequest;
use App\Settings\WebsiteSettings;
use App\Models\IllustrationPrice;
use Inertia\Response;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function website(WebsiteSettings $settings): Response
    {
        return Inertia::render('Admin/Settings/Website', compact('settings'));
    }

    public function updateWebsite(WebsiteSettingsRequest $request, WebsiteSettings $settings)
    {
        $settings->comics_text = $request->get('comics_text');
        $settings->shop_title = $request->get('shop_title');
        $settings->shop_subtitle = $request->get('shop_subtitle');
        $settings->contact_text = $request->get('contact_text');

        $settings->comics_image_url = $this->uploadFile($request, $settings, 'comics_image_url');
        $settings->contact_image_url = $this->uploadFile($request, $settings, 'contact_image_url');

        $settings->save();

        return redirect()->back()->with('success', 'Paramètres enregistrés');
    }

    public function illustration()
    {
        $settings = IllustrationPrice::all();
        return Inertia::render('Admin/Settings/Illustration', compact('settings'));
    }

    public function updateIllustration(IllustrationSettingsRequest $request)
    {
        $data = $request->all();
        
        // Get all illustration prices
        $settings = IllustrationPrice::all();
        
        // Update only the values that have changed
        foreach ($settings as $setting) {
            if (isset($data[$setting->key]) && $data[$setting->key] != $setting->price) {
                $setting->price = $data[$setting->key];
                $setting->save();
            }
        }

        return redirect()->back()->with('success', 'Paramètres enregistrés');
    }

    private function uploadFile(WebsiteSettingsRequest $request, WebsiteSettings $settings, string $field): string
    {
        if ($request->hasFile($field)) {
            if (str_contains($settings->{$field}, '/storage')) {
                $realPath = $realPath = str_replace('/storage', '', $settings->{$field});
                Storage::delete($realPath);
            }

            $file = $request->file($field);
            $file->storeAs('settings', $file->getClientOriginalName());

            return "/storage/settings/{$file->getClientOriginalName()}";

        } else {
            return $settings->{$field};
        }
    }
}
