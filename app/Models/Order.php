<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\OrderStatus;
use App\Casts\MoneyCast;

/**
 * @property int $id
 * @property int $total
 * @property int $shipmentFees
 * @property int $stripeFees
 * @property string $reference
 * @property string $additionalInfos
 * @property int $user_id
 * @property int $guest_id
 * @property int $shipping_address_id
 * @property int $billing_address_id
 * @property bool $useSameAddress
 * @property OrderStatus $status
 * @property string $stripe_payment_link
 * @property string $stripe_payment_intent_id
 * @property string $paid_at
 * @property string $created_at
 * @property string $updated_at
 */
class Order extends Model
{
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

    protected $casts = [
        'status' => OrderStatus::class,
        'useSameAddress' => 'boolean',
        'total' => MoneyCast::class,
        'shipmentFees' => MoneyCast::class,
        'stripeFees' => MoneyCast::class,
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
        'paid_at' => 'datetime:d/m/Y H:i',
    ];
}
