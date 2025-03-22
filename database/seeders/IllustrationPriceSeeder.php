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
            ['name' => 'Buste - Prix de base', 'key' => 'bust_base', 'price' => 30],
            ['name' => 'Buste - Personne supplémentaire', 'key' => 'bust_add_human', 'price' => 25],
            ['name' => 'Buste - Animal supplémentaire', 'key' => 'bust_add_animal', 'price' => 15],

            // Full length prices
            ['name' => 'Portrait en pied - Prix de base', 'key' => 'fl_base', 'price' => 40],
            ['name' => 'Portrait en pied - Personne supplémentaire', 'key' => 'fl_add_human', 'price' => 30],
            ['name' => 'Portrait en pied - Animal supplémentaire', 'key' => 'fl_add_animal', 'price' => 15],

            // Animal prices
            ['name' => 'Animal - Prix de base', 'key' => 'animal_base', 'price' => 25],
            ['name' => 'Animal - Animal supplémentaire', 'key' => 'animal_add_one', 'price' => 15],
            ['name' => 'Animal - Jouet', 'key' => 'animal_toy', 'price' => 5],

            // Pose options
            ['name' => 'Pose simple', 'key' => 'option_pose_simple', 'price' => 0],
            ['name' => 'Pose complexe', 'key' => 'option_pose_complex', 'price' => 10],

            // Background options
            ['name' => 'Fond uni / gradient', 'key' => 'option_bg_gradient', 'price' => 0],
            ['name' => 'Fond simple', 'key' => 'option_bg_simple', 'price' => 10],
            ['name' => 'Fond complexe', 'key' => 'option_bg_complex', 'price' => 20],

            // Additional options
            ['name' => 'Impression', 'key' => 'options_print', 'price' => 4],
            ['name' => 'Suivi de commande', 'key' => 'options_add_tracking', 'price' => 4],
        ];

        foreach ($prices as $price) {
            IllustrationPrice::create($price);
        }
    }
} 