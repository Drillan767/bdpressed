<?php

namespace App\Models;

use App\Enums\IllustrationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IllustrationStatusChange extends Model
{
    protected $fillable = [
        'illustration_id',
        'from_status',
        'to_status', 
        'reason',
        'metadata',
        'triggered_by',
        'user_id',
    ];

    protected $casts = [
        'from_status' => IllustrationStatus::class,
        'to_status' => IllustrationStatus::class,
        'metadata' => 'array',
    ];

    public function illustration(): BelongsTo
    {
        return $this->belongsTo(Illustration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
