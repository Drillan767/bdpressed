<?php

use App\Enums\OrderStatus;
use Tests\Feature\OrderStateTransitions\SharedTestUtilities;

uses(SharedTestUtilities::class);

beforeEach(function () {
    $this->setUpStateTransitionTest();
});

describe('IN_PROGRESS Order Status Transitions', function () {

    describe('IN_PROGRESS → PENDING_PAYMENT', function () {
        it('allows transition and sends payment link notification', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::IN_PROGRESS);

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

    describe('IN_PROGRESS → PAID (direct transition)', function () {
        it('allows direct transition for illustration-only orders', function () {
            $order = $this->createIllustrationOnlyOrder(OrderStatus::IN_PROGRESS);

            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::PAID);

            expect($updatedOrder->status)->toBe(OrderStatus::PAID);
            $this->assertPaymentConfirmationNotificationsSent($updatedOrder);
        });
    });

    describe('IN_PROGRESS → CANCELLED', function () {
        it('allows cancellation and sends notification with reason', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::IN_PROGRESS);

            // Should succeed with reason
            $updatedOrder = $this->assertTransitionSucceeds($order, OrderStatus::CANCELLED, [
                'reason' => 'Production issues',
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

    describe('Invalid IN_PROGRESS transitions', function () {
        it('prevents backward transitions', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::IN_PROGRESS);

            // Cannot go back to earlier states
            $this->assertTransitionNotAllowed($order, OrderStatus::NEW);
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

        it('prevents skipping to shipping states', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::IN_PROGRESS);

            // Cannot skip directly to shipping states without payment
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
