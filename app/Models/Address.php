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
        'user_id',
        'guest_id',
    ];

    protected $casts = [
        'default' => 'boolean',
    ];
}
