<?php

use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\OrderStateTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    OrderStateTestHelpers::setupTestEnvironment();
});

describe('PENDING_PAYMENT Order State Transitions', function () {

    describe('PENDING_PAYMENT → PAID', function () {

        it('allows single item order to transition from PENDING_PAYMENT to PAID', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PAID);
        });

        it('allows multiple items order to transition from PENDING_PAYMENT to PAID', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PAID);
        });

        it('allows order with items and illustrations to transition from PENDING_PAYMENT to PAID', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PAID);
        });

        it('allows illustration-only order to transition from PENDING_PAYMENT to PAID', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PAID);
        });

    });

    describe('Invalid transitions from PENDING_PAYMENT', function () {

        it('prevents PENDING_PAYMENT → NEW transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::NEW);
        });

        it('prevents PENDING_PAYMENT → IN_PROGRESS transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
        });

        it('prevents PENDING_PAYMENT → TO_SHIP transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
        });

        it('prevents PENDING_PAYMENT → SHIPPED transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
        });

        it('prevents PENDING_PAYMENT → DONE transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::DONE);
        });

        it('prevents PENDING_PAYMENT → CANCELLED transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PENDING_PAYMENT);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::CANCELLED);
        });
    });
});
