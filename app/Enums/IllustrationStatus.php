<?php

namespace App\Enums;

enum IllustrationStatus: string
{
    case PENDING = 'PENDING';
    case DEPOSIT_PENDING = 'DEPOSIT_PENDING';
    case DEPOSIT_PAID = 'DEPOSIT_PAID';
    case IN_PROGRESS = 'IN_PROGRESS';
    case CLIENT_REVIEW = 'CLIENT_REVIEW';
    case PAYMENT_PENDING = 'PAYMENT_PENDING';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
}
