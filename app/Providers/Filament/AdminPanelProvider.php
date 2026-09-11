<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('SMZ ONE')
            ->brandLogo(asset('logo.webp'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('logo.webp'))
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Zinc,
            ])
            ->darkMode(true)
            // Faz A'da "php artisan make:filament-theme" ile oluşturulan özel tema -
            // saade/filament-fullcalendar gibi paketlerin CSS'i buraya import edilebilsin diye gerekli.
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // NOT: ContentCalendarWidget BİLİNÇLİ OLARAK burada değil - dashboard'da
                // değil, Content bölümünde (ListContentItems sayfasında header widget
                // olarak) gösteriliyor. Genel dashboard sadece StatsOverview + gerçek
                // veriyle çalışan kartları içeriyor.
                \App\Filament\Widgets\StatsOverview::class,
            ])
            ->plugins([
                // editable() eklendi - FullCalendar'ın kendi varsayılanı 'false'dur,
                // bu çağrılmadan sürükle-bırak (drag & drop) HİÇBİR ZAMAN aktif olmaz,
                // onEventDrop() doğru yazılmış olsa bile. Sürükle-bırağın "çalışmıyor"
                // görünmesinin asıl sebebi büyük ihtimalle buydu.
                FilamentFullCalendarPlugin::make()
                    ->editable(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
