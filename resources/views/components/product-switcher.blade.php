{{-- Product Switcher — Atlassian-style app grid --}}
@php
    $products = config('products', []);
    $accessibleKeys = ($accessibleProducts ?? collect())->pluck('key')->toArray();
    $currentProduct = $currentProduct ?? '';
@endphp

<div x-data="{ switcherOpen: false }" @click.away="switcherOpen = false" class="relative">
    <button @click="switcherOpen = !switcherOpen"
            class="flex items-center gap-2 px-2.5 py-2 rounded-lg text-[12px] font-medium text-white/38 hover:text-white/65 hover:bg-white/[0.04] transition-colors w-full"
            title="Switch product">
        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
            <rect x="3"  y="3"  width="7" height="7" rx="1.5"/>
            <rect x="14" y="3"  width="7" height="7" rx="1.5"/>
            <rect x="3"  y="14" width="7" height="7" rx="1.5"/>
            <rect x="14" y="14" width="7" height="7" rx="1.5"/>
        </svg>
        <span>Switch Product</span>
    </button>

    <div x-show="switcherOpen" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute bottom-full left-0 mb-2 w-64 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl z-[60] overflow-hidden">

        <div class="px-3.5 py-2.5 border-b border-white/[0.06]">
            <p class="text-[11px] font-semibold text-white/40 uppercase tracking-wider">Products</p>
        </div>

        <div class="p-2 space-y-0.5 max-h-72 overflow-y-auto scrollbar-thin">
            {{-- Hub --}}
            <a href="{{ route('hub') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors hover:bg-white/[0.06]">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-600 to-gray-800 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="5" cy="5" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="19" cy="5" r="1.5"/>
                        <circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>
                        <circle cx="5" cy="19" r="1.5"/><circle cx="12" cy="19" r="1.5"/><circle cx="19" cy="19" r="1.5"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] font-semibold text-white/75">BAI Hub</p>
                    <p class="text-[10px] text-white/35">Home</p>
                </div>
            </a>

            @foreach($products as $key => $product)
                @php
                    $hasAccess = in_array($key, $accessibleKeys);
                    $isAvailable = $product['available'] ?? false;
                    $isCurrent = ($key === $currentProduct);
                    $route = ($product['route'] ?? null) && $hasAccess && $isAvailable ? route($product['route']) : null;
                    $colorMap = [
                        'indigo' => 'from-indigo-600 to-indigo-800',
                        'amber'  => 'from-amber-500 to-amber-700',
                        'teal'   => 'from-teal-500 to-teal-700',
                        'rose'   => 'from-rose-500 to-rose-700',
                        'sky'    => 'from-sky-500 to-sky-700',
                        'violet' => 'from-violet-500 to-violet-700',
                        'emerald'=> 'from-emerald-500 to-emerald-700',
                    ];
                    $gradient = $colorMap[$product['color'] ?? 'indigo'] ?? 'from-gray-600 to-gray-800';
                @endphp

                @if($route && !$isCurrent)
                    <a href="{{ $route }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors hover:bg-white/[0.06]">
                @elseif($isCurrent)
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-white/[0.08]">
                @else
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg opacity-35 cursor-not-allowed">
                @endif

                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br {{ $gradient }} flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $product['icon'] ?? 'M13 2L4.09 12.97H11L10 22l8.91-10.97H13L14 2z' }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5">
                            <p class="text-[12px] font-semibold {{ $isCurrent ? 'text-white/90' : 'text-white/70' }} truncate">{{ $product['name'] }}</p>
                            @if($isCurrent)
                                <span class="text-[8px] font-bold uppercase tracking-wider text-indigo-400 bg-indigo-500/10 px-1.5 py-0.5 rounded">Current</span>
                            @endif
                            @if(!$isAvailable)
                                <span class="text-[8px] font-bold uppercase tracking-wider text-white/25 bg-white/5 px-1.5 py-0.5 rounded">Soon</span>
                            @endif
                        </div>
                        <p class="text-[10px] text-white/35 truncate">{{ $product['tagline'] ?? '' }}</p>
                    </div>

                @if($route && !$isCurrent)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
