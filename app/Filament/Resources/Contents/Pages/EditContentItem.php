<?php

namespace App\Filament\Resources\Contents\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\Contents\ContentItemResource;
use App\Models\SocialAccount;
use App\Services\AiLearningService;
use App\Services\MetaPublishingService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publishNow')
                ->label('Şimdi Yayınla')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => in_array($this->record->platform, ['facebook', 'instagram']))
                ->form([
                    TextInput::make('custom_image_url')
                        ->label('Görsel URL (öncelikli)')
                        ->url()
                        ->placeholder('https://...')
                        ->helperText('Zaten herkese açık bir yerde barınan görsel linki - doluysa aşağıdaki yükleme yok sayılır. Yerelde (smzone.test) test ederken bunu kullan, çünkü yüklenen dosyalar Meta\'ya erişilemez.'),
                    FileUpload::make('custom_image')
                        ->label('veya Görsel Yükle')
                        ->helperText('Sadece canlı (Hostinger) ortamda çalışır - yerelde Meta bu dosyaya erişemez.')
                        ->image()
                        ->disk('public')
                        ->directory('content-media'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $item = $this->record->fresh('product');

                    $account = SocialAccount::where('platform', $item->platform)
                        ->where('is_connected', true)
                        ->first();

                    if (!$account) {
                        Notification::make()
                            ->title('Bağlı hesap bulunamadı')
                            ->body(ucfirst($item->platform).' için bağlı bir hesap yok - önce Social Accounts sayfasından bağla.')
                            ->danger()
                            ->send();
                        return;
                    }

                    $imageUrl = !empty($data['custom_image_url'])
                        ? $data['custom_image_url']
                        : (!empty($data['custom_image'])
                            ? asset('storage/'.$data['custom_image'])
                            : $item->product?->image);

                    $publisher = app(MetaPublishingService::class);

                    $postId = match ($item->platform) {
                        'facebook' => $publisher->publishToFacebook($account, $item->body, $imageUrl),
                        'instagram' => $publisher->publishToInstagram($account, $item->body, $imageUrl),
                        default => null,
                    };

                    if (!$postId) {
                        Notification::make()
                            ->title('Yayınlanamadı')
                            ->body($publisher->getLastError() ?? 'Bilinmeyen hata')
                            ->danger()
                            ->send();
                        return;
                    }

                    $item->update([
                        'status' => ContentStatus::Published->value,
                        'published_at' => now(),
                        'meta' => array_merge($item->meta ?? [], ['post_id' => $postId]),
                    ]);

                    Notification::make()->title('Yayınlandı!')->success()->send();
                    $this->fillForm();
                }),
        ];
    }
}
