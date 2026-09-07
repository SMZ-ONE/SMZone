<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'social_account_id',
        'platform',
        'title',
        'body',
        'media',
        'status',
        'scheduled_at',
        'published_at',
        'meta',
    ];

    protected $casts = [
        'status' => ContentStatus::class,
        'media' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
        });
    }
}
