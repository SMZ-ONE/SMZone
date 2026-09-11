<x-filament-panels::page>
    <div class="space-y-6">

        <x-filament::section>
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-gray-400 flex-shrink-0" />
                <p class="text-sm text-gray-500">
                    Bu sayfadaki tüm modüller Instagram/Facebook API bağlantısı kurulunca gerçek veriyle dolacak. Şu an sadece yerleşim - hiçbir sayı gerçek değil.
                </p>
            </div>
        </x-filament::section>

        {{-- Üst filtre çubuğu - şimdilik görsel, API bağlanınca fonksiyonel olacak --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex gap-2">
                <x-filament::badge color="primary">Tümü</x-filament::badge>
                <x-filament::badge color="gray">Instagram</x-filament::badge>
                <x-filament::badge color="gray">Facebook</x-filament::badge>
            </div>
            <div class="flex gap-2">
                <x-filament::badge color="gray">24s</x-filament::badge>
                <x-filament::badge color="gray">7g</x-filament::badge>
                <x-filament::badge color="primary">30g</x-filament::badge>
                <x-filament::badge color="gray">90g</x-filament::badge>
            </div>
        </div>

        {{-- 5 özet kart --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach([
                ['icon' => 'heroicon-o-users', 'label' => 'Toplam Takipçi'],
                ['icon' => 'heroicon-o-eye', 'label' => 'Erişim'],
                ['icon' => 'heroicon-o-heart', 'label' => 'Etkileşim Oranı'],
                ['icon' => 'heroicon-o-bookmark', 'label' => 'Kaydetme Oranı'],
                ['icon' => 'heroicon-o-cursor-arrow-rays', 'label' => 'Tıklama Oranı'],
            ] as $card)
                <div class="p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 opacity-70">
                    <x-filament::icon :icon="$card['icon']" class="h-5 w-5 text-gray-400 mb-2" />
                    <p class="text-lg font-bold text-gray-400">—</p>
                    <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Takipçi artışı + Etkileşim skoru --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2">
                <x-filament::section>
                    <x-slot name="heading">Takipçi Artışı</x-slot>
                    <x-slot name="description">Platform bazında kümülatif takipçi gelişimi</x-slot>
                    <div class="h-64 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-sm text-gray-400">API bağlanınca grafik burada</p>
                    </div>
                </x-filament::section>
            </div>
            <x-filament::section>
                <x-slot name="heading">Etkileşim Skoru</x-slot>
                <div class="h-64 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                    <p class="text-sm text-gray-400">Yakında</p>
                </div>
            </x-filament::section>
        </div>

        {{-- Kitle içgörüleri --}}
        <div>
            <h3 class="text-base font-semibold mb-3">Kitle İçgörüleri</h3>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <x-filament::section>
                    <x-slot name="heading">Yaş Dağılımı</x-slot>
                    <div class="h-40 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-sm text-gray-400">Yakında</p>
                    </div>
                </x-filament::section>
                <x-filament::section>
                    <x-slot name="heading">Cinsiyet Kırılımı</x-slot>
                    <div class="h-40 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-sm text-gray-400">Yakında</p>
                    </div>
                </x-filament::section>
                <x-filament::section>
                    <x-slot name="heading">Öne Çıkan Konumlar</x-slot>
                    <div class="h-40 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-sm text-gray-400">Yakında</p>
                    </div>
                </x-filament::section>
            </div>
        </div>

        {{-- En iyi saat dilimleri --}}
        <x-filament::section>
            <x-slot name="heading">En Yüksek Etkileşimli Saat Dilimleri</x-slot>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach(range(1, 6) as $i)
                    <div class="p-3 rounded-lg border border-dashed border-gray-200 dark:border-gray-700 opacity-70 text-center">
                        <p class="text-xs text-gray-400">#{{ $i }}</p>
                        <p class="text-sm font-medium text-gray-400">—</p>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        {{-- İçerik performansı --}}
        <div>
            <h3 class="text-base font-semibold mb-3">İçerik Performansı</h3>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2">
                    <x-filament::section>
                        <x-slot name="heading">Etkileşim Kırılımı</x-slot>
                        <div class="h-56 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-sm text-gray-400">Yakında</p>
                        </div>
                    </x-filament::section>
                </div>
                <x-filament::section>
                    <x-slot name="heading">Sektör Kıyaslaması</x-slot>
                    <div class="h-56 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-sm text-gray-400">Yakında</p>
                    </div>
                </x-filament::section>
            </div>
        </div>

        {{-- Format bazında karşılaştırma --}}
        <x-filament::section>
            <x-slot name="heading">Format Bazında Karşılaştırma</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2">Format</th>
                            <th>Gönderi</th>
                            <th>Erişim</th>
                            <th>Etkileşim Oranı</th>
                            <th>Kaydetme</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['Reels / Kısa Video', 'Karusel', 'Tek Görsel', 'Hikaye', 'Metin / Bağlantı'] as $format)
                            <tr class="border-b border-gray-100 dark:border-gray-800 text-gray-400">
                                <td class="py-2">{{ $format }}</td>
                                <td>—</td>
                                <td>—</td>
                                <td>—</td>
                                <td>—</td>
                                <td>—</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- YZ tahmini + En iyi gönderiler --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-filament::section>
                <x-slot name="heading">YZ Tahmini</x-slot>
                <div class="h-40 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                    <p class="text-sm text-gray-400">Öneri motoru kurulunca</p>
                </div>
            </x-filament::section>
            <x-filament::section>
                <x-slot name="heading">En İyi Gönderiler</x-slot>
                <div class="h-40 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                    <p class="text-sm text-gray-400">Yakında</p>
                </div>
            </x-filament::section>
        </div>

        {{-- Dönüşüm hunisi --}}
        <x-filament::section>
            <x-slot name="heading">Dönüşüm Hunisi</x-slot>
            <x-slot name="description">Bu modül ayrıca Shopify sipariş verisiyle eşleştirme gerektiriyor - en geniş kapsamlı entegrasyon</x-slot>
            <div class="h-40 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                <p class="text-sm text-gray-400">Yakında</p>
            </div>
        </x-filament::section>

        {{-- Öneriler --}}
        <x-filament::section>
            <x-slot name="heading">Öneriler ve Aksiyon Planı</x-slot>
            <div class="h-32 flex items-center justify-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                <p class="text-sm text-gray-400">Öneri motoru kurulunca</p>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
