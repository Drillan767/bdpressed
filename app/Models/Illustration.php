<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $order_id
 * @property string $type
 * @property int $nbHumans
 * @property int $nbAnimals
 * @property int $nbItems
 * @property string $pose
 * @property string $background
 * @property string $status
 * @property string $description
 * @property float price
 * @property string $status
 * @property bool $print
 * @property bool $addTracking
 * @property string $trackingNumber
 * @property string $created_at
 * @property string $updated_at
 */
class Illustration extends Model
{
    protected $casts = [
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
        'addTracking' => 'boolean',
        'print' => 'boolean',
    ];

    public function order(): belongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
