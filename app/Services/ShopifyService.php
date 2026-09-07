<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    // REST Admin API Ekim 2024'ten beri "legacy" - Shopify yeni entegrasyonlar için
    // GraphQL Admin API öneriyor. 2026-07, bu yazı itibarıyla güncel stabil sürüm.
    private const API_VERSION = '2026-07';

    public function isConfigured(): bool
    {
        return filled(Setting::get('shopify_store_domain')) && filled(Setting::get('shopify_access_token'));
    }

    /**
     * Ürün arar (başlığa göre) - Filament'in searchable Select'i için kullanılır.
     * $search boşsa son eklenen ürünleri döner.
     */
    public function searchProducts(string $search = '', int $limit = 20): array
    {
        $query = <<<'GQL'
        query SearchProducts($query: String, $first: Int!) {
          products(first: $first, query: $query, sortKey: UPDATED_AT, reverse: true) {
            edges {
              node {
                id
                title
                description
                productType
                vendor
                status
                featuredImage { url }
                onlineStoreUrl
              }
            }
          }
        }
        GQL;

        $variables = [
            'first' => $limit,
            'query' => $search !== '' ? "title:*{$search}*" : null,
        ];

        $edges = $this->graphql($query, $variables, 'data.products.edges', []);

        return collect($edges)->map(fn ($edge) => $this->mapProductNode($edge['node']))->toArray();
    }

    /**
     * Tek bir ürünü Shopify GraphQL ID'siyle (gid://shopify/Product/...) getirir.
     */
    public function find(string $gid): ?array
    {
        $query = <<<'GQL'
        query GetProduct($id: ID!) {
          product(id: $id) {
            id
            title
            description
            productType
            vendor
            status
            featuredImage { url }
            onlineStoreUrl
          }
        }
        GQL;

        $node = $this->graphql($query, ['id' => $gid], 'data.product', null);

        return $node ? $this->mapProductNode($node) : null;
    }

    /**
     * Shopify kataloğundaki TÜM ürünleri sayfalayarak (cursor-based pagination) çeker.
     * searchProducts() sadece ilk N sonucu getirir (Filament'in searchable Select'i
     * için yeterliydi) - senkron için ise mağazadaki her ürüne ihtiyacımız var.
     */
    public function fetchAllProducts(int $pageSize = 50): array
    {
        $all = [];
        $cursor = null;

        $query = <<<'GQL'
        query SyncProducts($first: Int!, $after: String) {
          products(first: $first, after: $after, sortKey: UPDATED_AT) {
            pageInfo { hasNextPage endCursor }
            edges {
              node {
                id
                title
                description
                productType
                vendor
                status
                featuredImage { url }
                onlineStoreUrl
                variants(first: 1) {
                  edges {
                    node {
                      price
                      sku
                      inventoryQuantity
                    }
                  }
                }
              }
            }
          }
        }
        GQL;

        do {
            $json = $this->graphqlRaw($query, ['first' => $pageSize, 'after' => $cursor]);

            // Bir sayfa çekerken hata olursa (ör. geçici ağ sorunu) o ana kadar
            // toplananla devam ediyoruz - hata zaten graphqlRaw() içinde loglandı.
            if ($json === null) {
                break;
            }

            $connection = data_get($json, 'data.products', []);
            $edges = $connection['edges'] ?? [];

            foreach ($edges as $edge) {
                $all[] = $this->mapProductNode($edge['node']);
            }

            $hasNext = (bool) data_get($connection, 'pageInfo.hasNextPage', false);
            $cursor = data_get($connection, 'pageInfo.endCursor');
        } while ($hasNext && $cursor);

        return $all;
    }

    /**
     * Asıl senkron mantığı - hem `products:sync-shopify` Artisan komutu hem de
     * admin panelindeki "Sync Now" butonu (ProductSync sayfası) hem de (açıksa)
     * zamanlanmış otomatik görev bunu çağırır. Mantık tek yerde, tekrar yazılmıyor.
     * shopify_id upsert anahtarı olduğu için tekrar tekrar güvenle çalıştırılabilir.
     *
     * NOT: price/sku/stock ilk/varsayılan varyanttan okunuyor - çoklu varyant desteği
     * (her varyant için ayrı fiyat/stok) kapsam dışı.
     */
    public function syncProductsToDatabase(): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Shopify ayarları eksik (domain/access token).'];
        }

        $products = $this->fetchAllProducts();

        if (empty($products)) {
            return ['success' => false, 'message' => "Shopify'dan hiç ürün gelmedi - storage/logs/laravel.log dosyasına bakın."];
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($products as $p) {
            if (empty($p['id'])) {
                $skipped++;
                continue;
            }

            $existed = Product::where('shopify_id', $p['id'])->exists();

            Product::updateOrCreate(
                ['shopify_id' => $p['id']],
                [
                    'name' => $p['name'] ?: 'Untitled',
                    'description' => $p['description'] ?? '',
                    'category' => $p['category'] ?: null,
                    'image' => $p['image'] ?? null,
                    'shopify_url' => $p['url'] ?? null,
                    'is_active' => ($p['status'] ?? 'ACTIVE') === 'ACTIVE',
                    'price' => $p['price'] ?? 0,
                    'sku' => $p['sku'] ?: null,
                    'stock' => $p['stock'] ?? 0,
                ]
            );

            $existed ? $updated++ : $created++;
        }

        return [
            'success' => true,
            'total' => count($products),
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    private function mapProductNode(array $node): array
    {
        // İlk/varsayılan variant'tan okunuyor - ürünün beden/hacim gibi birden fazla
        // varyantı varsa şimdilik sadece ilkinin fiyatı/stoku/SKU'su alınıyor.
        // Çoklu varyant desteği (her varyant için ayrı fiyat/stok) kapsam dışı bırakıldı.
        $variant = data_get($node, 'variants.edges.0.node');

        return [
            'id' => $node['id'],
            'name' => $node['title'] ?? '',
            // description alanı düz metin döner (descriptionHtml değil) - AiContentService'in
            // beklediği format zaten düz metin, ekstra strip_tags gerekmiyor.
            'description' => $node['description'] ?? '',
            'category' => $node['productType'] ?? '',
            'vendor' => $node['vendor'] ?? '',
            'status' => $node['status'] ?? 'ACTIVE',
            'image' => $node['featuredImage']['url'] ?? null,
            // Taslak/yayınlanmamış ürünlerde onlineStoreUrl null olabilir - normal.
            'url' => $node['onlineStoreUrl'] ?? null,
            'price' => $variant['price'] ?? null,
            'sku' => $variant['sku'] ?? null,
            'stock' => $variant['inventoryQuantity'] ?? null,
        ];
    }

    private function graphql(string $query, array $variables, string $resultPath, mixed $default): mixed
    {
        $json = $this->graphqlRaw($query, $variables);

        if ($json === null) {
            return $default;
        }

        return data_get($json, $resultPath, $default);
    }

    /**
     * Ham GraphQL isteğini atar, tam JSON'u döner (veya hata durumunda null).
     * fetchAllProducts() gibi pageInfo'ya da ihtiyaç duyan çağrılar için graphql()'in
     * tek bir resultPath'e indirgenmiş hâli yetersizdi - bu yüzden ayrıştırıldı.
     */
    private function graphqlRaw(string $query, array $variables): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $domain = $this->normalizeDomain(Setting::get('shopify_store_domain'));
        $token = Setting::get('shopify_access_token');

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'X-Shopify-Access-Token' => $token,
                    'Content-Type' => 'application/json',
                ])
                ->post("https://{$domain}/admin/api/".self::API_VERSION.'/graphql.json', [
                    'query' => $query,
                    'variables' => $variables,
                ]);

            if (!$response->successful()) {
                Log::warning('ShopifyService: API hatası '.$response->status().' - '.$response->body());
                return null;
            }

            $json = $response->json();

            if (!empty($json['errors'])) {
                Log::warning('ShopifyService: GraphQL hatası - '.json_encode($json['errors']));
                return null;
            }

            return $json;

        } catch (\Throwable $e) {
            Log::warning('ShopifyService: istek başarısız - '.$e->getMessage());
            return null;
        }
    }

    /**
     * Setting'e "https://www.xxx.myshopify.com" gibi tam URL girilmişse (Shopify panelinden
     * kopyala-yapıştırda sık olur) burada temizleniyor. Kod zaten önüne "https://" ekleyip
     * URL kuruyor - domain'de protokol kalırsa "https://https://..." çift protokolüne
     * yol açıyordu (cURL "Could not resolve host: https" hatası buradan geliyordu).
     * Admin API her zaman *.myshopify.com üzerinden çalışır, bu domain'de asla "www."
     * olmaz - o yüzden www. de güvenle temizleniyor.
     */
    private function normalizeDomain(?string $domain): string
    {
        $domain = trim((string) $domain);
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = preg_replace('#^www\.#i', '', $domain);
        return rtrim($domain, '/');
    }
}
