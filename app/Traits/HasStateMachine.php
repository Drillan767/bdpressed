<?php

namespace App\Traits;

use App\Exceptions\InvalidStateTransitionException;

trait HasStateMachine
{
    protected static $beforeTransitionCallbacks = [];

    protected static $afterTransitionCallbacks = [];

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
