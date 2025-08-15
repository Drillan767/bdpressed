<?php

namespace App\Enums;

enum PaymentType: string
{
    case ORDER_FULL = 'order_full';
    case ILLUSTRATION_DEPOSIT = 'illustration_deposit';
    case ILLUSTRATION_FINAL = 'illustration_final';
}
