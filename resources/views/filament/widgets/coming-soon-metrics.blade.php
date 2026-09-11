<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Yakında</x-slot>
        <x-slot name="description">Bu kartlar gerçek veri bağlanınca dolacak - şimdilik yer tutucu</x-slot>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($this->getCards() as $card)
                <div class="p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 opacity-70">
                    <div class="flex items-center gap-2 mb-1">
                        <x-filament::icon :icon="$card['icon']" class="h-5 w-5 text-gray-400" />
                        <span class="text-sm font-medium text-gray-500">{{ $card['label'] }}</span>
                    </div>
                    <p class="text-xs text-gray-400">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
