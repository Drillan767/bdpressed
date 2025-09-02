<?php

namespace App\Actions\StatusTransitions;

use Illuminate\Database\Eloquent\Model;

abstract class BaseTransitionAction
{
    abstract public function execute(Model $model, $fromState, $toState, array $context = []): void;

    public function __invoke(Model $model, $fromState, $toState, array $context = []): void
    {
        $this->execute($model, $fromState, $toState, $context);
    }

    protected function shouldSkipAction(array $context): bool
    {
        return $context['skip_actions'] ?? false;
    }

    protected function isTriggeredBy(array $context, string $trigger): bool
    {
        return ($context['triggered_by'] ?? null) === $trigger;
    }
}
