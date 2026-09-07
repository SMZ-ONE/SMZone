<?php

namespace App\Filament\Widgets;

use App\Models\ContentItem;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ContentCalendarWidget extends FullCalendarWidget
{
    // Bilinçli olarak $model set edilmiyor: bu, paketin kendi create/edit/delete
    // modal CRUD akışını devre dışı bırakır. Düzenleme her zaman gerçek
    // ContentItemResource edit sayfasında (Brand/Learning entegrasyonu dahil)
    // yapılmalı - takvimde ayrı/basit bir ikinci düzenleme formu istemiyoruz,
    // bu da tam olarak Shopify'da yaşadığımız "iki paralel sistem" hatasını tekrarlardı.

    protected function headerActions(): array
    {
        return [];
    }

    /**
     * Haftalık/günlük görünüm butonları eklendi - önceden sadece varsayılan ay
     * görünümü vardı, geçiş yapacak bir buton hiç yoktu.
     */
    public function config(): array
    {
        return [
            'firstDay' => 1, // Pazartesi ile başlasın
            'headerToolbar' => [
                'left' => 'dayGridMonth,dayGridWeek,dayGridDay',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
        ];
    }

    /**
     * Görünen tarih aralığındaki ContentItem'ları FullCalendar'ın beklediği
     * event formatına çevirir. 'url' alanı sayesinde bir etkinliğe tıklanınca
     * doğrudan gerçek edit sayfasına gidilir - ayrı bir modal/form yok.
     *
     * 'extendedProps.image' - küçük önizleme görseli için eklendi (bkz.
     * eventDidMount()). Şu an ContentItem'ın kendi bir görseli olmadığı için
     * (media alanı henüz hiçbir yerde doldurulmuyor) ilişkili Product'ın
     * Shopify görseli fallback olarak kullanılıyor - ürünsüz/manuel girilen
     * içeriklerde görsel boş kalır, bu normal.
     */
    public function fetchEvents(array $fetchInfo): array
    {
        return ContentItem::query()
            ->with('product')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$fetchInfo['start'], $fetchInfo['end']])
            ->get()
            ->map(fn (ContentItem $item) => [
                'id' => $item->id,
                'title' => ($item->title ?: 'Untitled').' ('.ucfirst($item->platform).')',
                'start' => optional($item->scheduled_at)->toIso8601String(),
                'backgroundColor' => $this->colorForStatus($item->status),
                'borderColor' => $this->colorForStatus($item->status),
                'url' => "/admin/contents/content-items/{$item->id}/edit",
                'extendedProps' => [
                    'image' => $item->product?->image,
                ],
            ])
            ->toArray();
    }

    /**
     * Etkinliğin yanına küçük bir önizleme görseli (32x32) ekliyor -
     * extendedProps.image doluysa. FullCalendar'ın resmi "Render Hooks"
     * mekanizması kullanılıyor (paket dokümantasyonunda önerilen yöntem).
     */
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

    /**
     * Takvimde bir etkinlik sürüklenip başka bir güne/saate bırakıldığında
     * scheduled_at'i doğrudan günceller - hızlı yeniden planlama için.
     *
     * DÜZELTME: parent::onEventDrop() eklendi - paketin kendi dokümantasyonu
     * bu metodları override ederken parent'ın çağrılmaması durumunda "takvimin
     * düzgün çalışmaya devam etmeyeceğini" açıkça belirtiyor. Önceki hâlde bu
     * çağrı eksikti - sürükle-bırak veritabanını güncelliyordu ama takvimin
     * kendisi (JS tarafı) muhtemelen bu yüzden görsel olarak senkronsuz
     * kalıyordu / event bırakıldığı yerde durmuyordu.
     */
    public function onEventDrop(array $event, array $oldEvent, array $relatedEvents, array $delta, ?array $oldResource, ?array $newResource): bool
    {
        parent::onEventDrop($event, $oldEvent, $relatedEvents, $delta, $oldResource, $newResource);

        $item = ContentItem::find($event['id']);

        if (!$item || empty($event['start'])) {
            return false;
        }

        $item->update(['scheduled_at' => $event['start']]);

        return true;
    }

    private function colorForStatus(mixed $status): string
    {
        $value = $status instanceof \BackedEnum ? $status->value : $status;

        return match ($value) {
            'draft' => '#9ca3af',      // gri
            'scheduled' => '#f59e0b',  // amber
            'published' => '#22c55e', // yeşil
            'archived' => '#ef4444',  // kırmızı
            default => '#9ca3af',
        };
    }
}
