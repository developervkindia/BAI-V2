<x-layouts.opportunity title="{{ $portfolio->name }}" currentView="portfolios">

<div class="px-6 py-6 max-w-5xl mx-auto">
    <a href="{{ route('opportunity.portfolios.index') }}" class="text-[12px] text-white/30 hover:text-white/55 flex items-center gap-1 mb-4">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Portfolios
    </a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-[20px] font-bold text-white/90">{{ $portfolio->name }}</h1>
    </div>

    {{-- Column headers --}}
    <div class="grid grid-cols-12 px-4 py-2 text-[11px] font-semibold text-white/30 uppercase tracking-wider border-b border-white/[0.06]">
        <div class="col-span-4">Project</div>
        <div class="col-span-2">Status</div>
        <div class="col-span-2">Tasks</div>
        <div class="col-span-2">Progress</div>
        <div class="col-span-2">Owner</div>
    </div>

    @forelse($portfolio->projects as $proj)
    @php $total = $proj->tasks_count ?? 0; $done = $proj->completed_tasks_count ?? 0; $pct = $total > 0 ? round($done/$total*100) : 0; @endphp
    <a href="{{ route('opportunity.projects.show', $proj) }}" class="grid grid-cols-12 items-center px-4 py-3 border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
        <div class="col-span-4 flex items-center gap-2">
            <span class="w-3 h-3 rounded-sm shrink-0" style="background: {{ $proj->color }}"></span>
            <span class="text-[13px] text-white/75 truncate">{{ $proj->name }}</span>
        </div>
        <div class="col-span-2">
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $proj->status === 'on_track' ? 'bg-green-500/15 text-green-400' : ($proj->status === 'at_risk' ? 'bg-amber-500/15 text-amber-400' : 'bg-red-500/15 text-red-400') }}">
                {{ str_replace('_',' ',ucfirst($proj->status)) }}
            </span>
        </div>
        <div class="col-span-2 text-[12px] text-white/40">{{ $done }}/{{ $total }}</div>
        <div class="col-span-2 flex items-center gap-2">
            <div class="flex-1 h-1.5 bg-white/[0.06] rounded-full overflow-hidden"><div class="h-full bg-teal-500 rounded-full" style="width:{{ $pct }}%"></div></div>
            <span class="text-[11px] text-white/30">{{ $pct }}%</span>
        </div>
        <div class="col-span-2 flex items-center gap-1.5">
            <div class="w-5 h-5 rounded-full bg-teal-500/20 text-teal-400 text-[8px] font-bold flex items-center justify-center">{{ strtoupper(substr($proj->owner->name ?? '', 0, 2)) }}</div>
            <span class="text-[11px] text-white/35 truncate">{{ $proj->owner->name ?? '' }}</span>
        </div>
    </a>
    @empty
    <div class="py-12 text-center text-[13px] text-white/25">No projects in this portfolio yet</div>
    @endforelse
</div>

</x-layouts.opportunity>
