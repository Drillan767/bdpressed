<?php

namespace App\StateMachines;

use App\Enums\IllustrationStatus;

class IllustrationStateMachine
{
    protected array $transitions;

    public function __construct()
    {
        $this->transitions = [
            IllustrationStatus::PENDING->value => [
                IllustrationStatus::DEPOSIT_PENDING->value,
                IllustrationStatus::CANCELLED->value,
            ],
            IllustrationStatus::DEPOSIT_PENDING->value => [
                IllustrationStatus::DEPOSIT_PAID->value,
            ],
            IllustrationStatus::DEPOSIT_PAID->value => [
                IllustrationStatus::IN_PROGRESS->value,
                IllustrationStatus::CANCELLED->value,
            ],
            IllustrationStatus::IN_PROGRESS->value => [
                IllustrationStatus::CLIENT_REVIEW->value,
                IllustrationStatus::PAYMENT_PENDING->value,
                IllustrationStatus::CANCELLED->value,
            ],
            IllustrationStatus::CLIENT_REVIEW->value => [
                IllustrationStatus::IN_PROGRESS->value,
                IllustrationStatus::PAYMENT_PENDING->value,
                IllustrationStatus::CANCELLED->value,
            ],
            IllustrationStatus::PAYMENT_PENDING->value => [
                IllustrationStatus::COMPLETED->value,
            ],
            // Terminal states
            IllustrationStatus::COMPLETED->value => [],
            IllustrationStatus::CANCELLED->value => [],
        ];
    }

    public function canTransition($fromState, $toState): bool
    {
        $fromValue = is_object($fromState) ? $fromState->value : $fromState;
        $toValue = is_object($toState) ? $toState->value : $toState;

        return in_array($toValue, $this->transitions[$fromValue] ?? []);
    }

    public function getAvailableTransitions($fromState): array
    {
        $fromValue = is_object($fromState) ? $fromState->value : $fromState;
        $availableValues = $this->transitions[$fromValue] ?? [];

        return array_map(
            fn ($value) => IllustrationStatus::from($value),
            $availableValues
        );
    }

    public function isRefundable($fromState): bool
    {
        $fromValue = is_object($fromState) ? $fromState->value : $fromState;

        // Full refund possible before CLIENT_REVIEW approval
        return ! in_array($fromValue, [
            IllustrationStatus::PAYMENT_PENDING->value,
            IllustrationStatus::COMPLETED->value,
        ]);
    }

    public function isPointOfNoReturn($fromState, $toState): bool
    {
        $fromValue = is_object($fromState) ? $fromState->value : $fromState;
        $toValue = is_object($toState) ? $toState->value : $toState;

        // CLIENT_REVIEW → PAYMENT_PENDING is the point of no return
        return $fromValue === IllustrationStatus::CLIENT_REVIEW->value
            && $toValue === IllustrationStatus::PAYMENT_PENDING->value;
    }

    public function requiresWarning($fromState, $toState): bool
    {
        $toValue = is_object($toState) ? $toState->value : $toState;

        return $toValue === IllustrationStatus::CANCELLED->value;
    }

    public function triggersPaymentLink($fromState, $toState): bool
    {
        $toValue = is_object($toState) ? $toState->value : $toState;

        return in_array($toValue, [
            IllustrationStatus::DEPOSIT_PENDING->value,
            IllustrationStatus::PAYMENT_PENDING->value,
        ]);
    }
}
