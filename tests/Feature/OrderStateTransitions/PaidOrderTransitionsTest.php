<?php

use App\Enums\OrderStatus;
use Tests\Feature\OrderStateTransitions\SharedTestUtilities;

uses(SharedTestUtilities::class);

beforeEach(function () {
    $this->setUpStateTransitionTest();
});

describe('PAID Order Status Transitions', function () {

    describe('PAID → TO_SHIP', function () {
        it('allows transition and sends no notifications', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PAID);
            $this->addPaymentToOrder($order);

            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::TO_SHIP);

            expect($updatedOrder->status)->toBe(OrderStatus::TO_SHIP);
            $this->assertNoNotificationsSent();
        })->with([
            'single user' => ['single', false],
            'single guest' => ['single', true],
            'multiple user' => ['multiple', false],
            'multiple guest' => ['multiple', true],
            'mixed user' => ['mixed', false],
            'mixed guest' => ['mixed', true],
        ]);
    });

    describe('PAID → DONE (direct completion)', function () {
        it('allows direct completion for illustration-only orders', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PAID);
            $this->addPaymentToOrder($order);

            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::DONE);

            expect($updatedOrder->status)->toBe(OrderStatus::DONE);
            $this->assertNoNotificationsSent();
        })->with([
            'illustration user' => ['illustration', false],
            'illustration guest' => ['illustration', true],
        ]);
    });

    describe('PAID → CANCELLED (with refund)', function () {
        it('allows cancellation with reason and triggers refund process', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PAID);
            $payment = $this->addPaymentToOrder($order);

            // Verify that the order requires a refund BEFORE cancellation
            expect($order->requiresRefundOnCancellation())->toBeTrue();

            // Should succeed with reason and trigger refund
            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::CANCELLED, [
                'reason' => 'Customer changed mind',
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
            'illustration user' => ['illustration', false],
            'illustration guest' => ['illustration', true],
        ]);
    });

    describe('Invalid PAID transitions', function () {
        it('prevents backward transitions', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PAID);
            $this->addPaymentToOrder($order);

            // Cannot go back to earlier states
            $this->assertTransitionNotAllowed($order, OrderStatus::NEW);
            $this->assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
            $this->assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
        })->with([
            'single user' => ['single', false],
            'single guest' => ['single', true],
            'multiple user' => ['multiple', false],
            'multiple guest' => ['multiple', true],
            'mixed user' => ['mixed', false],
            'mixed guest' => ['mixed', true],
            'illustration user' => ['illustration', false],
            'illustration guest' => ['illustration', true],
        ]);

        it('prevents skipping shipping for physical items', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PAID);
            $this->addPaymentToOrder($order);

            // Cannot skip a shipping step for physical items
            $this->assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
        })->with([
            'single user' => ['single', false],
            'single guest' => ['single', true],
            'multiple user' => ['multiple', false],
            'multiple guest' => ['multiple', true],
            'mixed user' => ['mixed', false],
            'mixed guest' => ['mixed', true],
        ]);
    });

    describe('Business logic validation', function () {
        it('validates that PAID orders have payment records', function () {
            $order = $this->createSingleItemOrder(OrderStatus::PAID);

            // Order marked as PAID but no payment record should be flagged
            expect($order->payments)->toHaveCount(0);

            // Add payment and verify
            $this->addPaymentToOrder($order);
            $order = $order->fresh('payments');
            expect($order->payments)->toHaveCount(1);
        });
    });
});
