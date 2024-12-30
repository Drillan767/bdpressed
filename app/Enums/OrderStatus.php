<?php

namespace App\Enums;

enum OrderStatus
{
    case NEW;
    case ILLUSTRATION_DEPOSIT_PENDING;
    case ILLUSTRATION_DEPOSIT_PAID;
    case PENDING_CLIENT_REVIEW;
    case IN_PROGRESS;
    case PAYMENT_PENDING;
    case PAID;
    case TO_SHIP;
    case DONE;
    case CANCELLED;
}
