<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UrlScraperService
{
    /**
     * Verilen URL'den başlık ve açıklama çıkarır.
     * Güvenli değilse veya çekilemezse boş değerler döner (exception fırlatmaz).
     * Sonuç 6 saat cache'lenir - "Regenerate" butonuna art arda basınca aynı sayfa
     * tekrar tekrar ağdan çekilmesin diye (hem hız hem gereksiz dış istek).
     */
    public function scrape(string $url): array
    {
        if (!$this->isUrlSafe($url)) {
            Log::warning('UrlScraperService: güvensiz URL reddedildi', ['url' => $url]);
            return ['title' => '', 'description' => ''];
        }

        $cacheKey = 'url_scrape:'.md5($url);

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($url) {
            return $this->doScrape($url);
        });
    }

    private function doScrape(string $url): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->withOptions([
                    // Redirect zincirini de SSRF kontrolünden geçir - aksi halde
                    // dışarıya açık bir URL, iç ağa yönlendirme yapıp korumayı bypass edebilir.
                    'allow_redirects' => [
                        'max' => 3,
                        'on_redirect' => function ($request, $response, $uri) {
                            if (!$this->isUrlSafe((string) $uri)) {
                                throw new \RuntimeException('Güvensiz adrese yönlendirme engellendi: '.$uri);
                            }
                        },
                    ],
                ])
                ->get($url);

            $html = $response->body();

            preg_match('/<title>(.*?)<\/title>/is', $html, $m);
            preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\'](.*?)["\']/is', $html, $m2);
            if (empty($m2)) {
                preg_match('/<meta[^>]*property=["\']og:description["\'][^>]*content=["\'](.*?)["\']/is', $html, $m2);
            }

            return [
                'title' => $this->cleanTitle(html_entity_decode(trim($m[1] ?? ''), ENT_QUOTES)),
                'description' => html_entity_decode(trim($m2[1] ?? ''), ENT_QUOTES),
            ];
        } catch (\Throwable $e) {
            Log::warning('UrlScraperService: scrape başarısız - '.$e->getMessage(), ['url' => $url]);
            return ['title' => '', 'description' => ''];
        }
    }

    /**
     * <title> etiketleri genelde "Ürün Adı | Site Adı" veya "Ürün Adı – Site Adı"
     * formatında gelir (ör. "Organik Saç Bakım Kremi Fiyatları | Ecoshop").
     * Bu site-adı/kategori eki AI'ya ham veri olarak gidince cümlenin ortasında
     * garip bir şekilde yarı çevrilip kalabiliyor. En uzun parçayı (genelde asıl
     * ürün adı) tutup gerisini atıyoruz.
     */
    private function cleanTitle(string $title): string
    {
        $parts = preg_split('/\s*[\|\-–—]\s*/u', $title);
        if (count($parts) <= 1) {
            return $title;
        }

        $parts = array_filter(array_map('trim', $parts), fn ($p) => $p !== '');
        if (empty($parts)) {
            return $title;
        }

        usort($parts, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return $parts[array_key_first($parts)];
    }

    /**
     * SSRF koruması: sadece http/https, sadece standart web portları (80/443),
     * sadece public (dış) IP'ler. localhost, private range (10.x/172.16-31.x/192.168.x)
     * ve link-local/cloud metadata (169.254.x) adreslerini reddeder.
     */
    private function isUrlSafe(string $url): bool
    {
        $parts = parse_url($url);
        if (!$parts || empty($parts['host']) || !in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        if (!in_array($port, [80, 443], true)) {
            return false;
        }

        $host = strtolower($parts['host']);
        if ($host === 'localhost') {
            return false;
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
