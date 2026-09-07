<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Shopify Product Sync</x-slot>
            <x-slot name="description">Shopify kataloğundaki ürünleri yerel veritabanına çeker (upsert - tekrar tekrar güvenle çalıştırılabilir)</x-slot>

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Yerel katalogda kayıtlı ürün sayısı</p>
                    <p class="text-2xl font-bold">{{ $this->totalProducts }}</p>
                </div>
                <x-filament::button wire:click="syncNow" icon="heroicon-o-arrow-path" wire:loading.attr="disabled" wire:target="syncNow">
                    <span wire:loading.remove wire:target="syncNow">Sync Now</span>
                    <span wire:loading wire:target="syncNow">Senkronlanıyor...</span>
                </x-filament::button>
            </div>

            @if($lastRunSummary)
                <p class="mt-4 text-sm text-gray-500">Son senkron: {{ $lastRunSummary }}</p>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Otomatik Zamanlama</x-slot>
            <x-slot name="description">CLI komutuna gerek yok - aç/kapat ve sıklığı buradan yönet.</x-slot>

            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button wire:click="saveScheduleSettings" icon="heroicon-o-check" color="success">
                    Kaydet
                </x-filament::button>
            </div>

            @if($lastAutoSyncAt)
                <p class="mt-4 text-sm text-gray-500">Son otomatik çalışma: {{ $lastAutoSyncAt }}</p>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
