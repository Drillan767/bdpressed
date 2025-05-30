<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\IllustrationPriceObserver;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(IllustrationPriceObserver::class)]
class IllustrationPrice extends Model
{
    protected $fillable = [
        'name',
        'key',
        'price',
        'stripe_product_id',
        'stripe_price_id',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'metadata' => 'array',
    ];
} 