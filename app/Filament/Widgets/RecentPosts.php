<?php

namespace App\Filament\Widgets;

use App\Models\ContentItem;
use Filament\Widgets\Widget;

class RecentPosts extends Widget
{
    protected string $view = 'filament.widgets.recent-posts';
    protected int|string|array $columnSpan = 1;
    protected static ?int $sort = 4;

    public function getRecentItems()
    {
        return ContentItem::query()
            ->with('product')
            ->latest()
            ->limit(5)
            ->get();
    }
}
