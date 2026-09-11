<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Hızlı İşlemler</x-slot>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <a href="{{ \App\Filament\Pages\AiWriter::getUrl() }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 transition">
                <x-filament::icon icon="heroicon-o-sparkles" class="h-6 w-6 text-primary-500" />
                <span class="font-medium">AI ile Caption Oluştur</span>
            </a>

            <a href="{{ \App\Filament\Pages\ProductSync::getUrl() }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 transition">
                <x-filament::icon icon="heroicon-o-arrow-path" class="h-6 w-6 text-primary-500" />
                <span class="font-medium">Ürünleri Senkronla</span>
            </a>

            <a href="{{ \App\Filament\Pages\ContentCalendarPage::getUrl() }}"
               class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-primary-500 transition">
                <x-filament::icon icon="heroicon-o-calendar-days" class="h-6 w-6 text-primary-500" />
                <span class="font-medium">Takvimi Aç</span>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
