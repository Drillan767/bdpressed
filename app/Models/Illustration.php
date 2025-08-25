<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\IllustrationStatus;
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
        $this->statusChanges()->create([
            'from_status' => $fromState?->value,
            'to_status' => $toState->value,
            'reason' => $context['reason'] ?? null,
            'metadata' => $context['metadata'] ?? null,
            'triggered_by' => $context['triggered_by'] ?? 'manual',
            'user_id' => auth()->id(),
        ]);
    }
}
