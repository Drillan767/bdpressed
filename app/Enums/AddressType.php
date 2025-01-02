<?php

namespace App\Enums;

enum AddressType: string
{
    case SHIPPING = 'SHIPPING';
    case BILLING = 'BILLING';
}
