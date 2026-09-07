<?php

namespace App\Filament\Resources\Contents\Pages;

use App\Filament\Resources\Contents\ContentItemResource;
use App\Services\AiLearningService;
use Filament\Resources\Pages\EditRecord;

class EditContentItem extends EditRecord
{
    protected static string $resource = ContentItemResource::class;

    /**
     * Kaydetmeden hemen önceki (DB'deki) body değeri.
     * afterSave()'de yeni değerle kıyaslayıp fark varsa AiLearning'e loglamak için tutulur.
     */
    protected ?string $originalBody = null;

    protected function beforeSave(): void
    {
        // getOriginal() henüz save() çağrılmadığı için hâlâ DB'deki (eski) değeri döner,
        // form'dan gelen yeni değer bu noktada $this->record->body üzerinde fill edilmiş olsa bile.
        $this->originalBody = $this->record->getOriginal('body');
    }

    protected function afterSave(): void
    {
        $newBody = $this->record->body;

        // Gerçekten bir değişiklik yoksa (ör. sadece status değiştirildiyse) loglama - gürültü yaratmasın.
        if ($this->originalBody === null || $this->originalBody === $newBody) {
            return;
        }

        app(AiLearningService::class)->logEdit(
            $this->record,
            $this->originalBody,
            $newBody
        );
    }
}
