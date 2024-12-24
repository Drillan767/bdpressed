<?php

namespace App\Models;

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
 * @property float $price
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Product extends Model
{
    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
        'price' => 'float',
    ];
}
