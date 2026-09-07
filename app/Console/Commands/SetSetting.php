<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

/**
 * Setting değerlerini DAİMA model üzerinden (Setting::set()) yazar - bu yüzden
 * "encrypted" cast doğru şekilde uygulanır. Tinker'da yanlışlıkla
 * Setting::where(...)->update([...]) gibi toplu (mass) bir sorgu kullanılırsa
 * Eloquent cast'leri atlanır, düz metin yazılır ve sonraki Setting::get() çağrısı
 * "DecryptException: The payload is invalid" hatasıyla patlar. Bu komut o riski
 * ortadan kaldırıyor.
 *
 * Kullanım:
 *   php artisan setting:set shopify_store_domain bycosmetiq.myshopify.com
 *   php artisan setting:set gemini_api_key "AIza..."
 */
class SetSetting extends Command
{
    protected $signature = 'setting:set {key : Setting anahtarı, örn. shopify_store_domain} {value : Yeni değer}';

    protected $description = 'Bir Setting değerini model üzerinden (encrypted cast uygulanarak) güvenli şekilde yazar';

    public function handle(): int
    {
        $key = $this->argument('key');
        $value = $this->argument('value');

        Setting::set($key, $value);

        $this->info("'{$key}' güncellendi.");

        // Hemen okuyup doğruluyoruz - decrypt hatası varsa burada görünür.
        $readBack = Setting::get($key);
        $this->line("Doğrulama okuması: {$readBack}");

        return self::SUCCESS;
    }
}
