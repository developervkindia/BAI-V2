<x-layouts.smartprojects :project="$project" currentView="workload" :canEdit="$canEdit">

@php
    $weekDays = [];
    $d = \Illuminate\Support\Carbon::parse($weekStart);
    while ($d->lte(\Illuminate\Support\Carbon::parse($weekEnd))) {
        $weekDays[] = $d->copy();
        $d->addDay();
    }
@endphp

<div class="px-6 py-5 max-w-screen-xl mx-auto space-y-5">

    {{-- Header with week navigation --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('projects.workload', [$project, 'week_start' => \Illuminate\Support\Carbon::parse($weekStart)->subWeek()->toDateString()]) }}"
           class="p-2 rounded-lg hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h2 class="text-[15px] font-semibold text-white/85">
            Workload — {{ \Illuminate\Support\Carbon::parse($weekStart)->format('M j') }} to {{ \Illuminate\Support\Carbon::parse($weekEnd)->format('M j, Y') }}
        </h2>
        <a href="{{ route('projects.workload', [$project, 'week_start' => \Illuminate\Support\Carbon::parse($weekStart)->addWeek()->toDateString()]) }}"
           class="p-2 rounded-lg hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    {{-- Workload Bars --}}
    <div class="space-y-3">
        @foreach($memberData as $md)
        @php
            $barColor = $md['util_pct'] > 100 ? '#EF4444' : ($md['util_pct'] > 80 ? '#F59E0B' : '#22C55E');
        @endphp
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="flex items-center gap-4 mb-3">
                <div class="w-7 h-7 rounded-full bg-orange-500/20 text-orange-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($md['user']['name'], 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-[13px] font-medium text-white/75">{{ $md['user']['name'] }}</span>
                </div>
                <div class="flex items-center gap-3 text-[11px] shrink-0">
                    <span class="text-white/35">{{ $md['allocated'] }}h alloc.</span>
                    <span class="text-white/45 font-medium">{{ $md['logged'] }}h logged</span>
                    <span class="text-white/35">/ {{ $md['capacity'] }}h cap.</span>
                    <span class="font-bold" style="color: {{ $barColor }}">{{ $md['util_pct'] }}%</span>
                </div>
            </div>

            {{-- Capacity vs Logged bar --}}
            <div class="relative h-2 bg-white/[0.06] rounded-full overflow-hidden mb-3">
                <div class="absolute inset-y-0 left-0 rounded-full transition-all" style="width: {{ min($md['util_pct'], 100) }}%; background: {{ $barColor }}"></div>
            </div>

            {{-- Daily heatmap --}}
            <div class="flex gap-1">
                @foreach($weekDays as $day)
                    @php
                        $dayStr = $day->format('Y-m-d');
                        $hrs = $md['daily'][$dayStr] ?? 0;
                        $intensity = $hrs <= 0 ? 'bg-white/[0.03]' :
                            ($hrs <= 2 ? 'bg-green-500/15' :
                            ($hrs <= 4 ? 'bg-green-500/25' :
                            ($hrs <= 6 ? 'bg-amber-500/20' :
                            ($hrs <= 8 ? 'bg-amber-500/35' : 'bg-red-500/30'))));
                    @endphp
                    <div class="flex-1 text-center">
                        <div class="rounded-lg py-2 {{ $intensity }}">
                            <div class="text-[11px] font-medium {{ $hrs > 0 ? 'text-white/60' : 'text-white/15' }}">
                                {{ $hrs > 0 ? $hrs . 'h' : '—' }}
                            </div>
                        </div>
                        <div class="text-[9px] text-white/20 mt-1">{{ $day->format('D') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

</x-layouts.smartprojects>
