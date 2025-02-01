<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class IllustrationSettings extends Settings
{

    public int $bust_base;
    public int $bust_add_human;
    public int $bust_add_animal;
    public int $fl_base;
    public int $fl_add_human;
    public int $fl_add_animal;
    public int $animal_base;
    public int $annimal_add_one;
    public int $animal_toy;
    public int $option_pose_simple;
    public int $option_pose_complex;
    public int $option_bg_gradient;
    public int $option_bg_simple;
    public int $option_bg_complex;
    public int $options_print;
    public int $options_add_tracking;

    public static function group(): string
    {
        return 'illustration';
    }
}