<?php

namespace App\Filament\Widgets;

use App\Models\ContentItem;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class ContentHeatmap extends Widget
{
    protected string $view = 'filament.widgets.content-heatmap';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 5;

    /**
     * Son 12 haftayı Pazartesi-Pazar sütunlar halinde, gün başına içerik
     * sayısına göre yoğunluk seviyesiyle (0-4) döner - GitHub katkı grafiği mantığı.
     */
    public function getWeeks(): array
    {
        $start = now()->startOfWeek()->subWeeks(11);
        $end = now()->endOfWeek();

        $counts = ContentItem::query()
            ->whereBetween('scheduled_at', [$start, $end])
            ->get()
            ->groupBy(fn (ContentItem $item) => $item->scheduled_at->format('Y-m-d'))
            ->map->count();

        $maxCount = max(1, $counts->max() ?? 1);

        $weeks = [];
        $cursor = $start->copy();

        for ($w = 0; $w < 12; $w++) {
            $days = [];
            for ($d = 0; $d < 7; $d++) {
                $key = $cursor->format('Y-m-d');
                $count = $counts->get($key, 0);

                $days[] = [
                    'date' => $cursor->copy(),
                    'count' => $count,
                    'level' => $count === 0 ? 0 : (int) ceil(($count / $maxCount) * 4),
                ];

                $cursor->addDay();
            }
            $weeks[] = $days;
        }

        return $weeks;
    }
}
