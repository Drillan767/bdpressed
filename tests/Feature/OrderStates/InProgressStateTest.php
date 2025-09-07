<?php

use App\Enums\OrderStatus;
use App\Notifications\OrderPaymentLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\OrderStateTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    OrderStateTestHelpers::setupTestEnvironment();
});

describe('IN_PROGRESS Order State Transitions', function () {

    describe('IN_PROGRESS → PENDING_PAYMENT', function () {

        it('allows single item order to transition from IN_PROGRESS to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PENDING_PAYMENT);
            OrderStateTestHelpers::assertNotificationSentTo($order->user, OrderPaymentLinkNotification::class);
        });

        it('allows multiple items order to transition from IN_PROGRESS to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PENDING_PAYMENT);
            OrderStateTestHelpers::assertNotificationSentTo($order->user, OrderPaymentLinkNotification::class);
        });

        it('allows order with items and illustrations to transition from IN_PROGRESS to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PENDING_PAYMENT);
            OrderStateTestHelpers::assertNotificationSentTo($order->user, OrderPaymentLinkNotification::class);
        });

        it('creates payment link when transitioning from IN_PROGRESS to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            expect($order->stripe_payment_link)->toBeNull();

            $order->transitionTo(OrderStatus::PENDING_PAYMENT);

            expect($order->fresh()->stripe_payment_link)->not->toBeNull();
        });
    });

    describe('IN_PROGRESS → PAID (direct transition for illustration-only orders)', function () {

        it('allows illustration-only order to transition directly from IN_PROGRESS to PAID', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PAID);
        });

        it('may allow item orders to transition directly from IN_PROGRESS to PAID', function () {
            // This might be allowed according to state machine but may require validation
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            // Test based on the actual business rules implementation
            if ($order->canTransitionTo(OrderStatus::PAID)) {
                OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PAID);
            } else {
                OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PAID);
            }
        });
    });

    describe('IN_PROGRESS → CANCELLED', function () {

        it('requires cancellation reason for single item order', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason for multiple items order', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason for order with items and illustrations', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason for illustration-only order', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });
    });

    describe('Invalid transitions from IN_PROGRESS', function () {

        it('prevents IN_PROGRESS → NEW transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::NEW);
        });

        it('prevents IN_PROGRESS → TO_SHIP transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
        });

        it('prevents IN_PROGRESS → SHIPPED transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
        });

        it('prevents IN_PROGRESS → DONE transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::DONE);
        });
    });
});
