<x-layouts.smartprojects :project="$project" currentView="budget" :canEdit="$canEdit">

@php
    $maxWeeklyHours = $weeklySpend->max('total_hours') ?: 1;
@endphp

<div class="px-6 py-5 max-w-screen-xl mx-auto space-y-5">

    {{-- ================================================================ --}}
    {{-- STAT CARDS                                                        --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] font-medium text-white/35 mb-1.5">Total Budget</div>
            <div class="text-[28px] font-bold text-white/82 leading-none">${{ number_format($budget, 0) }}</div>
            <div class="text-[11px] text-white/25 mt-1">${{ number_format($rate, 0) }}/hr rate</div>
        </div>

        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] font-medium text-white/35 mb-1.5">Actual Spend</div>
            <div class="text-[28px] font-bold text-orange-400 leading-none">${{ number_format($actualSpend, 0) }}</div>
            <div class="text-[11px] text-white/25 mt-1">{{ $budgetUsedPct }}% of budget</div>
        </div>

        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] font-medium text-white/35 mb-1.5">Remaining</div>
            <div class="text-[28px] font-bold leading-none {{ $remaining >= 0 ? 'text-green-400' : 'text-red-400' }}">
                ${{ number_format(abs($remaining), 0) }}{{ $remaining < 0 ? ' over' : '' }}
            </div>
            <div class="text-[11px] text-white/25 mt-1">{{ round($totalLoggedHours, 1) }}h logged</div>
        </div>

        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] font-medium text-white/35 mb-1.5">Est. at Completion</div>
            <div class="text-[28px] font-bold leading-none {{ $estimatedTotalCost <= $budget ? 'text-white/82' : 'text-red-400' }}">
                ${{ number_format($estimatedTotalCost, 0) }}
            </div>
            <div class="text-[11px] text-white/25 mt-1">
                {{ round($remainingEstimated, 0) }}h remaining work
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- BUDGET PROGRESS BAR                                               --}}
    {{-- ================================================================ --}}
    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-[13px] font-semibold text-white/65">Budget Usage</h3>
            <span class="text-[12px] text-white/40">{{ $budgetUsedPct }}%</span>
        </div>
        <div class="h-3 bg-white/[0.08] rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500"
                 style="width: {{ min($budgetUsedPct, 100) }}%; background: {{ $budgetUsedPct > 90 ? '#EF4444' : ($budgetUsedPct > 70 ? '#F59E0B' : '#F97316') }};">
            </div>
        </div>
        <div class="flex justify-between mt-2 text-[10px] text-white/25">
            <span>$0</span>
            <span>${{ number_format($budget, 0) }}</span>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- FORECAST                                                          --}}
    {{-- ================================================================ --}}
    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
        <h3 class="text-[13px] font-semibold text-white/65 mb-4">Forecast</h3>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <div class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Burn Rate</div>
                <div class="text-[16px] font-bold text-white/70">{{ round($weeklyBurnRate, 1) }}h/wk</div>
                <div class="text-[10px] text-white/25">${{ number_format($weeklyBurnCost, 0) }}/wk</div>
            </div>
            <div>
                <div class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Budget Runway</div>
                <div class="text-[16px] font-bold {{ ($weeksUntilBudgetExhausted ?? 0) < ($estimatedCompletionWeeks ?? 0) ? 'text-red-400' : 'text-white/70' }}">
                    {{ $weeksUntilBudgetExhausted !== null ? $weeksUntilBudgetExhausted . ' weeks' : '∞' }}
                </div>
                <div class="text-[10px] text-white/25">at current rate</div>
            </div>
            <div>
                <div class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Est. Completion</div>
                <div class="text-[16px] font-bold text-white/70">
                    {{ $estimatedCompletionWeeks !== null ? $estimatedCompletionWeeks . ' weeks' : '—' }}
                </div>
                <div class="text-[10px] text-white/25">based on remaining work</div>
            </div>
            <div>
                <div class="text-[10px] text-white/30 uppercase tracking-wider mb-1">Variance</div>
                @php $variance = $budget - $estimatedTotalCost; @endphp
                <div class="text-[16px] font-bold {{ $variance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $variance >= 0 ? '+' : '-' }}${{ number_format(abs($variance), 0) }}
                </div>
                <div class="text-[10px] text-white/25">{{ $variance >= 0 ? 'under' : 'over' }} budget</div>
            </div>
        </div>

        @if($weeksUntilBudgetExhausted !== null && $estimatedCompletionWeeks !== null && $weeksUntilBudgetExhausted < $estimatedCompletionWeeks)
            <div class="mt-4 px-4 py-2.5 rounded-xl bg-red-500/10 border border-red-500/20 text-[11px] text-red-400">
                Budget will run out {{ round($estimatedCompletionWeeks - $weeksUntilBudgetExhausted, 1) }} weeks before estimated completion.
            </div>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- WEEKLY BURN CHART                                                 --}}
    {{-- ================================================================ --}}
    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
        <h3 class="text-[13px] font-semibold text-white/65 mb-4">Weekly Burn (Last 8 Weeks)</h3>
        @if($weeklySpend->count() > 0)
            <div class="flex items-end gap-2 h-32">
                @foreach($weeklySpend as $ws)
                    @php $pct = ($ws->total_hours / $maxWeeklyHours) * 100; @endphp
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <span class="text-[9px] text-white/30">{{ round($ws->total_hours, 1) }}h</span>
                        <div class="w-full rounded-t-lg bg-orange-500/40 transition-all" style="height: {{ $pct }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-2 mt-1.5">
                @foreach($weeklySpend as $ws)
                    <div class="flex-1 text-center text-[8px] text-white/20">W{{ substr($ws->week_key, -2) }}</div>
                @endforeach
            </div>
        @else
            <p class="text-[12px] text-white/25 text-center py-6">No time logged in the last 8 weeks</p>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- HOURS BREAKDOWN                                                   --}}
    {{-- ================================================================ --}}
    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
        <h3 class="text-[13px] font-semibold text-white/65 mb-4">Hours Summary</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center py-3">
                <div class="text-[24px] font-bold text-white/70">{{ round($totalLoggedHours, 1) }}</div>
                <div class="text-[10px] text-white/30 uppercase mt-1">Total Logged</div>
            </div>
            <div class="text-center py-3">
                <div class="text-[24px] font-bold text-green-400">{{ round($billableHours, 1) }}</div>
                <div class="text-[10px] text-white/30 uppercase mt-1">Billable</div>
            </div>
            <div class="text-center py-3">
                <div class="text-[24px] font-bold text-white/40">{{ round($totalLoggedHours - $billableHours, 1) }}</div>
                <div class="text-[10px] text-white/30 uppercase mt-1">Non-Billable</div>
            </div>
        </div>
    </div>
</div>

</x-layouts.smartprojects>
