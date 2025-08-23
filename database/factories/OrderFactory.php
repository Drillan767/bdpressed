<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total' => $this->faker->numberBetween(1000, 10000), // €10-100 in cents
            'shipmentFees' => $this->faker->numberBetween(400, 700), // €4-7 in cents
            'reference' => 'ORD-' . $this->faker->unique()->numberBetween(100000, 999999),
            'additionalInfos' => $this->faker->optional()->sentence(),
            'user_id' => User::factory(),
            'guest_id' => null,
            'shipping_address_id' => Address::factory(),
            'billing_address_id' => Address::factory(),
            'useSameAddress' => $this->faker->boolean(70),
            'status' => OrderStatus::NEW,
            'refusal_reason' => null,
        ];
    }

    /**
     * Create order with specific status
     */
    public function withStatus(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Create order for existing user
     */
    public function forUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            $shippingAddress = Address::factory()->create(['user_id' => $user->id]);
            $billingAddress = Address::factory()->create(['user_id' => $user->id]);
            
            return [
                'user_id' => $user->id,
                'guest_id' => null,
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $billingAddress->id,
            ];
        });
    }
}