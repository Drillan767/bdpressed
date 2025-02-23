<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('illustration.bust_base', 30);
        $this->migrator->add('illustration.bust_add_human', 25);
        $this->migrator->add('illustration.bust_add_animal', 15);

        $this->migrator->add('illustration.fl_base', 40);
        $this->migrator->add('illustration.fl_add_human', 30);
        $this->migrator->add('illustration.fl_add_animal', 15);

        $this->migrator->add('illustration.animal_base', 25);
        $this->migrator->add('illustration.animal_add_one', 15);
        $this->migrator->add('illustration.animal_toy', 5);

        $this->migrator->add('illustration.option_pose_simple', 0);
        $this->migrator->add('illustration.option_pose_complex', 10);

        $this->migrator->add('illustration.option_bg_gradient', 0);
        $this->migrator->add('illustration.option_bg_simple', 10);
        $this->migrator->add('illustration.option_bg_complex', 20);

        $this->migrator->add('illustration.options_print', 4);
        $this->migrator->add('illustration.options_add_tracking', 4);
    }
};
