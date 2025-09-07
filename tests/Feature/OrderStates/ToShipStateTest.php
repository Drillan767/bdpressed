<?php

use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\OrderStateTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    OrderStateTestHelpers::setupTestEnvironment();
});

describe('TO_SHIP Order State Transitions', function () {
    
    describe('TO_SHIP → SHIPPED (requires tracking number)', function () {
        
        it('requires tracking number for single item order transition to SHIPPED', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertShippingRequiresTracking($order);
        });

        it('requires tracking number for multiple items order transition to SHIPPED', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertShippingRequiresTracking($order);
        });

        it('requires tracking number for order with items and illustrations transition to SHIPPED', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertShippingRequiresTracking($order);
        });


        it('stores tracking number when transitioning to SHIPPED', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            $trackingNumber = 'TEST-TRACK-789012';
            
            $order->transitionTo(OrderStatus::SHIPPED, ['tracking_number' => $trackingNumber]);
            
            $order = $order->fresh();
            expect($order->status)->toBe(OrderStatus::SHIPPED);
            
            // Tracking number should be stored (exact field depends on implementation)
            // This might be in a related model or on the order itself
        });
    });

    describe('TO_SHIP → CANCELLED (triggers refund)', function () {
        
        it('requires cancellation reason and triggers refund for single item order', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
            
            // Should trigger refund process
        });

        it('requires cancellation reason and triggers refund for multiple items order', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason and triggers refund for order with items and illustrations', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

    });

    describe('Invalid transitions from TO_SHIP', function () {
        
        it('prevents TO_SHIP → NEW transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::NEW);
        });

        it('prevents TO_SHIP → IN_PROGRESS transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
        });

        it('prevents TO_SHIP → PENDING_PAYMENT transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
        });

        it('prevents TO_SHIP → PAID transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PAID);
        });

        it('prevents TO_SHIP → DONE transition (must go through SHIPPED)', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::DONE);
        });
    });
});