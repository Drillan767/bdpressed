<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class OrderPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'illustration_id',
        'type',
        'status',
        'amount',
        'currency',
        'stripe_payment_intent_id',
        'stripe_payment_link',
        'stripe_fee',
        'description',
        'paid_at',
        'failed_at',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'stripe_metadata',
    ];

    protected $casts = [
        'type' => PaymentType::class,
        'status' => PaymentStatus::class,
        'amount' => MoneyCast::class,
        'refunded_amount' => MoneyCast::class,
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'stripe_metadata' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function illustration(): BelongsTo
    {
        return $this->belongsTo(Illustration::class);
    }

    public function rules()
    {
        return [
            'illustration_id' => Rule::requiredIf(function () {
                return in_array($this->type, [PaymentType::ILLUSTRATION_DEPOSIT, PaymentType::ILLUSTRATION_FINAL]);
            }),
        ];
    }

    public function isForIllustration(): bool
    {
        return in_array($this->type, [PaymentType::ILLUSTRATION_DEPOSIT, PaymentType::ILLUSTRATION_FINAL]);
    }

    public function isFullyRefunded(): bool
    {
        return $this->refunded_amount >= $this->amount;
    }

    public function adminDisplay(): Attribute
    {
        return Attribute::make(
            get: fn () => [
                'id' => $this->id,
                'type' => $this->type->value === 'illustration_deposit' ? 'Acompte' : 'Paiement final',
                'amount' => $this->amount->formatted(),
                'status' => $this->status->value,
                'paid_at' => $this->paid_at?->format('d/m/Y H:i'),
                'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
                'stripe_fee' => $this->stripe_fee ?? 0,
                // 'stripe_fee' => $this->stripe_fee ? MoneyCast::of($this->stripe_fee, 'EUR')->formatted() : null,
                'stripe_payment_link' => $this->status === PaymentStatus::PENDING ? $this->stripe_payment_link : null,
                'description' => $this->description,
            ]
        );
    }
}
