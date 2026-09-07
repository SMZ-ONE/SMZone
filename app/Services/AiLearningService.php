<?php

namespace App\Services;

use App\Models\AiLearning;
use App\Models\ContentItem;

class AiLearningService
{
    public function logGeneration(
        ?int $userId,
        ?string $productName,
        ?string $productUrl,
        string $prompt,
        string $generated,
        string $platform,
        ?string $tone,
        ?string $lang,
        array $meta = []
    ): AiLearning {
        return AiLearning::create([
            'user_id' => $userId,
            'product_name' => $productName,
            'product_url' => $productUrl,
            'original_prompt' => $prompt,
            'original_content' => $generated,
            'edited_content' => null,
            'platform' => $platform,
            'tone' => $tone,
            'lang' => $lang,
            'feedback_type' => 'generated',
            'meta' => $meta,
        ]);
    }

    public function logEdit(ContentItem $contentItem, string $original, string $edited, string $feedbackType = 'edited'): AiLearning
    {
        return AiLearning::create([
            'user_id' => $contentItem->user_id,
            'product_name' => $contentItem->product?->name ?? $contentItem->title,
            'product_url' => $contentItem->meta['product_url'] ?? null,
            'original_prompt' => $contentItem->meta['custom_description'] ?? '',
            'original_content' => $original,
            'edited_content' => $edited,
            'platform' => $contentItem->platform,
            'tone' => $contentItem->meta['tone'] ?? null,
            'lang' => $contentItem->meta['lang'] ?? null,
            'feedback_type' => $feedbackType,
            'meta' => ['content_item_id' => $contentItem->id, 'title' => $contentItem->title],
        ]);
    }

    public function getBrandLearnings(?int $userId = null, int $limit = 20)
    {
        return AiLearning::when($userId, fn($q) => $q->where('user_id', $userId))
            ->whereNotNull('edited_content')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
