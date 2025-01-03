<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //

    protected $fillable = [
        'firstName',
        'lastName',
        'street',
        'street2',
        'city',
        'zipCode',
        'country',
        'type',
        'default',
        'user_billing_id',
        'user_shipping_id',
        'guest_billing_id',
        'guest_shipping_id',
    ];

    protected $casts = [
        'default' => 'boolean',
    ];
}
