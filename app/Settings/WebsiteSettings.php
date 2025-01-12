<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebsiteSettings extends Settings
{

    public static function group(): string
    {
        return 'website';
    }
}