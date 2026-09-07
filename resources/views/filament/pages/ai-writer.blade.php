<x-filament-panels::page>
    <div class="space-y-6">
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
</x-filament-panels::page>
