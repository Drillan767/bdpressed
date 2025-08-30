<?php

namespace App\StateMachines;

use App\Enums\OrderStatus;

class OrderStateMachine
{
    protected array $transitions;

    public function __construct()
    {
        $this->transitions = [
            OrderStatus::NEW->value => [
                OrderStatus::IN_PROGRESS->value,
                OrderStatus::PENDING_PAYMENT->value,
                OrderStatus::CANCELLED->value,
            ],
            OrderStatus::PENDING_PAYMENT->value => [
                OrderStatus::PAID->value,
            ],
            OrderStatus::IN_PROGRESS->value => [
                OrderStatus::PENDING_PAYMENT->value,
                OrderStatus::PAID->value, // Allow direct transition for illustration-only orders
                OrderStatus::CANCELLED->value,
            ],
            OrderStatus::PAID->value => [
                OrderStatus::TO_SHIP->value,
                OrderStatus::DONE->value, // Allow direct completion for digital-only illustration orders
                OrderStatus::CANCELLED->value, // Triggers refund
            ],
            OrderStatus::TO_SHIP->value => [
                OrderStatus::SHIPPED->value,
                OrderStatus::CANCELLED->value, // Triggers refund
            ],
            OrderStatus::SHIPPED->value => [
                OrderStatus::DONE->value,
            ],
            // Terminal states
            OrderStatus::DONE->value => [],
            OrderStatus::CANCELLED->value => [],
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
            fn ($value) => OrderStatus::from($value),
            $availableValues
        );
    }

    public function requiresRefund($fromState, $toState): bool
    {
        $toValue = is_object($toState) ? $toState->value : $toState;
        $fromValue = is_object($fromState) ? $fromState->value : $fromState;

        return $toValue === OrderStatus::CANCELLED->value
            && in_array($fromValue, [
                OrderStatus::PAID->value,
                OrderStatus::TO_SHIP->value,
            ]);
    }

    public function requiresWarning($fromState, $toState): bool
    {
        $toValue = is_object($toState) ? $toState->value : $toState;

        return $toValue === OrderStatus::CANCELLED->value;
    }
}
