<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Brand Studio</x-slot>
            <x-slot name="description">Deine Marken-DNA - AI wird jeden Caption in diesem Stil schreiben</x-slot>
            {{ $this->form }}
            <div class="mt-6">
                <x-filament::button wire:click="save" icon="heroicon-o-check" color="success">Save Brand Profile</x-filament::button>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
