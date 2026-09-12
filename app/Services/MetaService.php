<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaService
{
    private const API_VERSION = 'v26.0';

    protected ?string $lastError = null;

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Verilen token'la erişilebilen tüm Facebook Sayfalarını ve her sayfaya
     * bağlı Instagram Business hesabını tek çağrıda keşfeder. Token bir User
     * Access Token olmalı (pages_show_list, instagram_basic izinleriyle) -
     * doğrudan bir Page/IG token verilirse /me/accounts boş dönebilir, bu
     * durumda hata mesajı kullanıcıya gösterilir.
     *
     * @return array<int, array{page_id:string,page_name:string,page_token:string,instagram:?array}>
     */
    public function discoverAccounts(string $userToken): array
    {
        $this->lastError = null;

        $response = Http::get('https://graph.facebook.com/'.self::API_VERSION.'/me/accounts', [
            'fields' => 'id,name,access_token,instagram_business_account{id,username,profile_picture_url}',
            'access_token' => $userToken,
        ]);

        if (!$response->successful()) {
            $message = $response->json('error.message') ?? $response->body();
            Log::warning('MetaService: discoverAccounts başarısız - '.$message);
            $this->lastError = $message;
            return [];
        }

        $pages = $response->json('data', []);

        if (empty($pages)) {
            $this->lastError = 'Bu token ile erişilebilen bir Facebook Sayfası bulunamadı. Token\'ın pages_show_list ve instagram_basic izinleriyle oluşturulduğundan emin olun.';
            return [];
        }

        return collect($pages)->map(function ($page) {
            $ig = $page['instagram_business_account'] ?? null;

            return [
                'page_id' => $page['id'],
                'page_name' => $page['name'],
                'page_token' => $page['access_token'],
                'instagram' => $ig ? [
                    'id' => $ig['id'],
                    'username' => $ig['username'] ?? null,
                    'avatar' => $ig['profile_picture_url'] ?? null,
                ] : null,
            ];
        })->toArray();
    }
}
