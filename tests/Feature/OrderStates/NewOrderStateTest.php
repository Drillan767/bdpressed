<?php

use App\Enums\OrderStatus;
use App\Notifications\OrderPaymentLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\OrderStateTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    OrderStateTestHelpers::setupTestEnvironment();
});

describe('NEW Order State Transitions', function () {
    
    describe('NEW → IN_PROGRESS', function () {
        
        it('allows single item order to transition to IN_PROGRESS', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::IN_PROGRESS);
            OrderStateTestHelpers::assertNoEmailsSent();
        });

        it('allows multiple items order to transition to IN_PROGRESS', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder();
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::IN_PROGRESS);
            OrderStateTestHelpers::assertNoEmailsSent();
        });

        it('allows order with items and illustrations to transition to IN_PROGRESS', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations();
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::IN_PROGRESS);
            OrderStateTestHelpers::assertNoEmailsSent();
        });

        it('fails for illustration-only order without validation', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder();
            
            OrderStateTestHelpers::assertTransitionFailsValidation($order, OrderStatus::IN_PROGRESS);
        });
    });

    describe('NEW → PENDING_PAYMENT', function () {
        
        it('allows single item order to transition to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PENDING_PAYMENT);
            OrderStateTestHelpers::assertEmailSent(OrderPaymentLinkNotification::class);
        });

        it('allows multiple items order to transition to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder();
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PENDING_PAYMENT);
            OrderStateTestHelpers::assertEmailSent(OrderPaymentLinkNotification::class);
        });

        it('allows order with items and illustrations to transition to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations();
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PENDING_PAYMENT);
            OrderStateTestHelpers::assertEmailSent(OrderPaymentLinkNotification::class);
        });

        it('allows illustration-only order to transition to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder();
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::PENDING_PAYMENT);
            OrderStateTestHelpers::assertEmailSent(OrderPaymentLinkNotification::class);
        });

        it('creates payment link when transitioning to PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();
            
            expect($order->stripe_payment_link)->toBeNull();
            
            $order->transitionTo(OrderStatus::PENDING_PAYMENT);
            
            expect($order->fresh()->stripe_payment_link)->not->toBeNull();
        });
    });

    describe('NEW → CANCELLED', function () {
        
        it('requires cancellation reason for single item order', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason for multiple items order', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder();
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason for order with items and illustrations', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations();
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason for illustration-only order', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder();
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });
    });

    describe('Invalid transitions from NEW', function () {
        
        it('prevents NEW → PAID transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PAID);
        });

        it('prevents NEW → TO_SHIP transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
        });

        it('prevents NEW → SHIPPED transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
        });

        it('prevents NEW → DONE transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::DONE);
        });
    });
});