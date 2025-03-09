<?php

namespace App\Enums;

enum IllustrationStatus: string
{
    case PENDING = 'PENDING';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
    case DEPOSIT_PENDING = 'DEPOSIT_PENDING';
    case DEPOSIT_PAID = 'DEPOSIT_PAID';
    case PAYMENT_PENDING = 'PAYMENT_PENDING';
    case PAID = 'PAID';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
}