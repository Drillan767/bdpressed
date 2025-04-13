<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('website.shipping_default_weight', 150);
        $this->migrator->add('website.illustration_weight', 50);
    }
};
