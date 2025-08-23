<?php

use App\Enums\OrderStatus;
use App\StateMachines\OrderStateMachine;

describe('OrderStateMachine', function () {
    beforeEach(function () {
        $this->stateMachine = new OrderStateMachine();
    });

    describe('valid transitions', function () {
        it('allows NEW → IN_PROGRESS', function () {
            expect($this->stateMachine->canTransition(OrderStatus::NEW, OrderStatus::IN_PROGRESS))
                ->toBeTrue();
        });

        it('allows NEW → PENDING_PAYMENT', function () {
            expect($this->stateMachine->canTransition(OrderStatus::NEW, OrderStatus::PENDING_PAYMENT))
                ->toBeTrue();
        });

        it('allows NEW → CANCELLED', function () {
            expect($this->stateMachine->canTransition(OrderStatus::NEW, OrderStatus::CANCELLED))
                ->toBeTrue();
        });

        it('allows PENDING_PAYMENT → PAID', function () {
            expect($this->stateMachine->canTransition(OrderStatus::PENDING_PAYMENT, OrderStatus::PAID))
                ->toBeTrue();
        });

        it('allows IN_PROGRESS → PENDING_PAYMENT', function () {
            expect($this->stateMachine->canTransition(OrderStatus::IN_PROGRESS, OrderStatus::PENDING_PAYMENT))
                ->toBeTrue();
        });

        it('allows IN_PROGRESS → CANCELLED', function () {
            expect($this->stateMachine->canTransition(OrderStatus::IN_PROGRESS, OrderStatus::CANCELLED))
                ->toBeTrue();
        });

        it('allows PAID → TO_SHIP', function () {
            expect($this->stateMachine->canTransition(OrderStatus::PAID, OrderStatus::TO_SHIP))
                ->toBeTrue();
        });

        it('allows PAID → CANCELLED (triggers refund)', function () {
            expect($this->stateMachine->canTransition(OrderStatus::PAID, OrderStatus::CANCELLED))
                ->toBeTrue();
        });

        it('allows TO_SHIP → SHIPPED', function () {
            expect($this->stateMachine->canTransition(OrderStatus::TO_SHIP, OrderStatus::SHIPPED))
                ->toBeTrue();
        });

        it('allows TO_SHIP → CANCELLED (triggers refund)', function () {
            expect($this->stateMachine->canTransition(OrderStatus::TO_SHIP, OrderStatus::CANCELLED))
                ->toBeTrue();
        });

        it('allows SHIPPED → DONE', function () {
            expect($this->stateMachine->canTransition(OrderStatus::SHIPPED, OrderStatus::DONE))
                ->toBeTrue();
        });
    });

    describe('invalid transitions', function () {
        it('prevents NEW → TO_SHIP (skipping states)', function () {
            expect($this->stateMachine->canTransition(OrderStatus::NEW, OrderStatus::TO_SHIP))
                ->toBeFalse();
        });

        it('prevents PENDING_PAYMENT → IN_PROGRESS (backward)', function () {
            expect($this->stateMachine->canTransition(OrderStatus::PENDING_PAYMENT, OrderStatus::IN_PROGRESS))
                ->toBeFalse();
        });

        it('prevents PAID → NEW (backward)', function () {
            expect($this->stateMachine->canTransition(OrderStatus::PAID, OrderStatus::NEW))
                ->toBeFalse();
        });

        it('prevents SHIPPED → PAID (backward)', function () {
            expect($this->stateMachine->canTransition(OrderStatus::SHIPPED, OrderStatus::PAID))
                ->toBeFalse();
        });

        it('prevents transitions from terminal DONE state', function () {
            expect($this->stateMachine->canTransition(OrderStatus::DONE, OrderStatus::CANCELLED))
                ->toBeFalse()
                ->and($this->stateMachine->canTransition(OrderStatus::DONE, OrderStatus::NEW))
                ->toBeFalse();
        });

        it('prevents transitions from terminal CANCELLED state', function () {
            expect($this->stateMachine->canTransition(OrderStatus::CANCELLED, OrderStatus::NEW))
                ->toBeFalse()
                ->and($this->stateMachine->canTransition(OrderStatus::CANCELLED, OrderStatus::PAID))
                ->toBeFalse();
        });
    });

    describe('getAvailableTransitions', function () {
        it('returns correct transitions for NEW state', function () {
            $transitions = $this->stateMachine->getAvailableTransitions(OrderStatus::NEW);

            expect($transitions)->toHaveCount(3)
                ->and($transitions)->toContain(OrderStatus::IN_PROGRESS)
                ->and($transitions)->toContain(OrderStatus::PENDING_PAYMENT)
                ->and($transitions)->toContain(OrderStatus::CANCELLED);
        });

        it('returns correct transitions for PENDING_PAYMENT state', function () {
            $transitions = $this->stateMachine->getAvailableTransitions(OrderStatus::PENDING_PAYMENT);

            expect($transitions)->toHaveCount(1)
                ->and($transitions)->toContain(OrderStatus::PAID);
        });

        it('returns empty array for terminal DONE state', function () {
            $transitions = $this->stateMachine->getAvailableTransitions(OrderStatus::DONE);

            expect($transitions)->toBeEmpty();
        });

        it('returns empty array for terminal CANCELLED state', function () {
            $transitions = $this->stateMachine->getAvailableTransitions(OrderStatus::CANCELLED);

            expect($transitions)->toBeEmpty();
        });
    });

    describe('refund logic', function () {
        it('requires refund when PAID → CANCELLED', function () {
            expect($this->stateMachine->requiresRefund(OrderStatus::PAID, OrderStatus::CANCELLED))
                ->toBeTrue();
        });

        it('requires refund when TO_SHIP → CANCELLED', function () {
            expect($this->stateMachine->requiresRefund(OrderStatus::TO_SHIP, OrderStatus::CANCELLED))
                ->toBeTrue();
        });

        it('does not require refund when NEW → CANCELLED', function () {
            expect($this->stateMachine->requiresRefund(OrderStatus::NEW, OrderStatus::CANCELLED))
                ->toBeFalse();
        });

        it('does not require refund when IN_PROGRESS → CANCELLED', function () {
            expect($this->stateMachine->requiresRefund(OrderStatus::IN_PROGRESS, OrderStatus::CANCELLED))
                ->toBeFalse();
        });

        it('does not require refund for non-cancellation transitions', function () {
            expect($this->stateMachine->requiresRefund(OrderStatus::NEW, OrderStatus::IN_PROGRESS))
                ->toBeFalse();
        });
    });

    describe('warning requirements', function () {
        it('requires warning for any transition to CANCELLED', function () {
            expect($this->stateMachine->requiresWarning(OrderStatus::NEW, OrderStatus::CANCELLED))
                ->toBeTrue()
                ->and($this->stateMachine->requiresWarning(OrderStatus::PAID, OrderStatus::CANCELLED))
                ->toBeTrue()
                ->and($this->stateMachine->requiresWarning(OrderStatus::TO_SHIP, OrderStatus::CANCELLED))
                ->toBeTrue();
        });

        it('does not require warning for non-cancellation transitions', function () {
            expect($this->stateMachine->requiresWarning(OrderStatus::NEW, OrderStatus::IN_PROGRESS))
                ->toBeFalse()
                ->and($this->stateMachine->requiresWarning(OrderStatus::PAID, OrderStatus::TO_SHIP))
                ->toBeFalse();
        });
    });

    describe('handles string values', function () {
        it('accepts string values for transitions', function () {
            expect($this->stateMachine->canTransition('NEW', 'IN_PROGRESS'))
                ->toBeTrue()
                ->and($this->stateMachine->canTransition('PAID', 'CANCELLED'))
                ->toBeTrue();
        });

        it('handles mixed enum and string values', function () {
            expect($this->stateMachine->canTransition(OrderStatus::NEW, 'IN_PROGRESS'))
                ->toBeTrue()
                ->and($this->stateMachine->canTransition('PAID', OrderStatus::CANCELLED))
                ->toBeTrue();
        });
    });
});
