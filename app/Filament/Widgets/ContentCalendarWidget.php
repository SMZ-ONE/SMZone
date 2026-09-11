<?php

namespace App\Filament\Widgets;

use App\Models\ContentItem;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ContentCalendarWidget extends FullCalendarWidget
{
    // $model set edilmiyor - CRUD modalı yerine gerçek edit sayfasına yönlendiriyoruz.

    protected function headerActions(): array
    {
        return [];
    }

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                'left' => 'dayGridMonth,timeGridWeek,timeGridDay',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'slotMinTime' => '06:00:00',
            'slotMaxTime' => '24:00:00',
            // Varsayılan 30 dk'lık dilimler eklenen etkinlikle sıkışıp görünmez
            // oluyordu - 1 saatlik dilimlere çıkarıldı, satırlar da mevcut alanı
            // doldursun diye expandRows açıldı.
            'slotDuration' => '01:00:00',
            'slotLabelInterval' => '01:00:00',
            'expandRows' => true,
            // 24 saat formatı - DACH/TR konvansiyonuna uygun, AM/PM yok.
            'slotLabelFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
            'eventTimeFormat' => [
                'hour' => '2-digit',
                'minute' => '2-digit',
                'hour12' => false,
            ],
        ];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        $items = ContentItem::query()
            ->with('product')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$fetchInfo['start'], $fetchInfo['end']])
            ->get();

        $events = $items->map(fn (ContentItem $item) => [
            'id' => $item->id,
            'title' => ($item->title ?: 'Untitled').' ('.ucfirst($item->platform).')',
            'start' => optional($item->scheduled_at)->toIso8601String(),
            'backgroundColor' => $this->colorForStatus($item->status),
            'borderColor' => $this->colorForStatus($item->status),
            'url' => "/admin/contents/content-items/{$item->id}/edit",
            'extendedProps' => [
                'image' => $item->product?->image,
            ],
        ])->toArray();

        return array_merge($events, $this->heatmapEvents($items, $fetchInfo));
    }

    /**
     * Ay/hafta görünümündeki gün hücrelerini yoğunluğa göre renklendiriyor.
     * FullCalendar'ın "background event" mekanizması kullanılıyor - ayrı bir
     * render hook gerekmiyor, normal event feed'e display:'background' ile
     * eklenen sahte event'ler günün arka planını boyuyor.
     */
    private function heatmapEvents($items, array $fetchInfo): array
    {
        $counts = $items->groupBy(fn (ContentItem $item) => $item->scheduled_at->format('Y-m-d'))->map->count();
        $max = max(1, $counts->max() ?? 1);

        $cursor = \Illuminate\Support\Carbon::parse($fetchInfo['start'])->startOfDay();
        $end = \Illuminate\Support\Carbon::parse($fetchInfo['end'])->startOfDay();

        $heatmap = [];

        while ($cursor->lt($end)) {
            $count = $counts->get($cursor->format('Y-m-d'), 0);

            if ($count > 0) {
                $level = (int) ceil(($count / $max) * 4);
                $heatmap[] = [
                    'start' => $cursor->format('Y-m-d'),
                    'display' => 'background',
                    'backgroundColor' => $this->heatmapColor($level),
                    'allDay' => true,
                ];
            }

            $cursor->addDay();
        }

        return $heatmap;
    }

    private function heatmapColor(int $level): string
    {
        return match ($level) {
            1 => 'rgba(59,130,246,0.15)',
            2 => 'rgba(59,130,246,0.30)',
            3 => 'rgba(59,130,246,0.45)',
            4 => 'rgba(59,130,246,0.60)',
            default => 'transparent',
        };
    }

    // Küçük önizleme görseli - extendedProps.image doluysa (product'tan geliyor)
    public function eventDidMount(): string
    {
        return <<<'JS'
        function({ event, el }) {
            const image = event.extendedProps.image;
            if (!image) return;

            const img = document.createElement('img');
            img.src = image;
            img.style.width = '20px';
            img.style.height = '20px';
            img.style.borderRadius = '4px';
            img.style.objectFit = 'cover';
            img.style.marginRight = '4px';
            img.style.display = 'inline-block';
            img.style.verticalAlign = 'middle';

            const titleEl = el.querySelector('.fc-event-title');
            if (titleEl) {
                titleEl.prepend(img);
            } else {
                el.prepend(img);
            }
        }
        JS;
    }

    // Dönüş değeri "revert edilsin mi" anlamında - false = kabul et, true = geri al.
    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        $item = ContentItem::find($event['id']);

        if (!$item || empty($event['start'])) {
            return true;
        }

        $item->update(['scheduled_at' => $event['start']]);

        return false;
    }

    private function colorForStatus(mixed $status): string
    {
        $value = $status instanceof \BackedEnum ? $status->value : $status;

        return match ($value) {
            'draft' => '#9ca3af',
            'scheduled' => '#f59e0b',
            'published' => '#22c55e',
            'archived' => '#ef4444',
            default => '#9ca3af',
        };
    }
}
