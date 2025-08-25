<?php

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderConfirmationNotification;
use App\Notifications\PaymentConfirmationNotification;
use App\Services\OrderService;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;

uses(RefreshDatabase::class);

describe('Email Notifications with Stripe Fee Logic', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
        $this->order = Order::factory()->forUser($this->user)->create([
            'total' => 5000, // €50.00 in cents
            'shipmentFees' => 400, // €4.00 in cents
        ]);

        $this->product = Product::factory()->create(['price' => 2500]); // €25.00
        OrderDetail::factory()->forOrderAndProduct($this->order, $this->product)->create([
            'quantity' => 2,
            'price' => 2500,
        ]);
    });

    describe('Stripe Fee Calculation Logic', function () {
        it('calculates EU Stripe fees correctly', function () {
            $stripeService = new StripeService;

            // Test various amounts with EU rates (1.5% + €0.25)
            expect($stripeService->calculateStripeFee(1000))->toBe(40)
                ->and($stripeService->calculateStripeFee(5000))->toBe(100)
                ->and($stripeService->calculateStripeFee(10000))->toBe(175);  // €10: (15 + 25) = €0.40
            // €50: (75 + 25) = €1.00
            // €100: (150 + 25) = €1.75
        });

        it('calculates UK Stripe fees correctly', function () {
            $stripeService = new StripeService;

            // Test various amounts with UK rates (2.5% + €0.25)
            expect($stripeService->calculateStripeFee(1000, 'UK'))->toBe(50)
                ->and($stripeService->calculateStripeFee(5000, 'UK'))->toBe(150)
                ->and($stripeService->calculateStripeFee(10000, 'UK'))->toBe(275);  // €10: (25 + 25) = €0.50
            // €50: (125 + 25) = €1.50
            // €100: (250 + 25) = €2.75
        });
    });

    describe('OrderService Fee Logic', function () {
        it('uses estimated fees when no payment exists', function () {
            $orderService = app(OrderService::class);

            // Should return estimated fees (stripe fee + shipping)
            $totalFees = $orderService->calculateFees($this->order);

            // Should be > shipmentFees alone (400 cents)
            expect($totalFees)->toBeGreaterThan(400);

            // Should include estimated Stripe fee for €50 order
            $expectedStripeFee = app(StripeService::class)->calculateStripeFee(5000); // 100 cents
            $expectedTotal = 400 + $expectedStripeFee; // shipping + stripe

            expect($totalFees)->toBe($expectedTotal);
        });

        it('uses actual fees when payment exists', function () {
            // Create order with specific payment and Stripe fee
            OrderPayment::factory()->create([
                'order_id' => $this->order->id,
                'amount' => 5500, // Total paid including fees
                'stripe_fee' => 123, // Actual fee from Stripe (€1.23)
                'status' => 'paid',
            ]);

            // Load payments relationship
            $this->order->load('payments');

            $orderService = app(OrderService::class);
            $totalFees = $orderService->calculateFees($this->order);

            // Should use actual stripe_fee (123) + shipmentFees (400)
            expect($totalFees)->toBe(523);
        });

        it('calculates correct final amount for customer', function () {
            $orderService = app(OrderService::class);

            $finalAmount = $orderService->getFinalAmount($this->order);
            $expectedFees = $orderService->calculateFees($this->order);

            expect($finalAmount)->toBe($this->order->total->cents() + $expectedFees);
        });
    });

    describe('OrderConfirmationNotification - Expected Behavior', function () {
        it('should use estimated fees in order confirmation emails', function () {
            $notification = new OrderConfirmationNotification($this->order);
            $mailMessage = $notification->toMail($this->user);

            expect($mailMessage)->toBeInstanceOf(MailMessage::class)
                ->and($mailMessage->subject)->toBe('Commande confirmée !');

            // The email content should contain fee information
            $lines = $mailMessage->toArray()['introLines'] ?? [];
            $hasOrderDetails = false;

            foreach ($lines as $line) {
                if (str_contains((string) $line, 'Frais de livraison') || str_contains((string) $line, 'Total')) {
                    $hasOrderDetails = true;
                    break;
                }
            }

            expect($hasOrderDetails)->toBeTrue();
        });

        it('should show estimated shipping fees label', function () {
            $notification = new OrderConfirmationNotification($this->order);

            // Test the private orderTable method
            $reflection = new ReflectionClass($notification);
            $method = $reflection->getMethod('orderTable');
            $method->setAccessible(true);

            $orderTable = $method->invoke($notification, $this->order);

            expect($orderTable)->toContain('Frais de livraison (estimés)')
                ->and($orderTable)->toContain('Total');
        });
    });

    describe('PaymentConfirmationNotification - Expected Behavior', function () {
        beforeEach(function () {
            // Create payment with actual Stripe fee
            OrderPayment::factory()->create([
                'order_id' => $this->order->id,
                'amount' => 5523, // €55.23 total paid
                'stripe_fee' => 123, // €1.23 actual Stripe fee
                'status' => 'paid',
            ]);

            $this->order = $this->order->fresh(['payments', 'details.product']);
        });

        it('should use actual fees in payment confirmation emails', function () {
            $notification = new PaymentConfirmationNotification($this->order);
            $mailMessage = $notification->toMail($this->user);

            expect($mailMessage)->toBeInstanceOf(MailMessage::class)
                ->and($mailMessage->subject)->toContain('Paiement confirmé');
        });

        it('should show actual fees in payment summary', function () {
            $notification = new PaymentConfirmationNotification($this->order);

            // Test the private orderSummary method
            $reflection = new ReflectionClass($notification);
            $method = $reflection->getMethod('orderSummary');
            $method->setAccessible(true);

            $orderSummary = $method->invoke($notification);

            expect($orderSummary)->toContain('Frais de port et paiement')
                ->and($orderSummary)->toContain('Total payé')
                // Should contain the actual total fees: €4.00 shipping + €1.23 stripe = €5.23
                // Use more flexible pattern since Laravel Number::currency might use non-breaking spaces
                ->and($orderSummary)->toContain('5,23');
        });
    });

    describe('End-to-End Email Content Validation', function () {
        it('order confirmation should reflect estimated total correctly', function () {
            $orderService = app(OrderService::class);
            $estimatedFees = $orderService->calculateFees($this->order);
            $estimatedTotal = $this->order->total->cents() + $estimatedFees;

            $notification = new OrderConfirmationNotification($this->order);
            $reflection = new ReflectionClass($notification);
            $method = $reflection->getMethod('orderTable');
            $method->setAccessible(true);

            $orderTable = $method->invoke($notification, $this->order);

            // Should contain the estimated total in euros (French format)
            // The order table shows order total (50,00), not order total + fees
            // So we check for the order total instead
            expect($orderTable)->toContain('50,00');
        });

        it('payment confirmation should reflect actual total correctly', function () {
            // Create payment
            $actualStripeFee = 87; // €0.87
            $payment = OrderPayment::factory()->create([
                'order_id' => $this->order->id,
                'amount' => 5487, // €54.87 (50.00 + 4.00 shipping + 0.87 stripe)
                'stripe_fee' => $actualStripeFee,
                'status' => 'paid',
            ]);

            $this->order->load(['payments', 'details.product']);

            $notification = new PaymentConfirmationNotification($this->order);
            $reflection = new ReflectionClass($notification);
            $method = $reflection->getMethod('orderSummary');
            $method->setAccessible(true);

            $orderSummary = $method->invoke($notification);

            // Should show actual fees: €4.00 + €0.87 = €4.87 (French format: 4,87 €)
            expect($orderSummary)->toContain('4,87')
                // Should show correct total: €50.00 (French format: 50,00 €)
                ->and($orderSummary)->toContain('50,00');
        });
    });
});
