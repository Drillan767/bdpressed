<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Casts\MoneyCast;
use App\Traits\HasStateMachine;
use App\StateMachines\IllustrationStateMachine;
use App\Enums\IllustrationStatus;

/**
 * @property int $order_id
 * @property string $type
 * @property int $nbHumans
 * @property int $nbAnimals
 * @property int $nbItems
 * @property string $pose
 * @property string $background
 * @property string $status
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

    protected function getStateMachine(): IllustrationStateMachine
    {
        return new IllustrationStateMachine();
    }

    protected function getCurrentState(): string
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
}
