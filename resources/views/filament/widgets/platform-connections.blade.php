<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Platform Bağlantıları</x-slot>

        <div class="space-y-3">
            @foreach($this->getPlatforms() as $platform)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span @class([
                            'w-2 h-2 rounded-full',
                            'bg-success-500' => $platform['connected'],
                            'bg-gray-300 dark:bg-gray-600' => !$platform['connected'],
                        ])></span>
                        <span class="text-sm font-medium">{{ $platform['label'] }}</span>
                        @if($platform['username'])
                            <span class="text-xs text-gray-500">{{ '@'.$platform['username'] }}</span>
                        @endif
                    </div>

                    @if($platform['connected'])
                        <span class="text-xs text-gray-500">
                            {{ $platform['last_synced_at']?->diffForHumans() ?? 'Bağlı' }}
                        </span>
                    @else
                        <span class="text-xs text-gray-400">Bağlı değil</span>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
