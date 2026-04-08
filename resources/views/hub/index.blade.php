<x-layouts.hub>

@php
$hour = (int) date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$firstName = explode(' ', auth()->user()->name ?? 'there')[0];

$gradients = [
    'indigo'  => 'from-indigo-500 to-violet-600',
    'sky'     => 'from-sky-400 to-cyan-500',
    'violet'  => 'from-violet-500 to-purple-600',
    'emerald' => 'from-emerald-400 to-teal-500',
    'amber'   => 'from-amber-400 to-orange-500',
    'rose'    => 'from-rose-400 to-pink-500',
    'teal'    => 'from-teal-400 to-cyan-500',
];
$iconBgClass = [
    'indigo'  => 'from-indigo-500/20 to-violet-600/20 border-indigo-500/20',
    'sky'     => 'from-sky-500/20 to-cyan-500/20 border-sky-500/20',
    'violet'  => 'from-violet-500/20 to-purple-500/20 border-violet-500/20',
    'emerald' => 'from-emerald-500/20 to-teal-500/20 border-emerald-500/20',
    'amber'   => 'from-amber-500/20 to-orange-500/20 border-amber-500/20',
    'rose'    => 'from-rose-500/20 to-pink-500/20 border-rose-500/20',
    'teal'    => 'from-teal-500/20 to-cyan-500/20 border-teal-500/20',
];

$statColorMap = [
    'indigo' => 'text-indigo-400',
    'amber'  => 'text-amber-400',
    'teal'   => 'text-teal-400',
    'rose'   => 'text-rose-400',
    'orange' => 'text-orange-400',
];
@endphp

<div class="max-w-4xl mx-auto space-y-8">

    {{-- GREETING --}}
    <div class="pt-1">
        <h1 class="text-[24px] font-bold text-white/90 leading-tight tracking-tight">
            {{ $greeting }}, {{ $firstName }}
        </h1>
        <p class="text-[13px] text-white/35 mt-1">
            {{ $currentOrg?->name ?? 'No organization' }} &middot; BAI workspace
        </p>
    </div>

    @if($currentOrg && $currentOrg->isAdmin(auth()->user()))
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-white/[0.07] bg-white/[0.02] px-4 py-3.5">
        <p class="text-[12px] text-white/40 leading-relaxed">
            Manage this organization’s settings, members, roles, and billing in one place — separate from any product.
        </p>
        <a href="{{ route('organizations.manage', $currentOrg) }}"
           class="shrink-0 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-[12px] font-medium bg-violet-500/15 border border-violet-500/25 text-violet-300 hover:bg-violet-500/25 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            Organization management
        </a>
    </div>
    @endif

    {{-- QUICK STATS --}}
    @if(!empty($quickStats))
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
        @php
            $statItems = [
                ['key' => 'boards',           'label' => 'Active Boards',    'color' => 'indigo'],
                ['key' => 'active_projects',  'label' => 'Active Projects',  'color' => 'amber'],
                ['key' => 'opp_tasks_due_soon','label' => 'Tasks Due Soon',  'color' => 'teal'],
                ['key' => 'employees',        'label' => 'Employees',        'color' => 'rose'],
                ['key' => 'pending_leaves',   'label' => 'Pending Leaves',   'color' => 'orange'],
            ];
        @endphp
        @foreach($statItems as $stat)
            <div class="p-3.5 rounded-xl border border-white/[0.06] bg-[#14142A]">
                <p class="text-[20px] font-bold {{ $statColorMap[$stat['color']] }}">{{ $quickStats[$stat['key']] ?? 0 }}</p>
                <p class="text-[10px] text-white/30 mt-0.5">{{ $stat['label'] }}</p>
            </div>
        @endforeach
    </div>
    @endif

    {{-- PRODUCTS GRID --}}
    <section>
        <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-3">Your Products</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach($allProducts as $product)
                @php
                    $def    = $productConfig[$product->key] ?? [];
                    $active = in_array($product->key, $accessibleKeys) && $product->is_available;
                    $color  = $def['color'] ?? 'indigo';
                    $grad   = $gradients[$color] ?? 'from-indigo-500 to-violet-600';
                    $iconBg = $iconBgClass[$color] ?? 'from-indigo-500/20 to-violet-600/20 border-indigo-500/20';
                @endphp

                @if($active && !empty($def['route']))
                    <a href="{{ route($def['route']) }}"
                       class="group relative flex flex-col p-5 rounded-2xl border border-white/[0.07] bg-[#14142A] hover:bg-[#1C1C34] hover:border-white/[0.13] transition-all duration-200">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $iconBg }} border flex items-center justify-center mb-3.5">
                            <svg class="w-5.5 h-5.5 bg-gradient-to-br {{ $grad }} [-webkit-background-clip:text] [background-clip:text] text-transparent" fill="currentColor" viewBox="0 0 24 24" style="color: transparent; background: linear-gradient(135deg, var(--tw-gradient-stops));">
                                <path d="{{ $def['icon'] ?? '' }}"/>
                            </svg>
                        </div>
                        <h3 class="text-[13px] font-semibold text-white/85 group-hover:text-white transition-colors leading-tight">{{ $product->name }}</h3>
                        <p class="text-[11px] text-white/30 mt-1 leading-relaxed line-clamp-2">{{ $product->tagline }}</p>
                        <div class="absolute top-4 right-4 w-5 h-5 rounded-full bg-white/[0.04] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-2.5 h-2.5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @else
                    <div class="relative flex flex-col p-5 rounded-2xl border border-white/[0.04] bg-[#111120] opacity-45 cursor-not-allowed select-none">
                        <div class="w-11 h-11 rounded-xl bg-white/[0.04] flex items-center justify-center mb-3.5">
                            <svg class="w-5.5 h-5.5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $def['icon'] ?? 'M12 4v16m8-8H4' }}"/>
                            </svg>
                        </div>
                        <h3 class="text-[13px] font-semibold text-white/30 leading-tight">{{ $product->name }}</h3>
                        <p class="text-[11px] text-white/18 mt-1 leading-relaxed line-clamp-2">{{ $product->tagline }}</p>
                        <span class="absolute top-3 right-3 text-[8px] px-1.5 py-0.5 rounded-full bg-white/[0.06] text-white/25 font-semibold tracking-wide">SOON</span>
                    </div>
                @endif
            @endforeach
        </div>
    </section>

    {{-- RECENT ACTIVITY --}}
    @if(!empty($recentActivity))
    <section>
        <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-3">Recent Activity</h2>
        <div class="rounded-xl border border-white/[0.06] bg-[#14142A] divide-y divide-white/[0.04]">
            @foreach($recentActivity as $activity)
                @php
                    $typeColors = [
                        'board'       => 'text-indigo-400 bg-indigo-500/15',
                        'projects'    => 'text-amber-400 bg-amber-500/15',
                        'hr'          => 'text-rose-400 bg-rose-500/15',
                        'opportunity' => 'text-teal-400 bg-teal-500/15',
                    ];
                    $colorClass = $typeColors[$activity['type']] ?? 'text-white/40 bg-white/10';
                @endphp
                <div class="flex items-center gap-3 px-4 py-3">
                    <div class="w-6 h-6 rounded-lg {{ $colorClass }} flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] text-white/60 truncate">{{ $activity['text'] }}</p>
                    </div>
                    <span class="text-[10px] text-white/20 shrink-0">
                        {{ $activity['time'] ? \Carbon\Carbon::parse($activity['time'])->diffForHumans(null, true, true) : '' }}
                    </span>
                </div>
            @endforeach
        </div>
    </section>
    @endif

</div>

</x-layouts.hub>
