<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Guest extends Model
{
    public function orders(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function ShippingAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('type', 'SHIPPING');
    }

    public function BillingAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('type', 'BILLING');
    }
}