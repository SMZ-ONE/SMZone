<?php

namespace App\Filament\Widgets;

use App\Models\ContentItem;
use Filament\Widgets\Widget;

class TodayScheduleBanner extends Widget
{
    protected string $view = 'filament.widgets.today-schedule-banner';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 0;

    public function getTodayCount(): int
    {
        return ContentItem::whereDate('scheduled_at', today())->count();
    }

    public function getWeekCount(): int
    {
        return ContentItem::whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
    }
}
