<x-filament-panels::page>
    <div class="space-y-4">
        <x-filament::section>
            <div class="flex flex-wrap items-center gap-6 text-sm">
                <div class="flex items-center gap-3">
                    <span class="font-medium text-gray-500">Durum:</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full" style="background:#9ca3af"></span> Taslak</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full" style="background:#f59e0b"></span> Planlandı</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full" style="background:#22c55e"></span> Yayınlandı</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-500">Gün yoğunluğu:</span>
                    <span class="text-xs text-gray-400">Az</span>
                    <span class="w-3 h-3 rounded-sm" style="background: rgba(59,130,246,0.15)"></span>
                    <span class="w-3 h-3 rounded-sm" style="background: rgba(59,130,246,0.30)"></span>
                    <span class="w-3 h-3 rounded-sm" style="background: rgba(59,130,246,0.45)"></span>
                    <span class="w-3 h-3 rounded-sm" style="background: rgba(59,130,246,0.60)"></span>
                    <span class="text-xs text-gray-400">Çok</span>
                </div>
            </div>
        </x-filament::section>

        @livewire(\App\Filament\Widgets\ContentCalendarWidget::class)
    </div>
</x-filament-panels::page>
