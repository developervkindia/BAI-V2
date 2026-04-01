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
@endphp

<div class="max-w-4xl mx-auto space-y-10">

    {{-- ========================================================== --}}
    {{-- HEADER                                                       --}}
    {{-- ========================================================== --}}
    <div class="pt-2">
        <h1 class="text-[26px] font-bold text-white/88 leading-tight tracking-tight">
            {{ $greeting }}, {{ $firstName }}
        </h1>
        <p class="text-[14px] text-white/35 mt-1.5">
            {{ $currentOrg->name }} &middot; BAI workspace
        </p>
    </div>

    {{-- ========================================================== --}}
    {{-- PRODUCTS GRID                                                --}}
    {{-- ========================================================== --}}
    <section>
        <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-4">Your Products</h2>

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
                       class="group relative flex flex-col p-5 rounded-2xl border border-white/[0.07] bg-[#17172A] hover:bg-[#1E1E32] hover:border-white/[0.13] transition-all duration-200">
                        {{-- Icon --}}
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $iconBg }} border flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 bg-gradient-to-br {{ $grad }} [-webkit-background-clip:text] [background-clip:text] text-transparent" fill="currentColor" viewBox="0 0 24 24" style="color: transparent; background: linear-gradient(135deg, var(--tw-gradient-stops));">
                                <path d="{{ $def['icon'] ?? '' }}"/>
                            </svg>
                            {{-- Fallback solid icon --}}
                        </div>
                        {{-- Actually use a simpler colored icon --}}
                        <h3 class="text-[14px] font-semibold text-white/82 group-hover:text-white transition-colors leading-tight">{{ $product->name }}</h3>
                        <p class="text-[12px] text-white/35 mt-1 leading-relaxed line-clamp-2">{{ $product->tagline }}</p>

                        {{-- Arrow indicator --}}
                        <div class="absolute top-4 right-4 w-6 h-6 rounded-full bg-white/[0.04] flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <svg class="w-3 h-3 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @else
                    <div class="relative flex flex-col p-5 rounded-2xl border border-white/[0.04] bg-[#111120] opacity-50 cursor-not-allowed select-none">
                        <div class="w-12 h-12 rounded-xl bg-white/[0.04] flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $def['icon'] ?? 'M12 4v16m8-8H4' }}"/>
                            </svg>
                        </div>
                        <h3 class="text-[14px] font-semibold text-white/35 leading-tight">{{ $product->name }}</h3>
                        <p class="text-[12px] text-white/20 mt-1 leading-relaxed line-clamp-2">{{ $product->tagline }}</p>
                        <span class="absolute top-3.5 right-3.5 text-[9px] px-2 py-0.5 rounded-full bg-white/[0.06] text-white/25 font-semibold tracking-wide">SOON</span>
                    </div>
                @endif
            @endforeach
        </div>
    </section>

    {{-- ========================================================== --}}
    {{-- ORGANIZATION SWITCHER (multi-org)                           --}}
    {{-- ========================================================== --}}
    @if($organizations->count() > 1)
        <section>
            <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-4">Organizations</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($organizations as $org)
                    <form method="POST" action="{{ route('organizations.switch', $org) }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl border text-[13px] font-medium transition-all {{ $org->id === $currentOrg->id ? 'border-white/20 bg-white/[0.07] text-white/80' : 'border-white/[0.07] bg-white/[0.03] text-white/45 hover:border-white/15 hover:bg-white/[0.06] hover:text-white/70' }}">
                            <span class="w-6 h-6 rounded-lg bg-indigo-500/15 text-indigo-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($org->name, 0, 1)) }}
                            </span>
                            {{ $org->name }}
                            @if($org->id === $currentOrg->id)
                                <svg class="w-3.5 h-3.5 text-green-400 ml-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @endif
                        </button>
                    </form>
                @endforeach
                <a href="{{ route('organizations.create') }}"
                   class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-dashed border-white/[0.1] text-[13px] font-medium text-white/30 hover:border-white/20 hover:text-white/55 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New
                </a>
            </div>
        </section>
    @endif

    {{-- ========================================================== --}}
    {{-- QUICK STATS                                                 --}}
    {{-- ========================================================== --}}
    @if(!empty($quickStats))
    <section>
        <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-4">Overview</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="p-4 rounded-xl border border-white/[0.06] bg-[#17172A]">
                <p class="text-[22px] font-bold text-indigo-400">{{ $quickStats['boards'] ?? 0 }}</p>
                <p class="text-[11px] text-white/35 mt-1">Active Boards</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.06] bg-[#17172A]">
                <p class="text-[22px] font-bold text-amber-400">{{ $quickStats['active_projects'] ?? 0 }}</p>
                <p class="text-[11px] text-white/35 mt-1">Active Projects</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.06] bg-[#17172A]">
                <p class="text-[22px] font-bold text-teal-400">{{ $quickStats['opp_tasks_due_soon'] ?? 0 }}</p>
                <p class="text-[11px] text-white/35 mt-1">Tasks Due Soon</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.06] bg-[#17172A]">
                <p class="text-[22px] font-bold text-rose-400">{{ $quickStats['employees'] ?? 0 }}</p>
                <p class="text-[11px] text-white/35 mt-1">Employees</p>
            </div>
            <div class="p-4 rounded-xl border border-white/[0.06] bg-[#17172A]">
                <p class="text-[22px] font-bold text-orange-400">{{ $quickStats['pending_leaves'] ?? 0 }}</p>
                <p class="text-[11px] text-white/35 mt-1">Pending Leaves</p>
            </div>
        </div>
    </section>
    @endif

    {{-- ========================================================== --}}
    {{-- QUICK ACTIONS                                               --}}
    {{-- ========================================================== --}}
    <section>
        <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-2">
            @if(in_array('board', $accessibleKeys))
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-indigo-500/20 bg-indigo-500/10 text-[12px] font-medium text-indigo-300 hover:bg-indigo-500/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Board
                </a>
            @endif
            @if(in_array('projects', $accessibleKeys))
                <a href="{{ route('projects.index') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-amber-500/20 bg-amber-500/10 text-[12px] font-medium text-amber-300 hover:bg-amber-500/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Project
                </a>
            @endif
            @if(in_array('hr', $accessibleKeys))
                <a href="{{ route('hr.leave.apply') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-rose-500/20 bg-rose-500/10 text-[12px] font-medium text-rose-300 hover:bg-rose-500/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Apply Leave
                </a>
            @endif
            @if(in_array('opportunity', $accessibleKeys))
                <a href="{{ route('opportunity.my-tasks') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-teal-500/20 bg-teal-500/10 text-[12px] font-medium text-teal-300 hover:bg-teal-500/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    My Tasks
                </a>
            @endif
        </div>
    </section>

    {{-- ========================================================== --}}
    {{-- RECENT ACTIVITY                                             --}}
    {{-- ========================================================== --}}
    @if(!empty($recentActivity))
    <section>
        <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-4">Recent Activity</h2>
        <div class="rounded-xl border border-white/[0.06] bg-[#17172A] divide-y divide-white/[0.04]">
            @foreach($recentActivity as $activity)
                @php
                    $typeColors = [
                        'board' => 'text-indigo-400 bg-indigo-500/15',
                        'projects' => 'text-amber-400 bg-amber-500/15',
                        'hr' => 'text-rose-400 bg-rose-500/15',
                        'opportunity' => 'text-teal-400 bg-teal-500/15',
                    ];
                    $colorClass = $typeColors[$activity['type']] ?? 'text-white/40 bg-white/10';
                @endphp
                <div class="flex items-center gap-3 px-4 py-3">
                    <div class="w-7 h-7 rounded-lg {{ $colorClass }} flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[12px] text-white/65 truncate">{{ $activity['text'] }}</p>
                    </div>
                    <span class="text-[10px] text-white/25 shrink-0">
                        {{ $activity['time'] ? \Carbon\Carbon::parse($activity['time'])->diffForHumans(null, true, true) : '' }}
                    </span>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ========================================================== --}}
    {{-- ORG SETTINGS LINK                                           --}}
    {{-- ========================================================== --}}
    <div class="flex items-center justify-between pt-2 pb-6">
        @if(auth()->user()->is_super_admin)
            <a href="{{ route('super-admin.dashboard') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-500/15 border border-red-500/25 text-[12px] font-semibold text-red-400 hover:bg-red-500/25 hover:text-red-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Platform Admin
            </a>
        @else
            <div></div>
        @endif
        <a href="{{ route('organizations.show', $currentOrg) }}"
           class="flex items-center gap-2 text-[12px] text-white/25 hover:text-white/50 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Organization Settings
        </a>
    </div>

</div>

</x-layouts.hub>
