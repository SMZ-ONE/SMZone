<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Setting;
use App\Services\ShopifyService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use BackedEnum;
use UnitEnum;

class ProductSync extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';
    protected static string|UnitEnum|null $navigationGroup = 'SMZ ONE - Settings';
    protected string $view = 'filament.pages.product-sync';
    protected static ?string $title = 'Product Sync';
    protected static ?int $navigationSort = 3;

    public ?array $data = [];
    public ?string $lastRunSummary = null;
    public ?string $lastAutoSyncAt = null;

    public function mount(): void
    {
        // "Sync Now" butonu tıklanmadan önce ekranda son senkron zamanını göstermek
        // için - syncProductsToDatabase() her başarılı çalıştığında bu Setting
        // güncelleniyor (bkz. syncNow()).
        $this->lastRunSummary = Setting::get('shopify_last_sync_summary');
        $this->lastAutoSyncAt = Setting::get('shopify_last_auto_sync_at');

        // Otomatik zamanlama artık CLI ("php artisan setting:set ...") değil,
        // doğrudan bu formdan yönetiliyor.
        $this->form->fill([
            'enabled' => Setting::get('shopify_sync_enabled') === '1',
            'frequency_minutes' => Setting::get('shopify_sync_frequency_minutes') !== null
                ? (int) Setting::get('shopify_sync_frequency_minutes')
                : 360,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('enabled')
                ->label('Otomatik senkron aktif')
                ->live()
                ->helperText('Açıksa, aşağıdaki sıklıkla Shopify kataloğu otomatik olarak yerel veritabanına senkronize edilir.'),

            Select::make('frequency_minutes')
                ->label('Sıklık')
                ->options([
                    15 => '15 dakikada bir',
                    30 => '30 dakikada bir',
                    60 => 'Saatte bir',
                    180 => '3 saatte bir',
                    360 => '6 saatte bir',
                    720 => '12 saatte bir',
                    1440 => 'Günde bir',
                ])
                ->default(360)
                ->native(false)
                ->visible(fn (callable $get) => (bool) $get('enabled'))
                ->required(fn (callable $get) => (bool) $get('enabled')),
        ])->statePath('data');
    }

    public function saveScheduleSettings(): void
    {
        $state = $this->form->getState();

        Setting::set('shopify_sync_enabled', $state['enabled'] ? '1' : '0');

        if ($state['enabled']) {
            Setting::set('shopify_sync_frequency_minutes', (string) $state['frequency_minutes']);
        }

        Notification::make()->title('Zamanlama ayarları kaydedildi')->success()->send();
    }

    public function syncNow(ShopifyService $shopify): void
    {
        $result = $shopify->syncProductsToDatabase();

        if (!$result['success']) {
            Notification::make()->title('Senkron başarısız')->body($result['message'])->danger()->send();
            return;
        }

        $summary = "{$result['total']} ürün bulundu: {$result['created']} yeni, {$result['updated']} güncellendi".
            ($result['skipped'] ? ", {$result['skipped']} atlandı" : '').
            ' - '.now()->format('d.m.Y H:i');

        Setting::set('shopify_last_sync_summary', $summary);
        $this->lastRunSummary = $summary;

        Notification::make()->title('Senkron tamamlandı')->body($summary)->success()->send();
    }

    public function getTotalProductsProperty(): int
    {
        return Product::count();
    }
}
