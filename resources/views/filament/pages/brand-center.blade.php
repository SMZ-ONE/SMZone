<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Brand Brain Active 🧠</x-slot>
        <x-slot name="description">AI powered studio for byCOSMETIQ</x-slot>
        <p class="text-sm text-gray-500">Marka DNA'nı tanımla, AI her caption'ı bu tonda üretsin. Hepsi markanın tonunda.</p>
    </x-filament::section>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <x-filament::section>
            <x-slot name="heading">🎨 Brand Studio</x-slot>
            <x-slot name="description">Marka DNA</x-slot>
            <p class="text-sm">Ton, dil, yasaklı kelimeler, tercih edilen hashtag'ler - AI Writer'ın her caption'da uyacağı kurallar burada.</p>
            <x-filament::badge color="success" class="mt-3">Ready</x-filament::badge>
            <x-filament::button class="mt-4 w-full" tag="a" :href="\App\Filament\Pages\BrandStudio::getUrl()">Brand Studio'ya Git →</x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">🖼️ AI Image</x-slot>
            <x-slot name="description">Background & Lifestyle</x-slot>
            <p class="text-sm">Ürün fotoğrafından arka plan temizleme ve lifestyle mockup üretimi.</p>
            <x-filament::badge color="warning" class="mt-3">Phase 3</x-filament::badge>
            <x-filament::button class="mt-4 w-full" disabled>Coming Soon</x-filament::button>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">💬 CRM AI</x-slot>
            <x-slot name="description">Smart Reply</x-slot>
            <p class="text-sm">Yorumlara ve DM'lere marka tonunda otomatik cevap önerileri.</p>
            <x-filament::badge color="warning" class="mt-3">Phase 3</x-filament::badge>
            <x-filament::button class="mt-4 w-full" disabled>Coming Soon</x-filament::button>
        </x-filament::section>
    </div>
</x-filament-panels::page>
