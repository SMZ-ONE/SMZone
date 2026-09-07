<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'website_url',
        'tone_default',
        'lang_default',
        'brand_voice',
        'brand_story',
        'forbidden_words',
        'preferred_hashtags',
        'brand_keywords',
        'extra_instructions',
        'is_active',
    ];

    protected $casts = [
        'forbidden_words' => 'array',
        'preferred_hashtags' => 'array',
        'brand_keywords' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function activeForUser(?int $userId = null): ?self
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) return null;

        return static::where('user_id', $userId)->active()->latest()->first()
            ?? static::where('user_id', $userId)->latest()->first();
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
