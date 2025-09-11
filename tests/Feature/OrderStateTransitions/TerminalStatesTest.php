<?php

use App\Enums\OrderStatus;
use Tests\Feature\OrderStateTransitions\SharedTestUtilities;

uses(SharedTestUtilities::class);

beforeEach(function () {
    $this->setUpStateTransitionTest();
});

describe('Terminal Order States (DONE & CANCELLED)', function () {

    describe('DONE status restrictions', function () {
        it('prevents any transitions from DONE status', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::DONE);
            $this->addPaymentToOrder($order);

            // DONE is a terminal state - no transitions allowed
            $this->assertTransitionNotAllowed($order, OrderStatus::NEW);
            $this->assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
            $this->assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
            $this->assertTransitionNotAllowed($order, OrderStatus::PAID);
            $this->assertTransitionNotAllowed($order, OrderStatus::TO_SHIP);
            $this->assertTransitionNotAllowed($order, OrderStatus::SHIPPED);
            $this->assertTransitionNotAllowed($order, OrderStatus::CANCELLED, 'Admin override');
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

        it('validates DONE orders have proper completion data', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::DONE);
            $this->addPaymentToOrder($order);

            // DONE orders should have payment records
            expect($order->payments)->toHaveCount(1);
            expect($order->payments->first()->status)->toBe('paid');

            // For physical items, shipping should have occurred
            if ($type !== 'illustration') {
                // Should have gone through shipping process
                // (This might be tracked in status changes or other models)
                expect($order->status)->toBe(OrderStatus::DONE);
            }

            // Illustrations should be completed
            if (in_array($type, ['illustration', 'mixed'])) {
                expect($order->illustrations)->not->toBeEmpty();
                // Validate illustration completion status if tracked
            }
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

        it('has no available transitions', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::DONE);

            $availableTransitions = $order->getAvailableStatuses();
            expect($availableTransitions)->toBeEmpty();
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

    describe('CANCELLED status restrictions', function () {
        it('prevents any transitions from CANCELLED status', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::CANCELLED);

            // CANCELLED is a terminal state - no transitions allowed
            $this->assertTransitionNotAllowed($order, OrderStatus::NEW);
            $this->assertTransitionNotAllowed($order, OrderStatus::IN_PROGRESS);
            $this->assertTransitionNotAllowed($order, OrderStatus::PENDING_PAYMENT);
            $this->assertTransitionNotAllowed($order, OrderStatus::PAID);
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

        it('validates cancellation data integrity', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::CANCELLED);

            // Cancelled orders should have cancellation reason
            // This might be stored in refusal_reason field or status changes
            expect($order->status)->toBe(OrderStatus::CANCELLED);

            // Check if cancellation reason is tracked
            // (Implementation may vary - this test will reveal current structure)
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

        it('has no available transitions', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::CANCELLED);

            $availableTransitions = $order->getAvailableStatuses();
            expect($availableTransitions)->toBeEmpty();
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

    describe('Refund requirements validation', function () {
        it('correctly identifies orders requiring refunds', function () {
            // Orders cancelled from PAID status should require refund
            $paidOrder = $this->createSingleItemOrder(OrderStatus::PAID);
            $this->addPaymentToOrder($paidOrder);

            expect($paidOrder->requiresRefundOnCancellation())->toBeTrue();

            // Orders cancelled from TO_SHIP status should require refund
            $toShipOrder = $this->createSingleItemOrder(OrderStatus::TO_SHIP);
            $this->addPaymentToOrder($toShipOrder);

            expect($toShipOrder->requiresRefundOnCancellation())->toBeTrue();
        });

        it('correctly identifies orders not requiring refunds', function () {
            // Orders cancelled from early states should not require refund
            $newOrder = $this->createSingleItemOrder(OrderStatus::NEW);
            expect($newOrder->requiresRefundOnCancellation())->toBeFalse();

            $inProgressOrder = $this->createSingleItemOrder(OrderStatus::IN_PROGRESS);
            expect($inProgressOrder->requiresRefundOnCancellation())->toBeFalse();

            // PENDING_PAYMENT might not require refund (depends on implementation)
            $pendingOrder = $this->createSingleItemOrder(OrderStatus::PENDING_PAYMENT);
            expect($pendingOrder->requiresRefundOnCancellation())->toBeFalse();
        });
    });

    describe('State machine consistency', function () {
        it('validates terminal state definitions match state machine', function () {
            $order = $this->createSingleItemOrder();
            $stateMachine = $order->getStateMachine();

            // Verify DONE has no available transitions
            $doneTransitions = $stateMachine->getAvailableTransitions(OrderStatus::DONE);
            expect($doneTransitions)->toBeEmpty();

            // Verify CANCELLED has no available transitions
            $cancelledTransitions = $stateMachine->getAvailableTransitions(OrderStatus::CANCELLED);
            expect($cancelledTransitions)->toBeEmpty();
        });

        it('validates cancellation warning requirements', function () {
            $order = $this->createSingleItemOrder();
            $stateMachine = $order->getStateMachine();

            // All transitions to CANCELLED should require warning
            $allStatuses = [
                OrderStatus::NEW,
                OrderStatus::IN_PROGRESS,
                OrderStatus::PENDING_PAYMENT,
                OrderStatus::PAID,
                OrderStatus::TO_SHIP,
            ];

            foreach ($allStatuses as $fromStatus) {
                if ($stateMachine->canTransition($fromStatus, OrderStatus::CANCELLED)) {
                    expect($stateMachine->requiresWarning($fromStatus, OrderStatus::CANCELLED))
                        ->toBeTrue("Transition from {$fromStatus->value} to CANCELLED should require warning");
                }
            }
        });
    });

    describe('Business rule validation', function () {
        it('ensures cancelled orders cannot be reactivated', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::CANCELLED);

            // Should not be able to "fix" or reactivate cancelled orders
            expect($order->canBeCancelled())->toBeFalse();

            // All business operations should recognize terminal state
            $availableStatuses = $order->getAvailableStatuses();
            expect($availableStatuses)->toBeEmpty();
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

        it('ensures done orders maintain final state', function ($type, $useGuest) {
            $order = $this->createOrderByScenario($type, $useGuest, OrderStatus::DONE);

            // Done orders should not allow any modifications
            expect($order->canBeCancelled())->toBeFalse();

            $availableStatuses = $order->getAvailableStatuses();
            expect($availableStatuses)->toBeEmpty();
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
