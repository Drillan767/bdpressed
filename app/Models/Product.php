<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

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
 * @property float $price
 * @property \DateTime $created_at
 * @property \DateTime $updated_at
 */
class Product extends Model
{
    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
        'price' => 'float',
        'illustrations' => 'array',
    ];

    public function illustrations(): Attribute
    {
        return Attribute::make(
            get: function($illustrations) {
                $parsedPaths = json_decode($illustrations, true);

                return array_map(function ($illustration) {
                    $realPath = str_replace('/storage', '', $illustration);

                    return [
                        'path' => $illustration,
                        'type' => Storage::mimeType($realPath),
                    ];
                }, $parsedPaths);
            }
        );
    }
}
