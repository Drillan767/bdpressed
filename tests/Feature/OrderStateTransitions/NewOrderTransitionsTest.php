<?php

use App\Enums\OrderStatus;
use Tests\Feature\OrderStateTransitions\SharedTestUtilities;

uses(SharedTestUtilities::class);

beforeEach(function () {
    $this->setUpStateTransitionTest();
});

describe('NEW Order Status Transitions', function () {

    describe('NEW → IN_PROGRESS', function () {
        it('allows transition and sends no notifications', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::NEW);

            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::IN_PROGRESS);

            expect($updatedOrder->status)->toBe(OrderStatus::IN_PROGRESS);
            $this->assertNoNotificationsSent();
        })->with([
            'single user' => ['single', false],
            'single guest' => ['single', true],
            'multiple user' => ['multiple', false],
            'multiple guest' => ['multiple', true],
            'mixed user' => ['mixed', false],
            'mixed guest' => ['mixed', true],
        ]);

        it('allows transition for illustration-only orders', function () {
            $order = $this->createIllustrationOnlyOrder(OrderStatus::NEW);

            // Illustration-only orders can also go to IN_PROGRESS
            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::IN_PROGRESS);

            expect($updatedOrder->status)->toBe(OrderStatus::IN_PROGRESS);
            $this->assertNoNotificationsSent();
        });
    });

    describe('NEW → PENDING_PAYMENT', function () {
        it('allows transition and sends payment link notification', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::NEW);

            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::PENDING_PAYMENT);

            expect($updatedOrder->status)->toBe(OrderStatus::PENDING_PAYMENT);
            $this->assertPaymentLinkNotificationSent($updatedOrder);
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

    describe('NEW → CANCELLED', function () {
        it('allows cancellation and sends notification with reason', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::NEW);

            // Should succeed with reason
            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::CANCELLED, [
                'reason' => 'Customer requested cancellation',
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

    describe('Invalid NEW transitions', function () {
        it('prevents invalid status transitions', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::NEW);

            // Cannot skip directly to later stages
            $this->assertTransitionNotAllowed($order, OrderStatus::PAID);
            $this->assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
            $this->assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
            $this->assertTransitionNotAllowed($order, OrderStatus::DONE);
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
});
