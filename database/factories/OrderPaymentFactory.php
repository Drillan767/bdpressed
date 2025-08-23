<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderPayment>
 */
class OrderPaymentFactory extends Factory
{
    protected $model = OrderPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'illustration_id' => null,
            'type' => 'order_full',
            'status' => 'pending',
            'amount' => $this->faker->numberBetween(1000, 10000), // €10-100 in cents
            'currency' => 'EUR',
            'stripe_payment_intent_id' => null,
            'stripe_payment_link' => null,
            'stripe_fee' => null,
            'description' => 'Order payment',
            'paid_at' => null,
            'failed_at' => null,
            'refunded_amount' => 0,
            'refunded_at' => null,
            'refund_reason' => null,
            'stripe_metadata' => null,
        ];
    }

    /**
     * Create paid payment
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'stripe_payment_intent_id' => 'pi_' . $this->faker->uuid(),
            'stripe_fee' => intval($attributes['amount'] * 0.015) + 25, // 1.5% + €0.25
            'paid_at' => now(),
        ]);
    }

    /**
     * Create failed payment
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'failed_at' => now(),
        ]);
    }

    /**
     * Create refunded payment
     */
    public function refunded(int $refundAmount = null): static
    {
        return $this->state(function (array $attributes) use ($refundAmount) {
            $refundAmount = $refundAmount ?? $attributes['amount'];
            
            return [
                'status' => 'refunded',
                'refunded_amount' => $refundAmount,
                'refunded_at' => now(),
                'refund_reason' => 'Customer requested refund',
            ];
        });
    }

    /**
     * Set specific Stripe fee
     */
    public function withStripeFee(int $feeInCents): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_fee' => $feeInCents,
        ]);
    }

    /**
     * For specific order
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }
}