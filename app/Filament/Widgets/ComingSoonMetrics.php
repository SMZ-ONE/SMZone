<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class ComingSoonMetrics extends Widget
{
    protected string $view = 'filament.widgets.coming-soon-metrics';
    protected int|string|array $columnSpan = 2;
    protected static ?int $sort = 4;

    /**
     * Her kart, ileride hangi servis/entegrasyonun bu alanı dolduracağını
     * belirtiyor - Faz 3 (Analytics) kapsamına girdiğinde buradaki placeholder'lar
     * tek tek gerçek veriyle değiştirilecek, kartların yeri/düzeni değişmeyecek.
     */
    public function getCards(): array
    {
        return [
            [
                'icon' => 'heroicon-o-heart',
                'label' => 'Toplam Etkileşim',
                'note' => 'Instagram/Facebook API bağlanınca',
            ],
            [
                'icon' => 'heroicon-o-user-plus',
                'label' => 'Yeni Takipçi',
                'note' => 'Instagram/Facebook API bağlanınca',
            ],
            [
                'icon' => 'heroicon-o-clock',
                'label' => 'En İyi Yayın Saatleri',
                'note' => 'Yeterli etkileşim verisi birikince',
            ],
            [
                'icon' => 'heroicon-o-light-bulb',
                'label' => 'AI Önerileri',
                'note' => 'Öneri motoru kurulunca',
            ],
        ];
    }
}
