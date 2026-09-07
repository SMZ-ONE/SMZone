<x-filament-panels::page>
    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <x-filament::section class="lg:col-span-2">
                <x-slot name="heading">Welcome back to SMZ Natural Cosmetics 🌿</x-slot>
                <x-slot name="description">Organic, natural, bestseller - your Brand Brain is ready</x-slot>
                <div class="flex flex-wrap gap-3 mt-2">
                    <x-filament::button tag="a" href="/admin/social-accounts" color="warning" icon="heroicon-o-at-symbol">Connect Account</x-filament::button>
                    <x-filament::button tag="a" href="/admin/contents/create" color="gray" icon="heroicon-o-calendar-days">New Content</x-filament::button>
                    <x-filament::button tag="a" href="/admin/ai-center" color="gray" icon="heroicon-o-sparkles">Open AI Center</x-filament::button>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">🧠 Brand Brain</x-slot>
                <div class="text-sm space-y-2">
                    <div class="flex justify-between"><span class="text-gray-500">Tone</span><span class="font-semibold">Natural, warm, trustworthy</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Keywords</span><span class="font-semibold">organic, bestseller</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Status</span><x-filament::badge color="success">Active</x-filament::badge></div>
                </div>
            </x-filament::section>
        </div>

        {{-- Stats are rendered automatically by Filament via Widgets --}}

        <x-filament::section>
            <x-slot name="heading">🚀 Quick Start Checklist</x-slot>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="flex gap-3"><span class="h-6 w-6 rounded-full bg-success-500 text-white flex items-center justify-center text-xs">1</span><span>Connect your Instagram or Facebook account in Social Accounts</span></div>
                <div class="flex gap-3"><span class="h-6 w-6 rounded-full bg-warning-500 text-white flex items-center justify-center text-xs">2</span><span>Add at least one Product with image and price</span></div>
                <div class="flex gap-3"><span class="h-6 w-6 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs">3</span><span>Create first post in Content Planner with AI Writer</span></div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
