<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'street' => $this->faker->streetAddress(),
            'street2' => '',
            'city' => $this->faker->city(),
            'zipCode' => $this->faker->postcode(),
            'country' => $this->faker->randomElement(['France', 'Belgium', 'Switzerland']),
            'user_id' => null,
            'guest_id' => null,
            'default_shipping' => false,
            'default_billing' => false,
        ];
    }

    /**
     * Create address for a specific user
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'guest_id' => null,
        ]);
    }

    /**
     * Mark as default shipping address
     */
    public function defaultShipping(): static
    {
        return $this->state(fn (array $attributes) => [
            'default_shipping' => true,
        ]);
    }

    /**
     * Mark as default billing address
     */
    public function defaultBilling(): static
    {
        return $this->state(fn (array $attributes) => [
            'default_billing' => true,
        ]);
    }
}
