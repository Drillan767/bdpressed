<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('website.comics_image_url', 'https://bedeprimee.josephlevarato.me/assets/images/yell.png');
        $this->migrator->add('website.comics_text', "Bienvenue dans mon journal intime pas très intime, mi burn-out mi révolution pantoufle.\n\nIci règne le désordre mental et l'auto sabotage glorieux ¥");
        $this->migrator->add('website.shop_title', 'Mes petites créations à vendre :');
        $this->migrator->add('website.shop_subtitle', '¥ Tu reçois des payettes, je reçois de quoi payer ma psy ¥');
        $this->migrator->add('website.contact_image_url', 'https://bedeprimee.josephlevarato.me/assets/images/yell.png');
        $this->migrator->add('website.contact_text', 'Une question ? une demande ? N\'hésitez plus ! ¥');
    }
};
