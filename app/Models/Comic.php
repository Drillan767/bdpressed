<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comic extends Model
{
    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(ComicPage::class);
    }
}
