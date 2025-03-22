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
            ['name' => 'Prix de base', 'category' => 'Buste', 'key' => 'bust_base', 'price' => 30],
            ['name' => 'Personne supplémentaire', 'category' => 'Buste', 'key' => 'bust_add_human', 'price' => 25],
            ['name' => 'Animal supplémentaire', 'category' => 'Buste', 'key' => 'bust_add_animal', 'price' => 15],

            // Full length prices
            ['name' => 'Prix de base', 'category' => 'Portrait en pied', 'key' => 'fl_base', 'price' => 40],
            ['name' => 'Personne supplémentaire', 'category' => 'Portrait en pied', 'key' => 'fl_add_human', 'price' => 30],
            [ 'name' => 'Animal supplémentaire', 'category' => 'Portrait en pied', 'key' => 'fl_add_animal', 'price' => 15],

            // Animal prices
            ['name' => 'Prix de base', 'category' => 'Animal', 'key' => 'animal_base', 'price' => 25],
            ['name' => 'Animal supplémentaire', 'category' => 'Animal', 'key' => 'animal_add_one', 'price' => 15],
            ['name' => 'Jouet', 'category' => 'Animal', 'key' => 'animal_toy', 'price' => 5],

            // Pose options
            ['name' => 'Simple', 'category' => 'Pose', 'key' => 'option_pose_simple', 'price' => 0],
            ['name' => 'Complexe', 'category' => 'Pose', 'key' => 'option_pose_complex', 'price' => 10],

            // Background options
            ['name' => 'Uni / gradient', 'category' => 'Fond', 'key' => 'option_bg_gradient', 'price' => 0],
            ['name' => 'Simple', 'category' => 'Fond', 'key' => 'option_bg_simple', 'price' => 10],
            ['name' => 'Complexe', 'category' => 'Fond', 'key' => 'option_bg_complex', 'price' => 20],

            // Additional options
            ['name' => 'Impression', 'category' => 'Options', 'key' => 'options_print', 'price' => 4],
            ['name' => 'Suivi de commande', 'category' => 'Options', 'key' => 'options_add_tracking', 'price' => 4],
        ];

        foreach ($prices as $price) {
            IllustrationPrice::create($price);
        }
    }
} 