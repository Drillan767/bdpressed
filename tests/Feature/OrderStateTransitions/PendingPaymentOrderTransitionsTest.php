<?php

use App\Enums\OrderStatus;
use Tests\Feature\OrderStateTransitions\SharedTestUtilities;

uses(SharedTestUtilities::class);

beforeEach(function () {
    $this->setUpStateTransitionTest();
});

describe('PENDING_PAYMENT Order Status Transitions', function () {

    describe('PENDING_PAYMENT → PAID', function () {
        it('allows transition and sends payment confirmation notifications', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PENDING_PAYMENT);

            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::PAID);

            expect($updatedOrder->status)->toBe(OrderStatus::PAID);
            $this->assertPaymentConfirmationNotificationsSent($updatedOrder);
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

    describe('Invalid PENDING_PAYMENT transitions', function () {
        it('prevents backward transitions', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PENDING_PAYMENT);

            // Cannot go back to earlier states
            $this->assertTransitionNotAllowed($order, OrderStatus::NEW);
            $this->assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
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

        it('prevents skipping to later states', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PENDING_PAYMENT);

            // Cannot skip directly to shipping states
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

        it('does not allow cancellation from pending payment state', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::PENDING_PAYMENT);

            // Based on the state machine, PENDING_PAYMENT can only go to PAID
            $this->assertTransitionNotAllowed($order, OrderStatus::CANCELLED, 'No longer needed');
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
