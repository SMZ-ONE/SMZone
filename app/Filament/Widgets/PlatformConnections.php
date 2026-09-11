<?php

namespace App\Filament\Widgets;

use App\Models\SocialAccount;
use Filament\Widgets\Widget;

class PlatformConnections extends Widget
{
    protected string $view = 'filament.widgets.platform-connections';
    protected int|string|array $columnSpan = 1;
    protected static ?int $sort = 3;

    // AiWriter'ın platform seçenekleriyle aynı liste (instagram/tiktok/facebook)
    public function getPlatforms(): array
    {
        $known = ['instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok'];
        $accounts = SocialAccount::whereIn('platform', array_keys($known))->get()->keyBy('platform');

        return collect($known)->map(fn ($label, $key) => [
            'label' => $label,
            'connected' => $accounts->get($key)?->is_connected ?? false,
            'username' => $accounts->get($key)?->username,
            'last_synced_at' => $accounts->get($key)?->last_synced_at,
        ])->values()->toArray();
    }
}
