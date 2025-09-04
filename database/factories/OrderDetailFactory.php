<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderDetail>
 */
class OrderDetailFactory extends Factory
{
    protected $model = OrderDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'order_id' => Order::factory(),
            'quantity' => $this->faker->numberBetween(1, 3),
            'price' => $this->faker->numberBetween(1000, 5000), // €10-50 in cents
        ];
    }

    /**
     * Create order detail for specific order and product
     */
    public function forOrderAndProduct(Order $order, Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price' => $product->price,
        ]);
    }

    /**
     * Set specific quantity
     */
    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }
}
