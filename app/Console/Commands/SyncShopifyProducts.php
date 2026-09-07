<?php

namespace App\Console\Commands;

use App\Services\ShopifyService;
use Illuminate\Console\Command;

/**
 * Asıl senkron mantığı ShopifyService::syncProductsToDatabase() içinde - bu komut
 * sadece onu çağırıp terminale okunaklı bir özet basıyor. Aynı metod admin
 * panelindeki "Sync Now" butonu (ProductSync sayfası) ve (açıksa) zamanlanmış
 * otomatik görev tarafından da kullanılıyor - mantık tek yerde.
 *
 * Kullanım: php artisan products:sync-shopify
 */
class SyncShopifyProducts extends Command
{
    protected $signature = 'products:sync-shopify';

    protected $description = 'Shopify kataloğundaki ürünleri yerel products tablosuna senkronize eder (upsert)';

    public function handle(ShopifyService $shopify): int
    {
        $this->info('Shopify\'dan ürünler çekiliyor, bu biraz sürebilir...');

        $result = $shopify->syncProductsToDatabase();

        if (!$result['success']) {
            $this->error($result['message']);
            return self::FAILURE;
        }

        $this->info("Tamamlandı - Shopify'da {$result['total']} ürün bulundu: {$result['created']} yeni eklendi, {$result['updated']} güncellendi".($result['skipped'] ? ", {$result['skipped']} atlandı (id yoktu)" : '').'.');

        return self::SUCCESS;
    }
}
