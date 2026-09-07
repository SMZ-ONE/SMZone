<?php

use App\Models\Setting;
use App\Services\ShopifyService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Shopify Product Sync - opsiyonel, ayarla açılan zamanlama
|--------------------------------------------------------------------------
| Zamanlayıcı her 15 dakikada bir "çalışsın mı?" diye kontrol eder, ama
| gerçek Shopify isteğini SADECE şu ikisi de doğruysa atar:
|   1) Setting'te shopify_sync_enabled = "1" olmalı (varsayılan: kapalı)
|   2) shopify_sync_frequency_minutes ile belirlenen süre geçmiş olmalı
| Hiçbir şey ayarlamazsan bu blok var olsa bile SESSİZCE hiç çalışmaz -
| tamamen manuel (Sync Now butonu / artisan komutu) kalırsın.
|
| AÇMAK İÇİN:
|   php artisan setting:set shopify_sync_enabled 1
|   php artisan setting:set shopify_sync_frequency_minutes 360   (örn. 6 saatte bir)
| KAPATMAK İÇİN:
|   php artisan setting:set shopify_sync_enabled 0
|
| AYRICA ŞART: Hostinger'da (cPanel > Cron Jobs) şu satırın BİR KERE
| eklenmiş olması gerekiyor - Laravel'in kendi zamanlayıcısını her dakika
| "yoklar", gerçek işi yukarıdaki Setting kontrolü belirler:
|   * * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
|
| Ayarların şu anki durumunu (açık/kapalı, sıklık, son otomatik çalışma
| zamanı) admin panelinde "Product Sync" sayfasında görebilirsin.
*/
Schedule::call(function () {
    app(ShopifyService::class)->syncProductsToDatabase();
    Setting::set('shopify_last_auto_sync_at', now()->toDateTimeString());
})->everyFifteenMinutes()->when(function () {
    if (Setting::get('shopify_sync_enabled') !== '1') {
        return false;
    }

    $frequencyMinutes = (int) Setting::get('shopify_sync_frequency_minutes', 60);
    $lastRun = Setting::get('shopify_last_auto_sync_at');

    if (!$lastRun) {
        return true;
    }

    return now()->diffInMinutes(\Carbon\Carbon::parse($lastRun)) >= $frequencyMinutes;
});
