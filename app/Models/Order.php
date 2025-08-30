<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\OrderStatus;
use App\Services\OrderService;
use App\StateMachines\OrderStateMachine;
use App\Traits\HasStateMachine;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $total
 * @property int $shipmentFees
 * @property string $reference
 * @property string $additionalInfos
 * @property int $user_id
 * @property int $guest_id
 * @property int $shipping_address_id
 * @property int $billing_address_id
 * @property bool $useSameAddress
 * @property OrderStatus $status
 * @property string $created_at
 * @property string $updated_at
 */
class Order extends Model
{
    use HasFactory, HasStateMachine;

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'shipping_address_id')->withTrashed();
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'billing_address_id')->withTrashed();
    }

    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function illustrations(): HasMany
    {
        return $this->hasMany(Illustration::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function statusChanges(): HasMany
    {
        return $this->hasMany(OrderStatusChange::class);
    }

    protected $casts = [
        'status' => OrderStatus::class,
        'useSameAddress' => 'boolean',
        'total' => MoneyCast::class,
        'shipmentFees' => MoneyCast::class,
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
    ];

    protected function getStateMachine(): OrderStateMachine
    {
        return new OrderStateMachine;
    }

    protected function getCurrentState(): OrderStatus
    {
        return $this->status;
    }

    protected function setCurrentState($state): void
    {
        $this->status = is_object($state) ? $state : OrderStatus::from($state);
    }

    public function canBeCancelled(): bool
    {
        return $this->canTransitionTo(OrderStatus::CANCELLED);
    }

    public function requiresRefundOnCancellation(): bool
    {
        return $this->getStateMachine()->requiresRefund($this->status, OrderStatus::CANCELLED);
    }

    public function getAvailableStatuses(): array
    {
        return $this->getStateMachine()->getAvailableTransitions($this->status);
    }

    public function isIllustrationOnlyOrder(): bool
    {
        $orderService = app(OrderService::class);
        return $orderService->shouldSkipOrderPayment($this);
    }

    public function allIllustrationsCompleted(): bool
    {
        $orderService = app(OrderService::class);
        return $orderService->areAllIllustrationsCompleted($this);
    }

    public function stripeFees(): Attribute
    {
        return Attribute::make(
            get: function () {
                $orderService = app(OrderService::class);

                return $orderService->calculateStripeFeesOnly($this);
            }
        );
    }

    protected function executeAfterTransitionCallbacks($fromState, $toState, array $context): void
    {
        // Always log status changes regardless of specific transition
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
