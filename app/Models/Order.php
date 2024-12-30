<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\OrderStatus;

class Order extends Model
{
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function illustration(): HasOne
    {
        return $this->hasOne(Illustration::class);
    }

    protected $casts = [
        'status' => OrderStatus::class,
    ];
}
