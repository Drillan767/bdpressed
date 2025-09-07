<?php

use App\Enums\OrderStatus;
use App\Exceptions\InvalidStateTransitionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\OrderStateTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    OrderStateTestHelpers::setupTestEnvironment();
});

describe('DONE Order State (Terminal)', function () {

    describe('DONE state immutability', function () {

        it('prevents any transitions from DONE state for single item orders', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTerminalState($order);
        });

        it('prevents any transitions from DONE state for multiple items orders', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTerminalState($order);
        });

        it('prevents any transitions from DONE state for orders with items and illustrations', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::DONE);

            OrderStateTestHelpers::assertTerminalState($order);
        });

        it('prevents any transitions from DONE state for illustration-only orders', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTerminalState($order);
        });
    });

    describe('Explicit transition blocks from DONE', function () {

        it('cannot transition DONE → NEW', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::NEW);
        });

        it('cannot transition DONE → IN_PROGRESS', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
        });

        it('cannot transition DONE → PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
        });

        it('cannot transition DONE → PAID', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PAID);
        });

        it('cannot transition DONE → TO_SHIP', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
        });

        it('cannot transition DONE → SHIPPED', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
        });

        it('cannot transition DONE → CANCELLED', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::CANCELLED);
        });
    });

    describe('DONE state validation', function () {

        it('throws exception when trying to force transition from DONE', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            expect(fn () => $order->transitionTo(OrderStatus::NEW))
                ->toThrow(InvalidStateTransitionException::class)
                ->and(fn () => $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'test']))
                ->toThrow(InvalidStateTransitionException::class);
        });

        it('maintains DONE status after failed transition attempts', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            try {
                $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'test']);
            } catch (InvalidStateTransitionException $e) {
                // Expected exception
            }

            expect($order->fresh()->status)->toBe(OrderStatus::DONE);
        });
    });

    describe('DONE state business logic', function () {

        it('represents successfully completed order lifecycle', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::DONE);

            // DONE status indicates:
            // - Payment was received
            // - Items were shipped (if applicable)
            // - Order is complete and delivered
            // - No further actions needed

            expect($order->status)->toBe(OrderStatus::DONE);

            // No emails should be sent when checking DONE orders
            OrderStateTestHelpers::assertNoNotificationsSent();
        });
    });
});
