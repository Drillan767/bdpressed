<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

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
        'user_id',
        'guest_id',
    ];

    protected $casts = [
        'default_shipping' => 'boolean',
        'default_billing' => 'boolean',
    ];
}
