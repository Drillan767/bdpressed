<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ComicPage extends Model
{

    protected $fillable = [
        'comic_id',
        'image',
        'order',
    ];

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }
}
