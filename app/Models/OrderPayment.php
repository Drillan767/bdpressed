<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;
use App\Casts\MoneyCast;

class OrderPayment extends Model
{
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
                return in_array($this->type, ['illustration_deposit', 'illustration_final']);
            }),
        ];
    }

    public function isForIllustration(): bool
    {
        return in_array($this->type, ['illustration_deposit', 'illustration_final']);
    }

    public function isFullyRefunded(): bool
    {
        return $this->refunded_amount >= $this->amount;
    }
}
