<?php

namespace App\Services;

use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaPublishingService
{
    private const API_VERSION = 'v26.0';

    protected ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Facebook Sayfasına gönderi paylaşır. Görsel varsa /photos, yoksa
     * /feed kullanılır (Facebook, Instagram'ın aksine metin-only postu kabul eder).
     *
     * @return string|null Başarılıysa oluşan post ID'si, başarısızsa null.
     */
    public function publishToFacebook(SocialAccount $account, string $message, ?string $imageUrl = null): ?string
    {
        $this->lastError = null;

        $endpoint = $imageUrl
            ? "https://graph.facebook.com/".self::API_VERSION."/{$account->provider_id}/photos"
            : "https://graph.facebook.com/".self::API_VERSION."/{$account->provider_id}/feed";

        $payload = $imageUrl
            ? ['url' => $imageUrl, 'caption' => $message, 'access_token' => $account->access_token]
            : ['message' => $message, 'access_token' => $account->access_token];

        $response = Http::post($endpoint, $payload);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            Log::warning('MetaPublishingService: Facebook paylaşım hatası - '.$error);
            $this->lastError = $error;
            return null;
        }

        return $response->json('post_id') ?? $response->json('id');
    }

    /**
     * Instagram Business hesabına gönderi paylaşır. İki adımlı Graph API akışı:
     * 1) /media ile bir "container" oluşturulur, 2) /media_publish ile yayınlanır.
     * Görsel ZORUNLU - Instagram metin-only postu desteklemiyor.
     *
     * @return string|null Başarılıysa oluşan media ID'si, başarısızsa null.
     */
    public function publishToInstagram(SocialAccount $account, string $caption, ?string $imageUrl): ?string
    {
        $this->lastError = null;

        if (!$imageUrl) {
            $this->lastError = 'Instagram için görsel zorunlu - bu içerikte görsel yok (ürün seçilmemiş veya ürünün görseli yok).';
            return null;
        }

        $container = Http::post("https://graph.facebook.com/".self::API_VERSION."/{$account->provider_id}/media", [
            'image_url' => $imageUrl,
            'caption' => $caption,
            'access_token' => $account->access_token,
        ]);

        if (!$container->successful()) {
            $error = $container->json('error.message') ?? $container->body();
            Log::warning('MetaPublishingService: Instagram container hatası - '.$error);
            $this->lastError = $error;
            return null;
        }

        $creationId = $container->json('id');

        $publish = Http::post("https://graph.facebook.com/".self::API_VERSION."/{$account->provider_id}/media_publish", [
            'creation_id' => $creationId,
            'access_token' => $account->access_token,
        ]);

        if (!$publish->successful()) {
            $error = $publish->json('error.message') ?? $publish->body();
            Log::warning('MetaPublishingService: Instagram publish hatası - '.$error);
            $this->lastError = $error;
            return null;
        }

        return $publish->json('id');
    }
}
