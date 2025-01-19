<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Guest extends Model
{
    protected $fillable = ['email'];

    public function orders(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(Address::class);
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(Address::class);
    }
}
