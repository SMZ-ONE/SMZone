<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Guvenlik Bilgisi</x-slot>
            <div class="text-sm leading-6 space-y-2">
                <p>• Keyler <b>veritabaninda sifreli</b> (Laravel encrypted cast - APP_KEY ile AES-256)</p>
                <p>• <b>.env veya GitHub'a asla gitmez</b></p>
                <p>• Sadece sen (Super Admin) gorebilirsin</p>
                <p>• Oneri: Google Cloud Console > API Key > IP kisitlamasi ekle</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">API Keys</x-slot>
            {{ $this->form }}
            <div class="mt-6">
                <x-filament::button wire:click="save" icon="heroicon-o-lock-closed">
                    Guvenli Kaydet
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
