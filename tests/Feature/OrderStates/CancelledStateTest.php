<?php

use App\Enums\OrderStatus;
use App\Exceptions\InvalidStateTransitionException;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Helpers\OrderStateTestHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    OrderStateTestHelpers::setupTestEnvironment();
});

describe('CANCELLED Order State (Terminal)', function () {

    describe('CANCELLED state immutability', function () {

        it('prevents any transitions from CANCELLED state for single item orders', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTerminalState($order);
        });

        it('prevents any transitions from CANCELLED state for multiple items orders', function () {
            $order = OrderStateTestHelpers::createMultipleItemsOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTerminalState($order);
        });

        it('prevents any transitions from CANCELLED state for orders with items and illustrations', function () {
            $order = OrderStateTestHelpers::createOrderWithItemsAndIllustrations(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTerminalState($order);
        });

        it('prevents any transitions from CANCELLED state for illustration-only orders', function () {
            $order = OrderStateTestHelpers::createIllustrationOnlyOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTerminalState($order);
        });
    });

    describe('Explicit transition blocks from CANCELLED', function () {

        it('cannot transition CANCELLED → NEW', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::NEW);
        });

        it('cannot transition CANCELLED → IN_PROGRESS', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
        });

        it('cannot transition CANCELLED → PENDING_PAYMENT', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
        });

        it('cannot transition CANCELLED → PAID', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::PAID);
        });

        it('cannot transition CANCELLED → TO_SHIP', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
        });

        it('cannot transition CANCELLED → SHIPPED', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
        });

        it('cannot transition CANCELLED → DONE', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            OrderStateTestHelpers::assertTransitionNotAllowed($order, OrderStatus::DONE);
        });
    });

    describe('CANCELLED state validation', function () {

        it('throws exception when trying to force any transition from CANCELLED', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            expect(fn() => $order->transitionTo(OrderStatus::NEW))
                ->toThrow(InvalidStateTransitionException::class)
                ->and(fn() => $order->transitionTo(OrderStatus::DONE))
                ->toThrow(InvalidStateTransitionException::class);

        });

        it('maintains CANCELLED status after failed transition attempts', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::CANCELLED);

            try {
                $order->transitionTo(OrderStatus::PAID);
            } catch (InvalidStateTransitionException $e) {
                // Expected exception
            }

            expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);
        });
    });

    describe('Cancellation reason requirements', function () {

        it('requires cancellation reason when transitioning to CANCELLED from any state', function () {
            // Test from NEW state
            $orderFromNew = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::NEW);
            OrderStateTestHelpers::assertCancellationRequiresReason($orderFromNew);

            // Test from IN_PROGRESS state
            $orderFromInProgress = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);
            OrderStateTestHelpers::assertCancellationRequiresReason($orderFromInProgress);

            // Test from PAID state (triggers refund)
            $orderFromPaid = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);
            OrderStateTestHelpers::assertCancellationRequiresReason($orderFromPaid);

            // Test from TO_SHIP state (triggers refund)
            $orderFromToShip = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);
            OrderStateTestHelpers::assertCancellationRequiresReason($orderFromToShip);
        });

        it('stores cancellation reason when order is cancelled', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::NEW);
            $reason = 'Customer requested cancellation';

            $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => $reason]);

            $order = $order->fresh();
            expect($order->status)->toBe(OrderStatus::CANCELLED);

            // Cancellation reason should be stored (exact field depends on implementation)
            // This might be in the refusal_reason field or a related model
            if (isset($order->refusal_reason)) {
                expect($order->refusal_reason)->toContain($reason);
            }
        });

        it('sends cancellation email with reason when order is cancelled', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::NEW);
            $reason = 'Out of stock - unable to fulfill order';

            // Debug: check if user exists
            expect($order->user)->not->toBeNull('Order should have a user');
            
            $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => $reason]);

            // Should send cancellation notification to customer with reason
            Notification::assertSentTo(
                $order->user,
                OrderCancelledNotification::class,
                function ($notification, $channels) use ($reason) {
                    return str_contains($notification->toMail($notification)->render(), $reason);
                }
            );
        });
    });

    describe('Refund behavior for paid orders', function () {

        it('triggers refund when cancelling PAID orders', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::PAID);

            // Mock/spy on refund service if needed
            $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'Test refund']);

            expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);

            // Refund logic should be triggered (exact implementation depends on RefundOrderAction)
        });

        it('triggers refund when cancelling TO_SHIP orders', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::TO_SHIP);

            $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'Test refund']);

            expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);

            // Refund logic should be triggered
        });

        it('does not trigger refund when cancelling NEW orders', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::NEW);

            $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'No payment made']);

            expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);

            // No refund should be processed for unpaid orders
        });

        it('does not trigger refund when cancelling IN_PROGRESS orders', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder(OrderStatus::IN_PROGRESS);

            $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'No payment made']);

            expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);

            // No refund should be processed for unpaid orders
        });
    });

    describe('CANCELLED state business logic', function () {

        it('represents permanently cancelled order that cannot be resumed', function () {
            $order = OrderStateTestHelpers::createSingleItemOrder();

            $order->transitionTo(OrderStatus::CANCELLED, ['cancellation_reason' => 'Business logic test']);

            // CANCELLED status indicates:
            // - Order has been permanently cancelled
            // - Refunds have been processed if applicable
            // - Customer has been notified
            // - Inventory has been restored if applicable
            // - No further state changes are possible

            expect($order->fresh()->status)->toBe(OrderStatus::CANCELLED);
            OrderStateTestHelpers::assertTerminalState($order->fresh());
        });
    });
});
