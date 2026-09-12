<x-filament-panels::page>
    <div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
        <x-filament::section>
            <x-slot name="heading">AI Writer</x-slot>
            <x-slot name="description">Enter Product URL or Name/Description, then Generate</x-slot>
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button wire:click="generate" icon="heroicon-o-sparkles">Generate Caption</x-filament::button>
            </div>
        </x-filament::section>

        @if($generated)
        <x-filament::section>
            <x-slot name="heading">Generated Caption</x-slot>
            <div id="captionDisplay" class="w-full p-6 rounded-xl bg-zinc-900 border border-zinc-700 text-zinc-100 text-[15px] leading-7 whitespace-pre-wrap break-words" style="min-height:200px;">{{ $generated }}</div>
            <textarea id="captionBox" style="position:absolute; left:-9999px;">{{ $generated }}</textarea>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-filament::button color="success" wire:click="saveAsDraft" icon="heroicon-o-calendar">Save to Planner</x-filament::button>
                <x-filament::button color="gray" icon="heroicon-o-clipboard-document" onclick="copyNow()">Copy</x-filament::button>
                <x-filament::button color="gray" icon="heroicon-o-arrow-path" wire:click="generate">Regenerate</x-filament::button>
            </div>
            <p id="copyStatus" class="mt-3 text-sm text-green-400 font-bold" style="display:none;">Copied!</p>
        </x-filament::section>

        @if(!empty($suggestedHashtags))
        <x-filament::section>
            <x-slot name="heading">Suggested Hashtags ({{ count($suggestedHashtags) }})</x-slot>
            <div class="flex flex-wrap gap-2">
                @foreach($suggestedHashtags as $tag)
                    <span class="px-3 py-1 rounded-full bg-zinc-800 text-zinc-100 text-sm border border-zinc-700">{{ $tag }}</span>
                @endforeach
            </div>
            <textarea id="hashtagBox" style="position:absolute; left:-9999px;">{{ implode(' ', $suggestedHashtags) }}</textarea>
            <div class="mt-4 flex flex-wrap gap-2">
                <x-filament::button size="sm" color="gray" icon="heroicon-o-clipboard-document" onclick="copyHashtags()">Copy Hashtags</x-filament::button>
                <x-filament::button
                    size="sm"
                    color="gray"
                    icon="heroicon-o-arrow-path"
                    wire:click="regenerateHashtags"
                    wire:loading.attr="disabled"
                    wire:target="regenerateHashtags"
                >
                    <span wire:loading.remove wire:target="regenerateHashtags">Regenerate Hashtags</span>
                    <span wire:loading wire:target="regenerateHashtags">Regenerating...</span>
                </x-filament::button>
            </div>
            <p id="hashtagCopyStatus" class="mt-3 text-sm text-green-400 font-bold" style="display:none;">Copied!</p>
        </x-filament::section>
        @endif
        <script>
            function copyNow(){ const b=document.getElementById('captionBox'); const d=document.getElementById('captionDisplay'); copyText(b?.value||d?.innerText||'','copyStatus'); }
            function copyHashtags(){ const b=document.getElementById('hashtagBox'); copyText(b?.value||'','hashtagCopyStatus'); }
            function copyText(t,s){ const el=document.getElementById(s); const show=()=>{ if(el){ el.style.display='block'; setTimeout(()=>el.style.display='none',2500);} }; if(navigator.clipboard&&window.isSecureContext){ navigator.clipboard.writeText(t).then(show).catch(()=>fb(t,show)); }else{ fb(t,show);} }
            function fb(t,d){ const ta=document.createElement('textarea'); ta.value=t; ta.style.position='fixed'; ta.style.left='-9999px'; document.body.appendChild(ta); ta.select(); try{ document.execCommand('copy'); d(); }catch(e){} document.body.removeChild(ta); }
        </script>
        @endif
        </div>

        {{-- Canlı Önizleme - format ve yüklenen medyaya göre şekil değiştirir --}}
        <div class="lg:col-span-1">
            <div class="sticky top-6">
                <x-filament::section>
                    <x-slot name="heading">Canlı Önizleme</x-slot>
                    @php
                        $format = $data['format'] ?? 'post';
                        $previewImages = $this->getPreviewImages();
                        $isVertical = in_array($format, ['story', 'reels']);
                    @endphp
                    <div class="max-w-[280px] mx-auto rounded-2xl overflow-hidden border border-zinc-700 bg-black">
                        <div class="flex items-center gap-2 p-2.5">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-pink-500 via-red-500 to-orange-400 flex-shrink-0"></div>
                            <span class="text-xs font-semibold text-white">{{ $brandDisplayName }}</span>
                            <span class="text-[10px] text-zinc-500 ml-auto uppercase">{{ $format }}</span>
                        </div>

                        <div class="{{ $isVertical ? 'aspect-[9/16]' : 'aspect-square' }} bg-zinc-900 flex items-center justify-center overflow-hidden relative">
                            @if(!empty($previewImages))
                                @if($format === 'reels' && is_array($data['media'] ?? null) === false && !empty($data['media']))
                                    <video src="{{ $previewImages[0] }}" class="w-full h-full object-cover" muted></video>
                                @else
                                    <img src="{{ $previewImages[0] }}" class="w-full h-full object-cover" alt="">
                                @endif
                            @else
                                <span class="text-zinc-600 text-xs">Medya yükleyin</span>
                            @endif
                        </div>

                        @if($format === 'carousel' && count($previewImages) > 1)
                            <div class="flex justify-center gap-1 py-2">
                                @foreach($previewImages as $i => $m)
                                    <span class="w-1.5 h-1.5 rounded-full {{ $i === 0 ? 'bg-white' : 'bg-zinc-600' }}"></span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center gap-3 px-2.5 py-2 text-zinc-400">
                            <x-filament::icon icon="heroicon-o-heart" class="h-4 w-4" />
                            <x-filament::icon icon="heroicon-o-chat-bubble-oval-left" class="h-4 w-4" />
                            <x-filament::icon icon="heroicon-o-paper-airplane" class="h-4 w-4" />
                        </div>

                        <div class="px-2.5 pb-3 text-xs text-zinc-200 whitespace-pre-wrap max-h-32 overflow-y-auto">
                            @if($generated)
                                <span class="font-semibold">{{ $brandDisplayName }}</span> {{ \Illuminate\Support\Str::limit($generated, 180) }}
                            @else
                                <span class="text-zinc-600">Caption oluşturunca burada görünecek...</span>
                            @endif
                        </div>
                    </div>
                </x-filament::section>
            </div>
        </div>
    </div>
    </div>
</x-filament-panels::page>
