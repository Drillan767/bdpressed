<?php

use App\Enums\OrderStatus;
use App\Notifications\ShippingNotification;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\OrderStateTransitions\SharedTestUtilities;

uses(SharedTestUtilities::class);

beforeEach(function () {
    $this->setUpStateTransitionTest();
});

describe('TO_SHIP Order Status Transitions', function () {

    describe('TO_SHIP → SHIPPED', function () {
        it('requires tracking number and sends shipping notification', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::TO_SHIP);
            $this->addPaymentToOrder($order);

            // Should succeed with tracking number
            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::SHIPPED, [
                'tracking_number' => 'FR123456789',
            ]);

            expect($updatedOrder->status)->toBe(OrderStatus::SHIPPED);

            if ($updatedOrder->user) {
                Notification::assertSentTo($updatedOrder->user, ShippingNotification::class);
            }
            if ($updatedOrder->guest) {
                Notification::assertSentTo($updatedOrder->guest, ShippingNotification::class);
            }

        })->with([
            'single user' => ['single', false],
            'single guest' => ['single', true],
            'multiple user' => ['multiple', false],
            'multiple guest' => ['multiple', true],
            'mixed user' => ['mixed', false],
            'mixed guest' => ['mixed', true],
        ]);
    });

    describe('TO_SHIP → CANCELLED (with refund)', function () {
        it('allows cancellation with reason and triggers refund process', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::TO_SHIP);
            $payment = $this->addPaymentToOrder($order);

            // Verify that the order requires refund BEFORE cancellation
            expect($order->requiresRefundOnCancellation())->toBeTrue();

            // Should succeed with reason and trigger refund
            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::CANCELLED, [
                'reason' => 'Item damaged during preparation',
            ]);

            expect($updatedOrder->status)->toBe(OrderStatus::CANCELLED);
            $this->assertCancellationNotificationSent($updatedOrder);
        })->with([
            'single user' => ['single', false],
            'single guest' => ['single', true],
            'multiple user' => ['multiple', false],
            'multiple guest' => ['multiple', true],
            'mixed user' => ['mixed', false],
            'mixed guest' => ['mixed', true],
        ]);
    });

    describe('Invalid TO_SHIP transitions', function () {
        it('prevents backward transitions', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::TO_SHIP);
            $this->addPaymentToOrder($order);

            // Cannot go back to earlier states
            $this->assertTransitionNotAllowed($order, OrderStatus::NEW);
            $this->assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
            $this->assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
            $this->assertTransitionNotAllowed($order, OrderStatus::PAID);
        })->with([
            'single user' => ['single', false],
            'single guest' => ['single', true],
            'multiple user' => ['multiple', false],
            'multiple guest' => ['multiple', true],
            'mixed user' => ['mixed', false],
            'mixed guest' => ['mixed', true],
        ]);

        it('prevents skipping to completion', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::TO_SHIP);
            $this->addPaymentToOrder($order);

            // Cannot skip directly to done without shipping
            $this->assertTransitionNotAllowed($order, OrderStatus::DONE);
        })->with([
            'single user' => ['single', false],
            'single guest' => ['single', true],
            'multiple user' => ['multiple', false],
            'multiple guest' => ['multiple', true],
            'mixed user' => ['mixed', false],
            'mixed guest' => ['mixed', true],
        ]);
    });

    describe('Tracking number validation', function () {
        it('validates tracking number format when provided', function () {
            $order = $this->createSingleItemOrder(OrderStatus::TO_SHIP);
            $this->addPaymentToOrder($order);

            // Test with valid tracking numbers
            $validTrackingNumbers = [
                'FR123456789',
                'LA123456789FR',
                '1234567890123',
            ];

            foreach ($validTrackingNumbers as $trackingNumber) {
                $testOrder = $this->createSingleItemOrder(OrderStatus::TO_SHIP);
                $this->addPaymentToOrder($testOrder);

                $updatedOrder = $this->assertTransitionSucceeds($testOrder, OrderStatus::SHIPPED, [
                    'tracking_number' => $trackingNumber,
                ]);

                expect($updatedOrder->status)->toBe(OrderStatus::SHIPPED);
            }
        });

        it('prevents transition to SHIPPED without tracking number', function () {
            $order = $this->createSingleItemOrder(OrderStatus::TO_SHIP);
            $this->addPaymentToOrder($order);

            // State machine allows the transition, but validation should prevent it
            expect($order->canTransitionTo(OrderStatus::SHIPPED))->toBeTrue();

            // Actual transition should fail due to missing tracking number
            expect(fn () => $order->transitionTo(OrderStatus::SHIPPED))
                ->toThrow(InvalidArgumentException::class, 'Tracking number is required');

            // Verify order status remains unchanged
            expect($order->fresh()->status)->toBe(OrderStatus::TO_SHIP);
        });
    });
});
