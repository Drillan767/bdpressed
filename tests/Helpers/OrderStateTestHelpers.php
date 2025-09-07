<?php

namespace Tests\Helpers;

use App\Enums\IllustrationStatus;
use App\Enums\OrderStatus;
use App\Models\Illustration;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

class OrderStateTestHelpers
{
    /**
     * Set up test environment with faked events and mail
     */
    public static function setupTestEnvironment(): void
    {
        Event::fake();
        Mail::fake();
        Notification::fake();

        // Create roles if they don't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
        if (!Role::where('name', 'user')->exists()) {
            Role::create(['name' => 'user']);
        }
    }

    /**
     * Create a single item order (no illustrations)
     */
    public static function createSingleItemOrder(OrderStatus $status = OrderStatus::NEW): Order
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(10)->withPrice(2500)->create();

        $order = Order::factory()->withStatus($status)->forUser($user)->create();

        OrderDetail::factory()
            ->forOrderAndProduct($order, $product)
            ->withQuantity(1)
            ->create();

        return $order->fresh(['details', 'user', 'shippingAddress', 'billingAddress']);
    }

    /**
     * Create a multiple items order (no illustrations)
     */
    public static function createMultipleItemsOrder(OrderStatus $status = OrderStatus::NEW): Order
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product1 = Product::factory()->inStock(10)->withPrice(2000)->create();
        $product2 = Product::factory()->inStock(5)->withPrice(3500)->create();

        $order = Order::factory()->withStatus($status)->forUser($user)->create();

        // Product 1: quantity 3
        OrderDetail::factory()
            ->forOrderAndProduct($order, $product1)
            ->withQuantity(3)
            ->create();

        // Product 2: quantity 2
        OrderDetail::factory()
            ->forOrderAndProduct($order, $product2)
            ->withQuantity(2)
            ->create();

        return $order->fresh(['details', 'user', 'shippingAddress', 'billingAddress']);
    }

    /**
     * Create an illustration-only order (no items)
     */
    public static function createIllustrationOnlyOrder(OrderStatus $orderStatus = OrderStatus::NEW): Order
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $order = Order::factory()->withStatus($orderStatus)->forUser($user)->create();

        Illustration::factory()
            ->forOrder($order)
            ->withStatus(IllustrationStatus::PENDING)
            ->create();

        return $order->fresh(['illustrations', 'user', 'shippingAddress', 'billingAddress']);
    }

    /**
     * Create an order with items and illustrations (both independent)
     */
    public static function createOrderWithItemsAndIllustrations(OrderStatus $orderStatus = OrderStatus::NEW): Order
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $product = Product::factory()->inStock(10)->withPrice(2500)->create();

        $order = Order::factory()->withStatus($orderStatus)->forUser($user)->create();

        // Add regular product
        OrderDetail::factory()
            ->forOrderAndProduct($order, $product)
            ->withQuantity(1)
            ->create();

        // Add illustration (independent lifecycle)
        Illustration::factory()
            ->forOrder($order)
            ->withStatus(IllustrationStatus::PENDING)
            ->create();

        return $order->fresh(['details', 'illustrations', 'user', 'shippingAddress', 'billingAddress']);
    }

    /**
     * Assert that an order status transition succeeds
     */
    public static function assertTransitionSucceeds(Order $order, OrderStatus $toStatus, array $context = []): void
    {
        $fromStatus = $order->status;

        expect($order->canTransitionTo($toStatus))->toBeTrue(
            "Order should be able to transition from {$fromStatus->value} to {$toStatus->value}"
        );

        $order->transitionTo($toStatus, $context);

        expect($order->fresh()->status)->toBe($toStatus);
    }

    /**
     * Assert that an order status transition fails with validation error
     */
    public static function assertTransitionFailsValidation(Order $order, OrderStatus $toStatus): void
    {
        expect(fn() => $order->transitionTo($toStatus))
            ->toThrow(\Exception::class);
    }

    /**
     * Assert that a status change is not allowed (invalid state machine transition)
     */
    public static function assertTransitionNotAllowed(Order $order, OrderStatus $toStatus): void
    {
        $fromStatus = $order->status;

        expect($order->canTransitionTo($toStatus))->toBeFalse(
            "Order should NOT be able to transition from {$fromStatus->value} to {$toStatus->value}"
        );
    }

    /**
     * Test cancellation with reason requirement
     */
    public static function assertCancellationRequiresReason(Order $order): void
    {
        // Should fail without reason
        self::assertTransitionFailsValidation($order, OrderStatus::CANCELLED);

        // Should succeed with reason
        $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'Test cancellation reason']);
        expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);
    }

    /**
     * Assert that tracking number is required for SHIPPED transition
     */
    public static function assertShippingRequiresTracking(Order $order): void
    {
        // Should fail without tracking
        self::assertTransitionFailsValidation($order, OrderStatus::SHIPPED);

        // Should succeed with tracking
        $order->transitionTo(OrderStatus::SHIPPED, ['tracking_number' => 'TEST123456']);
        expect($order->fresh()->status)->toBe(OrderStatus::SHIPPED);
    }

    /**
     * Assert that a specific email was sent
     */
    public static function assertEmailSent(string $mailable): void
    {
        Mail::assertSent($mailable);
    }

    /**
     * Assert that no emails were sent
     */
    public static function assertNoEmailsSent(): void
    {
        Mail::assertNothingSent();
    }

    /**
     * Assert that terminal states cannot be changed
     */
    public static function assertTerminalState(Order $order): void
    {
        $allStates = [
            OrderStatus::NEW,
            OrderStatus::IN_PROGRESS,
            OrderStatus::PENDING_PAYMENT,
            OrderStatus::PAID,
            OrderStatus::TO_SHIP,
            OrderStatus::SHIPPED,
            OrderStatus::DONE,
            OrderStatus::CANCELLED
        ];

        foreach ($allStates as $state) {
            if ($state !== $order->status) {
                self::assertTransitionNotAllowed($order, $state);
            }
        }
    }
}
