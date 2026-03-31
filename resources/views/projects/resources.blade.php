<x-layouts.smartprojects :project="$project" currentView="resources" :canEdit="$canEdit">

<div class="px-6 py-5 max-w-screen-xl mx-auto space-y-4">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-[15px] font-semibold text-white/85">Resource Allocation</h2>
        <span class="text-[11px] text-white/30">{{ $unassigned }} unassigned tasks</span>
    </div>

    @foreach($memberData as $md)
    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl overflow-hidden">
        {{-- Member header --}}
        <div class="flex items-center gap-3 px-5 py-3 border-b border-white/[0.05]">
            <div class="w-8 h-8 rounded-full bg-orange-500/20 text-orange-300 text-[11px] font-bold flex items-center justify-center shrink-0">
                {{ strtoupper(substr($md['user']['name'], 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-[13px] font-medium text-white/80">{{ $md['user']['name'] }}</div>
                <div class="text-[10px] text-white/30">{{ ucfirst($md['role']) }}</div>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-white/40 shrink-0">
                <span>{{ $md['tasks']->count() }} tasks</span>
                <span>{{ $md['total_estimated'] }}h est.</span>
                <span>{{ $md['total_logged'] }}h logged</span>
            </div>

            {{-- Capacity bar --}}
            <div class="w-32 shrink-0">
                @php
                    $capPct = $md['capacity'] > 0 ? min(round($md['total_estimated'] / $md['capacity'] * 100), 120) : 0;
                    $barColor = $capPct > 100 ? '#EF4444' : ($capPct > 80 ? '#F59E0B' : '#22C55E');
                @endphp
                <div class="h-1.5 bg-white/[0.08] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all" style="width: {{ min($capPct, 100) }}%; background: {{ $barColor }}"></div>
                </div>
                <div class="text-[9px] text-white/25 mt-0.5">{{ $capPct }}% of {{ $md['capacity'] }}h/wk</div>
            </div>
        </div>

        {{-- Task cards --}}
        @if($md['tasks']->count() > 0)
        <div class="p-3 flex flex-wrap gap-2">
            @foreach($md['tasks'] as $task)
            <div class="bg-white/[0.03] border border-white/[0.06] rounded-xl px-3 py-2 min-w-[200px] max-w-[280px]">
                <div class="text-[12px] text-white/65 truncate">{{ $task['title'] }}</div>
                <div class="flex items-center gap-2 mt-1">
                    @if($task['priority'] && $task['priority'] !== 'none')
                    <span class="text-[9px] px-1.5 py-0.5 rounded-full
                        {{ $task['priority'] === 'critical' ? 'bg-red-500/15 text-red-400' :
                           ($task['priority'] === 'high' ? 'bg-orange-500/15 text-orange-400' :
                           ($task['priority'] === 'medium' ? 'bg-amber-500/15 text-amber-400' : 'bg-blue-500/15 text-blue-400')) }}">
                        {{ ucfirst($task['priority']) }}
                    </span>
                    @endif
                    @if($task['estimated_hours'])
                    <span class="text-[9px] text-white/25">{{ $task['estimated_hours'] }}h</span>
                    @endif
                    @if($task['due_date'])
                    <span class="text-[9px] text-white/25">{{ $task['due_date'] }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="px-5 py-4 text-[11px] text-white/20">No open tasks assigned</div>
        @endif
    </div>
    @endforeach
</div>

</x-layouts.smartprojects>
