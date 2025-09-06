<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Observers\ProductObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $quickDescription
 * @property string $description
 * @property string $promotedImage
 * @property string[] $illustrations
 * @property int $weight
 * @property int $stock
 * @property int $price
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
        'illustrations' => 'array',
        'price' => MoneyCast::class,
    ];
}
