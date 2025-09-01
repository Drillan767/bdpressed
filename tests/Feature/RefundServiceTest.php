<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\RefundService;
use App\Services\StripeService;

beforeEach(function () {
    // We'll create the service in each test after mocking StripeService
});

it('should not require refund for orders in NEW state', function () {
    $order = Order::factory()->create(['status' => OrderStatus::NEW]);

    // No need to mock StripeService for this test
    $refundService = app(RefundService::class);

    expect($order->requiresRefundOnCancellation())->toBeFalse();

    $result = $refundService->processOrderCancellationRefund($order, 'Customer request');

    expect($result['success'])->toBeTrue()
        ->and($result['message'])->toBe('No refund required for this order state')
        ->and($result['refunds'])->toBeEmpty();
});

it('should process full refund for orders in PAID state', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PAID]);

    $payment = OrderPayment::factory()->create([
        'order_id' => $order->id,
        'type' => PaymentType::ORDER_FULL,
        'status' => PaymentStatus::PAID,
        'amount' => 5000, // €50.00
        'stripe_payment_intent_id' => 'pi_test_12345',
    ]);

    // Mock StripeService BEFORE creating RefundService
    $this->mock(StripeService::class, function ($mock) {
        $mock->shouldReceive('refundPayment')
            ->with('pi_test_12345', 5000, 'Customer request')
            ->andReturn([
                'success' => true,
                'refund_id' => 're_test_67890',
                'amount' => 5000,
                'status' => 'succeeded',
            ]);
    });

    // Create RefundService AFTER mocking StripeService
    $refundService = app(RefundService::class);

    expect($order->requiresRefundOnCancellation())->toBeTrue();

    $result = $refundService->processOrderCancellationRefund($order, 'Customer request');

    expect($result['success'])->toBeTrue()
        ->and($result['refunds'])->toHaveCount(1)
        ->and($result['refunds'][0]['success'])->toBeTrue();

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::REFUNDED)
        ->and($payment->refunded_amount->cents())->toBe(5000)
        ->and($payment->refund_reason)->toBe('Customer request');
});

it('should handle partial refunds correctly', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PAID]);

    $payment = OrderPayment::factory()->create([
        'order_id' => $order->id,
        'type' => PaymentType::ORDER_FULL,
        'status' => PaymentStatus::PAID,
        'amount' => 10000, // €100.00
        'stripe_payment_intent_id' => 'pi_test_12345',
    ]);

    // Mock StripeService for partial refund BEFORE creating RefundService
    $this->mock(StripeService::class, function ($mock) {
        $mock->shouldReceive('refundPayment')
            ->with('pi_test_12345', 5000, 'Partial refund')
            ->andReturn([
                'success' => true,
                'refund_id' => 're_test_67890',
                'amount' => 5000,
                'status' => 'succeeded',
            ]);
    });

    // Create RefundService AFTER mocking StripeService
    $refundService = app(RefundService::class);

    $result = $refundService->refundPayment($payment, 5000, 'Partial refund');

    expect($result['success'])->toBeTrue()
        ->and($result['is_full_refund'])->toBeFalse();

    $payment->refresh();
    expect($payment->status)->toBe(PaymentStatus::PARTIALLY_REFUNDED)
        ->and($payment->refunded_amount->cents())->toBe(5000)
        ->and($payment->refund_reason)->toBe('Partial refund');
});

it('should calculate correct illustration refund amounts', function () {
    $depositPayment = OrderPayment::factory()->create([
        'type' => PaymentType::ILLUSTRATION_DEPOSIT,
        'amount' => 5000, // €50.00
    ]);

    $finalPayment = OrderPayment::factory()->create([
        'type' => PaymentType::ILLUSTRATION_FINAL,
        'amount' => 15000, // €150.00
    ]);

    // No need to mock for this calculation test
    $refundService = app(RefundService::class);

    // Before work starts - full refund
    expect($refundService->calculateIllustrationRefundAmount($depositPayment, 'DEPOSIT_PAID'))
        ->toBe(5000);

    // Work in progress - keep deposit, refund final payment
    expect($refundService->calculateIllustrationRefundAmount($depositPayment, 'IN_PROGRESS'))
        ->toBe(0);
    expect($refundService->calculateIllustrationRefundAmount($finalPayment, 'IN_PROGRESS'))
        ->toBe(15000);

    // Point of no return - no refunds
    expect($refundService->calculateIllustrationRefundAmount($depositPayment, 'COMPLETED'))
        ->toBe(0);
});

it('should provide accurate refund summary', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PAID]);

    // One fully paid payment
    OrderPayment::factory()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::PAID,
        'amount' => 5000,
    ]);

    // One partially refunded payment
    OrderPayment::factory()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::PARTIALLY_REFUNDED,
        'amount' => 10000,
        'refunded_amount' => 3000,
    ]);

    // No need to mock for this summary test
    $refundService = app(RefundService::class);

    $summary = $refundService->getOrderRefundSummary($order);

    expect($summary['total_paid'])->toBe(15000)
        ->and($summary['total_refunded'])->toBe(3000)
        ->and($summary['refundable_amount'])->toBe(12000) // 5000 + (10000 - 3000)
        ->and($summary['can_be_refunded'])->toBeTrue();
});
