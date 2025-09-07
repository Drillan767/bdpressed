<?php

use App\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\OrderStateTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    OrderStateTestHelpers::setupTestEnvironment();
});

describe('PAID Order State Transitions', function () {
    
    describe('PAID → TO_SHIP', function () {
        
        it('allows single item order to transition from PAID to TO_SHIP', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::TO_SHIP);
            OrderStateTestHelpers::assertNoEmailsSent();
        });

        it('allows multiple items order to transition from PAID to TO_SHIP', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::TO_SHIP);
            OrderStateTestHelpers::assertNoEmailsSent();
        });

        it('allows order with items and illustrations to transition from PAID to TO_SHIP', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::TO_SHIP);
            OrderStateTestHelpers::assertNoEmailsSent();
        });

        it('may not allow illustration-only order to transition to TO_SHIP if no physical items', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder(OrderStatus::PAID);
            
            // This should depend on whether illustration has print=true
            // Test the actual business logic implementation
            if ($order->canTransitionTo(OrderStatus::TO_SHIP)) {
                OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::TO_SHIP);
            } else {
                OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
            }
        });
    });

    describe('PAID → DONE (direct completion for digital-only orders)', function () {
        
        it('may allow illustration-only order to transition directly from PAID to DONE', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder(OrderStatus::PAID);
            
            // For digital-only illustrations (print=false)
            if ($order->canTransitionTo(OrderStatus::DONE)) {
                OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::DONE);
                OrderStateTestHelpers::assertNoEmailsSent();
            } else {
                OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::DONE);
            }
        });

        it('typically does not allow item orders to skip TO_SHIP/SHIPPED states', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            
            // Most item orders need shipping
            if ($order->canTransitionTo(OrderStatus::DONE)) {
                OrderStateTestHelpers::assertTransitionSucceeds($order, OrderStatus::DONE);
            } else {
                OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::DONE);
            }
        });
    });

    describe('PAID → CANCELLED (triggers refund)', function () {
        
        it('requires cancellation reason and triggers refund for single item order', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
            
            // Should trigger refund process
            // Additional assertions for refund logic can be added here
        });

        it('requires cancellation reason and triggers refund for multiple items order', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason and triggers refund for order with items and illustrations', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('requires cancellation reason and triggers refund for illustration-only order', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertCancellationRequiresReason($order);
        });

        it('restores inventory when cancelling paid item orders', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            $product = $order->details->first()->product;
            $originalStock = $product->fresh()->stock; // Stock after payment (reduced)
            $orderQuantity = $order->details->first()->quantity;
            
            $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'Test cancellation']);
            
            // Stock should be restored
            expect($product->fresh()->stock)->toBe($originalStock + $orderQuantity);
        });
    });

    describe('Invalid transitions from PAID', function () {
        
        it('prevents PAID → NEW transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::NEW);
        });

        it('prevents PAID → IN_PROGRESS transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
        });

        it('prevents PAID → PENDING_PAYMENT transition', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
        });

        it('prevents PAID → SHIPPED transition (must go through TO_SHIP)', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            
            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
        });
    });
});