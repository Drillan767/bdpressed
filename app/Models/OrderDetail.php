<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    public function product(): belongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
