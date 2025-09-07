<?php

namespace App\Traits;

use App\Exceptions\InvalidStateTransitionException;

trait HasStateMachine
{
    protected static array $beforeTransitionCallbacks = [];

    protected static array $afterTransitionCallbacks = [];

    public function canTransitionTo(string|object $toState): bool
    {
        $stateMachine = $this->getStateMachine();
        $currentState = $this->getCurrentState();

        return $stateMachine->canTransition($currentState, $toState);
    }

    public function transitionTo(string|object $toState, array $context = []): static
    {
        $stateMachine = $this->getStateMachine();
        $currentState = $this->getCurrentState();
        $toStateValue = is_object($toState) ? $toState->value : $toState;

        if (! $stateMachine->canTransition($currentState, $toState)) {
            $currentStateValue = is_object($currentState) ? $currentState->value : $currentState;
            throw new InvalidStateTransitionException(
                "Cannot transition from {$currentStateValue} to {$toStateValue}"
            );
        }

        // Validate cancellation reason requirement
        if (method_exists($stateMachine, 'requiresCancellationReason') &&
            $stateMachine->requiresCancellationReason($currentState, $toState) &&
            empty($context['cancellation_reason'])) {
            throw new InvalidStateTransitionException(
                'Cancellation reason is required when transitioning to CANCELLED status'
            );
        }

        // Validate tracking number requirement
        if (method_exists($stateMachine, 'requiresTrackingNumber') &&
            $stateMachine->requiresTrackingNumber($currentState, $toState) &&
            empty($context['tracking_number'])) {
            throw new InvalidStateTransitionException(
                'Tracking number is required when transitioning to SHIPPED status'
            );
        }

        $this->executeBeforeTransitionCallbacks($currentState, $toState, $context);

        $this->setCurrentState($toState);
        $this->save();

        $this->executeAfterTransitionCallbacks($currentState, $toState, $context);

        return $this;
    }

    public static function beforeTransition(string|object $fromState, string|object $toState, callable $callback): void
    {
        $key = static::getTransitionKey($fromState, $toState);
        static::$beforeTransitionCallbacks[static::class][$key][] = $callback;
    }

    public static function afterTransition(string|object $fromState, string|object $toState, callable $callback): void
    {
        $key = static::getTransitionKey($fromState, $toState);
        static::$afterTransitionCallbacks[static::class][$key][] = $callback;
    }

    protected function executeBeforeTransitionCallbacks($fromState, $toState, array $context): void
    {
        $key = static::getTransitionKey($fromState, $toState);
        $callbacks = static::$beforeTransitionCallbacks[static::class][$key] ?? [];

        foreach ($callbacks as $callback) {
            $callback($this, $fromState, $toState, $context);
        }
    }

    protected function executeAfterTransitionCallbacks($fromState, $toState, array $context): void
    {
        $key = static::getTransitionKey($fromState, $toState);
        $callbacks = static::$afterTransitionCallbacks[static::class][$key] ?? [];

        foreach ($callbacks as $callback) {
            $callback($this, $fromState, $toState, $context);
        }

        $this->statusChanges()->create([
            'from_status' => $fromState?->value,
            'to_status' => $toState->value,
            'reason' => $context['reason'] ?? null,
            'metadata' => $context['metadata'] ?? null,
            'triggered_by' => $context['triggered_by'] ?? 'manual',
            'user_id' => auth()->id(),
        ]);
    }

    protected static function getTransitionKey($fromState, $toState): string
    {
        $from = is_object($fromState) ? $fromState->value : $fromState;
        $to = is_object($toState) ? $toState->value : $toState;

        return "{$from}->{$to}";
    }

    abstract protected function getStateMachine();

    abstract protected function getCurrentState();

    abstract protected function setCurrentState($state): void;
}
