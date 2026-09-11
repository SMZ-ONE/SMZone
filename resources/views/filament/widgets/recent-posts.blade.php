<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Son Gönderiler</x-slot>
        <x-slot name="headerEnd">
            <a href="{{ \App\Filament\Resources\Contents\ContentItemResource::getUrl() }}" class="text-sm text-primary-600 hover:underline">
                Tümünü Gör →
            </a>
        </x-slot>

        @forelse($this->getRecentItems() as $item)
            <a href="/admin/contents/content-items/{{ $item->id }}/edit"
               class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-800' : '' }}">
                <div class="flex items-center gap-3 min-w-0">
                    @if($item->product?->image)
                        <img src="{{ $item->product->image }}" class="w-9 h-9 rounded-lg object-cover flex-shrink-0" alt="">
                    @else
                        <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0">
                            <x-filament::icon icon="heroicon-o-photo" class="h-4 w-4 text-gray-400" />
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm font-medium truncate">{{ $item->title ?: 'Untitled' }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($item->platform) }} · {{ $item->created_at->format('d.m.Y') }}</p>
                    </div>
                </div>
                <x-filament::badge :color="match($item->status->value ?? $item->status) {
                    'draft' => 'gray',
                    'scheduled' => 'warning',
                    'published' => 'success',
                    default => 'gray',
                }">
                    {{ ucfirst($item->status->value ?? $item->status) }}
                </x-filament::badge>
            </a>
        @empty
            <p class="text-sm text-gray-500 py-4">Henüz içerik yok.</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
