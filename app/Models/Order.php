<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public function illustration(): HasOne
    {
        return $this->hasOne(Illustration::class);
    }

    public function
}
