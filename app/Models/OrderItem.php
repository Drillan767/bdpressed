<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    public function details(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}