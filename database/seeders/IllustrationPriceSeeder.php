<?php

namespace Database\Seeders;

use App\Models\IllustrationPrice;
use Illuminate\Database\Seeder;

class IllustrationPriceSeeder extends Seeder
{
    public function run(): void
    {
        $prices = [
            // Bust prices
            ['name' => 'Prix de base', 'category' => 'Buste', 'key' => 'bust_base', 'price' => 3000],
            ['name' => 'Personne supplémentaire', 'category' => 'Buste', 'key' => 'bust_add_human', 'price' => 2500],
            ['name' => 'Animal supplémentaire', 'category' => 'Buste', 'key' => 'bust_add_animal', 'price' => 1500],

            // Full length prices
            ['name' => 'Prix de base', 'category' => 'Portrait en pied', 'key' => 'fl_base', 'price' => 4000],
            ['name' => 'Personne supplémentaire', 'category' => 'Portrait en pied', 'key' => 'fl_add_human', 'price' => 3000],
            ['name' => 'Animal supplémentaire', 'category' => 'Portrait en pied', 'key' => 'fl_add_animal', 'price' => 1500],

            // Animal prices
            ['name' => 'Prix de base', 'category' => 'Animal', 'key' => 'animal_base', 'price' => 2500],
            ['name' => 'Animal supplémentaire', 'category' => 'Animal', 'key' => 'animal_add_one', 'price' => 1500],
            ['name' => 'Jouet', 'category' => 'Animal', 'key' => 'animal_toy', 'price' => 500],

            // Pose options
            ['name' => 'Simple', 'category' => 'Pose', 'key' => 'option_pose_simple', 'price' => 0],
            ['name' => 'Complexe', 'category' => 'Pose', 'key' => 'option_pose_complex', 'price' => 1000],

            // Background options
            ['name' => 'Uni / gradient', 'category' => 'Fond', 'key' => 'option_bg_gradient', 'price' => 0],
            ['name' => 'Simple', 'category' => 'Fond', 'key' => 'option_bg_simple', 'price' => 1000],
            ['name' => 'Complexe', 'category' => 'Fond', 'key' => 'option_bg_complex', 'price' => 2000],

            // Additional options
            ['name' => 'Impression', 'category' => 'Options', 'key' => 'options_print', 'price' => 400],
            ['name' => 'Suivi de commande', 'category' => 'Options', 'key' => 'options_add_tracking', 'price' => 400],
        ];

        foreach ($prices as $i => $price) {
            $current = $i + 1;
            $total = count($prices);
            $this->command->outputComponents()->info("Creating \"{$price['category']} - {$price['name']}\" ($current / $total)");
            IllustrationPrice::create($price);
        }
    }
}
