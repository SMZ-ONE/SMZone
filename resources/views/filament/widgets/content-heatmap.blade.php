<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">İçerik Yoğunluğu (Son 12 Hafta)</x-slot>

        <div class="flex gap-1 overflow-x-auto pb-2">
            @foreach($this->getWeeks() as $week)
                <div class="flex flex-col gap-1">
                    @foreach($week as $day)
                        <div
                            title="{{ $day['date']->format('d.m.Y') }} - {{ $day['count'] }} içerik"
                            @class([
                                'w-3 h-3 rounded-sm',
                                'bg-gray-100 dark:bg-gray-800' => $day['level'] === 0,
                                'bg-primary-200 dark:bg-primary-900' => $day['level'] === 1,
                                'bg-primary-400 dark:bg-primary-700' => $day['level'] === 2,
                                'bg-primary-500 dark:bg-primary-600' => $day['level'] === 3,
                                'bg-primary-700 dark:bg-primary-400' => $day['level'] === 4,
                            ])
                        ></div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-2 mt-3 text-xs text-gray-500">
            <span>Az</span>
            <div class="w-3 h-3 rounded-sm bg-gray-100 dark:bg-gray-800"></div>
            <div class="w-3 h-3 rounded-sm bg-primary-200 dark:bg-primary-900"></div>
            <div class="w-3 h-3 rounded-sm bg-primary-400 dark:bg-primary-700"></div>
            <div class="w-3 h-3 rounded-sm bg-primary-500 dark:bg-primary-600"></div>
            <div class="w-3 h-3 rounded-sm bg-primary-700 dark:bg-primary-400"></div>
            <span>Çok</span>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
