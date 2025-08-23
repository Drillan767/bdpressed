<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;
use App\Casts\MoneyCast;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;

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
}
