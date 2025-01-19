<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebsiteSettings extends Settings
{

    public string $comics_image_url;
    public string $comics_text;
    public string $shop_title;
    public string $shop_subtitle;
    public string $contact_image_url;
    public string $contact_text;

    public static function group(): string
    {
        return 'website';
    }
}