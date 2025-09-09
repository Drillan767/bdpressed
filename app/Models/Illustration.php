<?php

namespace App\Models;

use App\Actions\StatusTransitions\SendIllustrationNotificationAction;
use App\Actions\StatusTransitions\SyncOrderStatusAction;
use App\Casts\MoneyCast;
use App\Enums\IllustrationStatus;
use App\Services\IllustrationService;
use App\Services\StripeService;
use App\StateMachines\IllustrationStateMachine;
use App\Traits\HasStateMachine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $order_id
 * @property string $type
 * @property int $nbHumans
 * @property int $nbAnimals
 * @property int $nbItems
 * @property string $pose
 * @property string $background
 * @property IllustrationStatus $status
 * @property string $description
 * @property int $price
 * @property bool $print
 * @property bool $addTracking
 * @property string $trackingNumber
 * @property string $created_at
 * @property string $updated_at
 */
class Illustration extends Model
{
    use HasStateMachine;

    protected static function boot()
    {
        parent::boot();

        // Register status transition actions
        static::registerStatusTransitionActions();
    }

    protected static function registerStatusTransitionActions(): void
    {
        // Send notifications for specific transitions
        static::afterTransition(
            IllustrationStatus::PENDING,
            IllustrationStatus::DEPOSIT_PENDING,
            fn ($model, $from, $to, $context) => app(SendIllustrationNotificationAction::class)->execute($model, $from, $to, $context)
        );

        static::afterTransition(
            IllustrationStatus::DEPOSIT_PENDING,
            IllustrationStatus::DEPOSIT_PAID,
            fn ($model, $from, $to, $context) => app(SendIllustrationNotificationAction::class)->execute($model, $from, $to, $context)
        );

        static::afterTransition(
            IllustrationStatus::CLIENT_REVIEW,
            IllustrationStatus::PAYMENT_PENDING,
            fn ($model, $from, $to, $context) => app(SendIllustrationNotificationAction::class)->execute($model, $from, $to, $context)
        );

        static::afterTransition(
            IllustrationStatus::PAYMENT_PENDING,
            IllustrationStatus::COMPLETED,
            fn ($model, $from, $to, $context) => app(SendIllustrationNotificationAction::class)->execute($model, $from, $to, $context)
        );

        // Sync order status for specific transitions
        static::afterTransition(
            IllustrationStatus::DEPOSIT_PENDING,
            IllustrationStatus::DEPOSIT_PAID,
            fn ($model, $from, $to, $context) => app(SyncOrderStatusAction::class)->execute($model, $from, $to, $context)
        );

        static::afterTransition(
            IllustrationStatus::PAYMENT_PENDING,
            IllustrationStatus::COMPLETED,
            fn ($model, $from, $to, $context) => app(SyncOrderStatusAction::class)->execute($model, $from, $to, $context)
        );
    }

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
        'addTracking' => 'boolean',
        'print' => 'boolean',
        'price' => MoneyCast::class,
        'status' => IllustrationStatus::class,
    ];

    public function order(): belongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function statusChanges(): HasMany
    {
        return $this
            ->hasMany(IllustrationStatusChange::class)
            ->orderBy('created_at', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    protected function getStateMachine(): IllustrationStateMachine
    {
        return new IllustrationStateMachine;
    }

    protected function getCurrentState(): IllustrationStatus
    {
        return $this->status;
    }

    protected function setCurrentState($state): void
    {
        $this->status = is_object($state) ? $state : IllustrationStatus::from($state);
    }

    public function canBeCancelled(): bool
    {
        return $this->canTransitionTo(IllustrationStatus::CANCELLED);
    }

    public function isRefundable(): bool
    {
        return $this->getStateMachine()->isRefundable($this->status);
    }

    public function isAtPointOfNoReturn(): bool
    {
        return in_array($this->status, [
            IllustrationStatus::PAYMENT_PENDING,
            IllustrationStatus::COMPLETED,
        ]);
    }

    public function needsPaymentLink(): bool
    {
        return in_array($this->status, [
            IllustrationStatus::DEPOSIT_PENDING,
            IllustrationStatus::PAYMENT_PENDING,
        ]);
    }

    public function getAvailableStatuses(): array
    {
        return $this->getStateMachine()->getAvailableTransitions($this->status);
    }

    protected function executeAfterTransitionCallbacks($fromState, $toState, array $context): void
    {
        // Handle payment link generation for specific transitions
        if (! isset($context['skip_payment_creation'])) {
            $this->handlePaymentTransitions($toState);
        }

        // Execute any registered callbacks (from the trait)
        $key = static::getTransitionKey($fromState, $toState);
        $callbacks = static::$afterTransitionCallbacks[static::class][$key] ?? [];

        foreach ($callbacks as $callback) {
            $callback($this, $fromState, $toState, $context);
        }
    }

    private function handlePaymentTransitions(IllustrationStatus $toStatus): void
    {
        $illustrationService = app(IllustrationService::class);
        $stripeService = app(StripeService::class);

        match ($toStatus) {
            IllustrationStatus::DEPOSIT_PENDING => $illustrationService->createDepositPayment($this, $stripeService),
            IllustrationStatus::PAYMENT_PENDING => $illustrationService->createFinalPayment($this, $stripeService),
            default => null,
        };
    }
}
