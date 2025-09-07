<?php

use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\OrderStateTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    OrderStateTestHelpers::setupTestEnvironment();
});

describe('SHIPPED Order State Transitions', function () {

    describe('SHIPPED → DONE', function () {

        it('allows single item order to transition from SHIPPED to DONE', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::DONE);
            OrderStateTestHelpers::assertNoNotificationsSent();
        });

        it('allows multiple items order to transition from SHIPPED to DONE', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::DONE);
            OrderStateTestHelpers::assertNoNotificationsSent();
        });

        it('allows order with items and illustrations to transition from SHIPPED to DONE', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::DONE);
            OrderStateTestHelpers::assertNoNotificationsSent();
        });

        it('completes the order lifecycle without additional notifications', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            $order->transitionTo(OrderStatus::DONE);

            expect($order->fresh()->status)->toBe(OrderStatus::DONE);
            OrderStateTestHelpers::assertNoNotificationsSent();

            // Order should now be in terminal state
            OrderStateTestHelpers::assertTerminalState($order->fresh());
        });
    });

    describe('Invalid transitions from SHIPPED', function () {

        it('prevents SHIPPED → NEW transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::NEW);
        });

        it('prevents SHIPPED → IN_PROGRESS transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
        });

        it('prevents SHIPPED → PENDING_PAYMENT transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
        });

        it('prevents SHIPPED → PAID transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PAID);
        });

        it('prevents SHIPPED → TO_SHIP transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
        });

        it('prevents SHIPPED → CANCELLED transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::CANCELLED);
        });
    });

    describe('SHIPPED terminal behavior', function () {

        it('only allows transition to DONE from SHIPPED state', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::SHIPPED);

            // Should only allow DONE
            expect($order->canTransitionTo(OrderStatus::DONE))->toBeTrue();

            // All other transitions should be blocked
            $blockedStates = [
                OrderStatus::NEW,
                OrderStatus::IN_PROGRESS,
                OrderStatus::PENDING_PAYMENT,
                OrderStatus::PAID,
                OrderStatus::TO_SHIP,
                OrderStatus::CANCELLED
            ];

            foreach ($blockedStates as $state) {
                expect($order->canTransitionTo($state))->toBeFalse(
                    "SHIPPED should not allow transition to {$state->value}"
                );
            }
        });
    });
});
