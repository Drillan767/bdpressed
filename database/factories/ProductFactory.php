<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'quickDescription' => $this->faker->sentence(8),
            'description' => $this->faker->paragraphs(3, true),
            'weight' => $this->faker->numberBetween(50, 500), // grams
            'stock' => $this->faker->numberBetween(0, 100),
            'illustrations' => '[]', // Empty JSON array for illustrations
            'promotedImage' => 'products/'.$this->faker->uuid().'.jpg',
            'price' => $this->faker->numberBetween(1000, 5000), // €10-50 in cents
            'stripe_link' => '',
        ];
    }

    /**
     * Create product with specific price
     */
    public function withPrice(int $priceInCents): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $priceInCents,
        ]);
    }

    /**
     * Create product with stock
     */
    public function inStock(int $stock = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => $stock,
        ]);
    }

    /**
     * Create out of stock product
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }
}
