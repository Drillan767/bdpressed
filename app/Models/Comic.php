<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comic extends Model
{
    protected $casts = [
        'is_published' => 'boolean',
        'created_at' => 'datetime:d/m/Y H:i',
        'updated_at' => 'datetime:d/m/Y H:i',
    ];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'preview',
        'is_published',
        'instagram_url',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(ComicPage::class)->orderBy('order');
    }
}
