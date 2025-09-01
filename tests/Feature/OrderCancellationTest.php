<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\OrderService;
use App\Services\StripeService;

it('should cancel order without refund for NEW orders', function () {
    $order = Order::factory()->create(['status' => OrderStatus::NEW]);

    // No need to mock for NEW orders
    $orderService = app(OrderService::class);

    $result = $orderService->cancelOrder($order, 'Customer changed mind');

    expect($result['success'])
        ->toBeTrue()
        ->and($result['message'])
        ->toBe('Order cancelled successfully')
        ->and($result['refund_processed'])
        ->toBeFalse();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::CANCELLED);
});

it('should cancel order with refund for PAID orders', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PAID]);

    $payment = OrderPayment::factory()->create([
        'order_id' => $order->id,
        'type' => PaymentType::ORDER_FULL,
        'status' => PaymentStatus::PAID,
        'amount' => 5000,
        'stripe_payment_intent_id' => 'pi_test_12345',
    ]);

    // Mock StripeService BEFORE creating OrderService
    $this->mock(StripeService::class, function ($mock) {
        $mock->shouldReceive('refundPayment')
            ->with('pi_test_12345', 5000, 'Quality issue')
            ->andReturn([
                'success' => true,
                'refund_id' => 're_test_67890',
                'amount' => 5000,
                'status' => 'succeeded',
            ]);
    });

    // Create OrderService AFTER mocking
    $orderService = app(OrderService::class);

    $result = $orderService->cancelOrder($order, 'Quality issue');

    expect($result['success'])
        ->toBeTrue()
        ->and($result['refund_processed'])
        ->toBeTrue()
        ->and($result['refund_details']['success'])
        ->toBeTrue();

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::CANCELLED);

    $payment->refresh();
    expect($payment->status)
        ->toBe(PaymentStatus::REFUNDED)
        ->and($payment->refund_reason)
        ->toBe('Quality issue');
});

it('should fail cancellation if refund fails', function () {
    $order = Order::factory()->create(['status' => OrderStatus::TO_SHIP]);

    OrderPayment::factory()->create([
        'order_id' => $order->id,
        'type' => PaymentType::ORDER_FULL,
        'status' => PaymentStatus::PAID,
        'amount' => 5000,
        'stripe_payment_intent_id' => 'pi_test_12345',
    ]);

    // Mock StripeService to fail BEFORE creating OrderService
    $this->mock(StripeService::class, function ($mock) {
        $mock->shouldReceive('refundPayment')
            ->with('pi_test_12345', 5000, 'Shipping issue')
            ->andReturn([
                'success' => false,
                'error' => 'Payment intent not found',
            ]);
    });

    // Create OrderService AFTER mocking
    $orderService = app(OrderService::class);

    $result = $orderService->cancelOrder($order, 'Shipping issue');

    expect($result['success'])
        ->toBeFalse()
        ->and($result['error'])
        ->toBe('Failed to process refunds');

    $order->refresh();
    expect($order->status)->toBe(OrderStatus::TO_SHIP); // Should not be cancelled
});

it('should provide correct refund summary', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PAID]);

    // Create multiple payments
    OrderPayment::factory()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::PAID,
        'amount' => 5000,
    ]);

    OrderPayment::factory()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::PARTIALLY_REFUNDED,
        'amount' => 10000,
        'refunded_amount' => 3000,
    ]);

    // No need to mock for summary
    $orderService = app(OrderService::class);

    $summary = $orderService->getRefundSummary($order);

    expect($summary['total_paid'])->toBe(15000)
        ->and($summary['total_refunded'])->toBe(3000)
        ->and($summary['refundable_amount'])->toBe(12000)
        ->and($summary['can_be_refunded'])->toBeTrue();
});

it('should handle orders with multiple payments correctly', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PAID]);

    // Regular order payment
    $orderPayment = OrderPayment::factory()->create([
        'order_id' => $order->id,
        'type' => PaymentType::ORDER_FULL,
        'status' => PaymentStatus::PAID,
        'amount' => 5000,
        'stripe_payment_intent_id' => 'pi_order_12345',
    ]);

    // Illustration deposit payment
    $depositPayment = OrderPayment::factory()->create([
        'order_id' => $order->id,
        'type' => PaymentType::ILLUSTRATION_DEPOSIT,
        'status' => PaymentStatus::PAID,
        'amount' => 3000,
        'stripe_payment_intent_id' => 'pi_deposit_12345',
    ]);

    // Mock StripeService for multiple refunds BEFORE creating OrderService
    $this->mock(StripeService::class, function ($mock) {
        $mock->shouldReceive('refundPayment')
            ->with('pi_order_12345', 5000, 'Order cancelled')
            ->andReturn([
                'success' => true,
                'refund_id' => 're_order_67890',
                'amount' => 5000,
                'status' => 'succeeded',
            ]);

        $mock->shouldReceive('refundPayment')
            ->with('pi_deposit_12345', 3000, 'Order cancelled')
            ->andReturn([
                'success' => true,
                'refund_id' => 're_deposit_67890',
                'amount' => 3000,
                'status' => 'succeeded',
            ]);
    });

    // Create OrderService AFTER mocking
    $orderService = app(OrderService::class);

    $result = $orderService->cancelOrder($order, 'Order cancelled');

    expect($result['success'])->toBeTrue()
        ->and($result['refund_processed'])->toBeTrue()
        ->and($result['refund_details']['refunds'])->toHaveCount(2);

    $orderPayment->refresh();
    $depositPayment->refresh();

    expect($orderPayment->status)->toBe(PaymentStatus::REFUNDED)
        ->and($depositPayment->status)->toBe(PaymentStatus::REFUNDED);
});
