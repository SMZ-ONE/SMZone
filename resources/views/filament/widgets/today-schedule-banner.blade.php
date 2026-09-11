<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-calendar" class="h-5 w-5 text-gray-500" />
                <span class="font-medium">Bugün {{ $this->getTodayCount() }} zamanlanmış gönderi var</span>
                <span class="text-sm text-gray-500">· Bu hafta {{ $this->getWeekCount() }} gönderi</span>
            </div>
            <a href="{{ \App\Filament\Pages\ContentCalendarPage::getUrl() }}" class="text-sm font-medium text-primary-600 hover:underline">
                Takvime Git →
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
