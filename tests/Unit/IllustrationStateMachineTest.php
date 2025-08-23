<?php

use App\Enums\IllustrationStatus;
use App\StateMachines\IllustrationStateMachine;

describe('IllustrationStateMachine', function () {
    beforeEach(function () {
        $this->stateMachine = new IllustrationStateMachine;
    });

    describe('valid transitions', function () {
        it('allows PENDING → DEPOSIT_PENDING', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::PENDING, IllustrationStatus::DEPOSIT_PENDING))
                ->toBeTrue();
        });

        it('allows PENDING → CANCELLED', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::PENDING, IllustrationStatus::CANCELLED))
                ->toBeTrue();
        });

        it('allows DEPOSIT_PENDING → DEPOSIT_PAID', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::DEPOSIT_PENDING, IllustrationStatus::DEPOSIT_PAID))
                ->toBeTrue();
        });

        it('allows DEPOSIT_PAID → IN_PROGRESS', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::DEPOSIT_PAID, IllustrationStatus::IN_PROGRESS))
                ->toBeTrue();
        });

        it('allows DEPOSIT_PAID → CANCELLED', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::DEPOSIT_PAID, IllustrationStatus::CANCELLED))
                ->toBeTrue();
        });

        it('allows IN_PROGRESS → CLIENT_REVIEW', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::IN_PROGRESS, IllustrationStatus::CLIENT_REVIEW))
                ->toBeTrue();
        });

        it('allows IN_PROGRESS → PAYMENT_PENDING (skip review)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::IN_PROGRESS, IllustrationStatus::PAYMENT_PENDING))
                ->toBeTrue();
        });

        it('allows IN_PROGRESS → CANCELLED', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::IN_PROGRESS, IllustrationStatus::CANCELLED))
                ->toBeTrue();
        });

        it('allows CLIENT_REVIEW → IN_PROGRESS (unlimited revisions)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::IN_PROGRESS))
                ->toBeTrue();
        });

        it('allows CLIENT_REVIEW → PAYMENT_PENDING (client approves)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::PAYMENT_PENDING))
                ->toBeTrue();
        });

        it('allows CLIENT_REVIEW → CANCELLED (artist discretion)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::CANCELLED))
                ->toBeTrue();
        });

        it('allows PAYMENT_PENDING → COMPLETED', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::PAYMENT_PENDING, IllustrationStatus::COMPLETED))
                ->toBeTrue();
        });
    });

    describe('invalid transitions', function () {
        it('prevents PENDING → DEPOSIT_PAID (skipping deposit pending)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::PENDING, IllustrationStatus::DEPOSIT_PAID))
                ->toBeFalse();
        });

        it('prevents DEPOSIT_PENDING → IN_PROGRESS (skipping deposit paid)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::DEPOSIT_PENDING, IllustrationStatus::IN_PROGRESS))
                ->toBeFalse();
        });

        it('prevents PAYMENT_PENDING → IN_PROGRESS (backward after point of no return)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::PAYMENT_PENDING, IllustrationStatus::IN_PROGRESS))
                ->toBeFalse();
        });

        it('prevents COMPLETED → any state (terminal)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::COMPLETED, IllustrationStatus::CANCELLED))
                ->toBeFalse()
                ->and($this->stateMachine->canTransition(IllustrationStatus::COMPLETED, IllustrationStatus::PENDING))
                ->toBeFalse();
        });

        it('prevents CANCELLED → any state (terminal)', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::CANCELLED, IllustrationStatus::PENDING))
                ->toBeFalse()
                ->and($this->stateMachine->canTransition(IllustrationStatus::CANCELLED, IllustrationStatus::IN_PROGRESS))
                ->toBeFalse();
        });
    });

    describe('getAvailableTransitions', function () {
        it('returns correct transitions for PENDING state', function () {
            $transitions = $this->stateMachine->getAvailableTransitions(IllustrationStatus::PENDING);

            expect($transitions)->toHaveCount(2)
                ->and($transitions)->toContain(IllustrationStatus::DEPOSIT_PENDING)
                ->and($transitions)->toContain(IllustrationStatus::CANCELLED);
        });

        it('returns correct transitions for CLIENT_REVIEW state (allows unlimited revisions)', function () {
            $transitions = $this->stateMachine->getAvailableTransitions(IllustrationStatus::CLIENT_REVIEW);

            expect($transitions)->toHaveCount(3)
                ->and($transitions)->toContain(IllustrationStatus::IN_PROGRESS)
                ->and($transitions)->toContain(IllustrationStatus::PAYMENT_PENDING)
                ->and($transitions)->toContain(IllustrationStatus::CANCELLED);
        });

        it('returns single transition for PAYMENT_PENDING state', function () {
            $transitions = $this->stateMachine->getAvailableTransitions(IllustrationStatus::PAYMENT_PENDING);

            expect($transitions)->toHaveCount(1)
                ->and($transitions)->toContain(IllustrationStatus::COMPLETED);
        });

        it('returns empty array for terminal states', function () {
            expect($this->stateMachine->getAvailableTransitions(IllustrationStatus::COMPLETED))
                ->toBeEmpty()
                ->and($this->stateMachine->getAvailableTransitions(IllustrationStatus::CANCELLED))
                ->toBeEmpty();
        });
    });

    describe('refund logic', function () {
        it('allows refunds before point of no return', function () {
            expect($this->stateMachine->isRefundable(IllustrationStatus::PENDING))
                ->toBeTrue()
                ->and($this->stateMachine->isRefundable(IllustrationStatus::DEPOSIT_PENDING))
                ->toBeTrue()
                ->and($this->stateMachine->isRefundable(IllustrationStatus::DEPOSIT_PAID))
                ->toBeTrue()
                ->and($this->stateMachine->isRefundable(IllustrationStatus::IN_PROGRESS))
                ->toBeTrue()
                ->and($this->stateMachine->isRefundable(IllustrationStatus::CLIENT_REVIEW))
                ->toBeTrue();
        });

        it('prevents refunds after point of no return', function () {
            expect($this->stateMachine->isRefundable(IllustrationStatus::PAYMENT_PENDING))
                ->toBeFalse()
                ->and($this->stateMachine->isRefundable(IllustrationStatus::COMPLETED))
                ->toBeFalse();
        });
    });

    describe('point of no return detection', function () {
        it('identifies CLIENT_REVIEW → PAYMENT_PENDING as point of no return', function () {
            expect($this->stateMachine->isPointOfNoReturn(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::PAYMENT_PENDING))
                ->toBeTrue();
        });

        it('does not flag other transitions as point of no return', function () {
            expect($this->stateMachine->isPointOfNoReturn(IllustrationStatus::PENDING, IllustrationStatus::DEPOSIT_PENDING))
                ->toBeFalse()
                ->and($this->stateMachine->isPointOfNoReturn(IllustrationStatus::IN_PROGRESS, IllustrationStatus::CLIENT_REVIEW))
                ->toBeFalse()
                ->and($this->stateMachine->isPointOfNoReturn(IllustrationStatus::PAYMENT_PENDING, IllustrationStatus::COMPLETED))
                ->toBeFalse();
        });
    });

    describe('warning requirements', function () {
        it('requires warning for any transition to CANCELLED', function () {
            expect($this->stateMachine->requiresWarning(IllustrationStatus::PENDING, IllustrationStatus::CANCELLED))
                ->toBeTrue()
                ->and($this->stateMachine->requiresWarning(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::CANCELLED))
                ->toBeTrue()
                ->and($this->stateMachine->requiresWarning(IllustrationStatus::IN_PROGRESS, IllustrationStatus::CANCELLED))
                ->toBeTrue();
        });

        it('does not require warning for non-cancellation transitions', function () {
            expect($this->stateMachine->requiresWarning(IllustrationStatus::PENDING, IllustrationStatus::DEPOSIT_PENDING))
                ->toBeFalse()
                ->and($this->stateMachine->requiresWarning(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::PAYMENT_PENDING))
                ->toBeFalse();
        });
    });

    describe('payment link triggers', function () {
        it('triggers payment link for DEPOSIT_PENDING transition', function () {
            expect($this->stateMachine->triggersPaymentLink(IllustrationStatus::PENDING, IllustrationStatus::DEPOSIT_PENDING))
                ->toBeTrue();
        });

        it('triggers payment link for PAYMENT_PENDING transition', function () {
            expect($this->stateMachine->triggersPaymentLink(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::PAYMENT_PENDING))
                ->toBeTrue();
        });

        it('does not trigger payment link for other transitions', function () {
            expect($this->stateMachine->triggersPaymentLink(IllustrationStatus::DEPOSIT_PENDING, IllustrationStatus::DEPOSIT_PAID))
                ->toBeFalse()
                ->and($this->stateMachine->triggersPaymentLink(IllustrationStatus::IN_PROGRESS, IllustrationStatus::CLIENT_REVIEW))
                ->toBeFalse();
        });
    });

    describe('unlimited revision workflow', function () {
        it('allows multiple CLIENT_REVIEW ↔ IN_PROGRESS transitions', function () {
            // Client requests changes multiple times
            expect($this->stateMachine->canTransition(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::IN_PROGRESS))
                ->toBeTrue()
                ->and($this->stateMachine->canTransition(IllustrationStatus::IN_PROGRESS, IllustrationStatus::CLIENT_REVIEW))
                ->toBeTrue()
                ->and($this->stateMachine->canTransition(IllustrationStatus::CLIENT_REVIEW, IllustrationStatus::IN_PROGRESS))
                ->toBeTrue()
                ->and($this->stateMachine->canTransition(IllustrationStatus::IN_PROGRESS, IllustrationStatus::CLIENT_REVIEW))
                ->toBeTrue();

            // This can happen unlimited times
        });
    });

    describe('handles string values', function () {
        it('accepts string values for transitions', function () {
            expect($this->stateMachine->canTransition('PENDING', 'DEPOSIT_PENDING'))
                ->toBeTrue()
                ->and($this->stateMachine->canTransition('CLIENT_REVIEW', 'PAYMENT_PENDING'))
                ->toBeTrue();
        });

        it('handles mixed enum and string values', function () {
            expect($this->stateMachine->canTransition(IllustrationStatus::PENDING, 'DEPOSIT_PENDING'))
                ->toBeTrue()
                ->and($this->stateMachine->canTransition('CLIENT_REVIEW', IllustrationStatus::PAYMENT_PENDING))
                ->toBeTrue();
        });
    });
});
