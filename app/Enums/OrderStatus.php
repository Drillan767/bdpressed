<?php

namespace App\Enums;

enum OrderStatus: string
{
    case NEW = 'NEW';
    case IN_PROGRESS = 'IN_PROGRESS';
    case PENDING_PAYMENT = 'PENDING_PAYMENT';
    case PAID = 'PAID';
    case TO_SHIP = 'TO_SHIP';
    case SHIPPED = 'SHIPPED';
    case DONE = 'DONE';
    case CANCELLED = 'CANCELLED';
}
