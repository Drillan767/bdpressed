<?php

namespace Database\Factories;

use App\Enums\IllustrationStatus;
use App\Models\Illustration;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Illustration>
 */
class IllustrationFactory extends Factory
{
    protected $model = Illustration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => $this->faker->randomElement(['bust', 'full_length', 'animal']),
            'nbHumans' => $this->faker->numberBetween(0, 3),
            'nbAnimals' => $this->faker->numberBetween(0, 2),
            'pose' => $this->faker->randomElement(['standing', 'sitting', 'action']),
            'background' => $this->faker->randomElement(['simple', 'detailed', 'transparent']),
            'status' => IllustrationStatus::PENDING,
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(5000, 15000), // €50-150 in cents
            'print' => $this->faker->boolean(),
            'addTracking' => $this->faker->boolean(30),
            'trackingNumber' => null,
        ];
    }

    /**
     * Create illustration with specific status
     */
    public function withStatus(IllustrationStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Create illustration for existing order
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }
}
