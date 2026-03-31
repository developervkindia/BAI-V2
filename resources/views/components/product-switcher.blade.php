@props(['products' => collect()])

@php
$productConfig = config('products', []);
// Full class strings to avoid Tailwind v4 purging dynamic values
$colorMap = [
    'indigo'  => ['bg' => 'bg-indigo-500/20',  'text' => 'text-indigo-400',  'grad' => 'from-indigo-500 to-violet-600'],
    'sky'     => ['bg' => 'bg-sky-500/20',     'text' => 'text-sky-400',     'grad' => 'from-sky-400 to-cyan-500'],
    'violet'  => ['bg' => 'bg-violet-500/20',  'text' => 'text-violet-400',  'grad' => 'from-violet-500 to-purple-600'],
    'emerald' => ['bg' => 'bg-emerald-500/20', 'text' => 'text-emerald-400', 'grad' => 'from-emerald-400 to-teal-500'],
    'amber'   => ['bg' => 'bg-amber-500/20',   'text' => 'text-amber-400',   'grad' => 'from-amber-400 to-orange-500'],
];
@endphp

<div x-data="{ open: false }" @click.away="open = false" class="relative">
    {{-- BAI waffle icon --}}
    <button
        @click="open = !open"
        class="p-2 rounded hover:bg-white/5 text-white/40 hover:text-white/70 transition-colors"
        title="BAI apps"
    >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <circle cx="5"  cy="5"  r="1.5"/>
            <circle cx="12" cy="5"  r="1.5"/>
            <circle cx="19" cy="5"  r="1.5"/>
            <circle cx="5"  cy="12" r="1.5"/>
            <circle cx="12" cy="12" r="1.5"/>
            <circle cx="19" cy="12" r="1.5"/>
            <circle cx="5"  cy="19" r="1.5"/>
            <circle cx="12" cy="19" r="1.5"/>
            <circle cx="19" cy="19" r="1.5"/>
        </svg>
    </button>

    {{-- Dropdown panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute left-0 top-full mt-2 w-72 bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl z-50 p-4"
    >
        {{-- BAI brand header --}}
        <div class="flex items-center gap-2 mb-3 px-1">
            <svg width="18" height="18" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="sw-bai-bg" x1="0" y1="0" x2="28" y2="28" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#312E81"/>
                        <stop offset="100%" stop-color="#7C3AED"/>
                    </linearGradient>
                </defs>
                <rect width="28" height="28" rx="7" fill="url(#sw-bai-bg)"/>
                <circle cx="14" cy="7.5" r="2" fill="white"/>
                <circle cx="7.5" cy="20" r="2" fill="white"/>
                <circle cx="20.5" cy="20" r="2" fill="white"/>
                <line x1="14" y1="9.5" x2="8.8" y2="18.5" stroke="white" stroke-opacity="0.65" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="14" y1="9.5" x2="19.2" y2="18.5" stroke="white" stroke-opacity="0.65" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="9.5" y1="20" x2="18.5" y2="20" stroke="white" stroke-opacity="0.65" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/35">BAI Suite</p>
        </div>

        <div class="grid grid-cols-3 gap-2">
            @foreach($productConfig as $key => $def)
                @php
                    $isAccessible = $products->contains('key', $key);
                    $colors       = $colorMap[$def['color']] ?? $colorMap['indigo'];
                @endphp

                @if($def['available'] && $isAccessible && $def['route'])
                    <a href="{{ route($def['route']) }}" @click="open = false"
                       class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-white/5 transition-colors group">
                        <div class="w-10 h-10 rounded-xl {{ $colors['bg'] }} flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $colors['text'] }}" fill="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $def['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-[10px] text-white/60 group-hover:text-white/90 font-medium text-center leading-tight">{{ $def['name'] }}</span>
                    </a>
                @else
                    <div class="flex flex-col items-center gap-1.5 p-3 rounded-xl opacity-40 cursor-default" title="Coming soon">
                        <div class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $def['icon'] }}"/>
                            </svg>
                        </div>
                        <span class="text-[10px] text-white/30 font-medium text-center leading-tight">{{ $def['name'] }}</span>
                        <span class="text-[8px] text-white/20 -mt-0.5">Soon</span>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-3 pt-3 border-t border-white/5">
            <a href="{{ route('hub') }}" @click="open = false"
               class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-white/5 text-xs text-white/40 hover:text-white/70 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                BAI Home
            </a>
        </div>
    </div>
</div>
