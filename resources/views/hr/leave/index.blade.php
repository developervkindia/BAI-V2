<x-layouts.hr title="Leave Overview" currentView="leave">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    balances: @js($balances->map(fn($b) => [
        'id' => $b->id,
        'type_name' => $b->leaveType->name,
        'type_code' => $b->leaveType->code,
        'color' => $b->leaveType->color ?? '#06b6d4',
        'is_paid' => $b->leaveType->is_paid,
        'available' => $b->available,
        'total' => $b->leaveType->max_days_per_year,
        'used' => $b->used,
        'opening' => $b->opening_balance,
        'accrued' => $b->accrued,
        'adjusted' => $b->adjusted,
        'carried_forward' => $b->carried_forward,
        'encashed' => $b->encashed,
    ])),
    requests: @js($recentRequests->map(fn($r) => [
        'id' => $r->id,
        'type_name' => $r->leaveType->name,
        'type_color' => $r->leaveType->color ?? '#06b6d4',
        'start_date' => $r->start_date?->format('M d, Y'),
        'end_date' => $r->end_date?->format('M d, Y'),
        'days' => $r->days,
        'status' => $r->status,
        'reason' => $r->reason,
        'is_half_day' => $r->is_half_day,
    ])),
    expandedCard: null,
    toggleCard(id) {
        this.expandedCard = this.expandedCard === id ? null : id;
    },
    progressPercent(available, total) {
        if (total <= 0) return 0;
        return Math.min(Math.round((available / total) * 100), 100);
    },
    statusClass(status) {
        const map = {
            'pending': 'text-amber-400 bg-amber-500/10 border-amber-500/20',
            'approved': 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
            'rejected': 'text-red-400 bg-red-500/10 border-red-500/20',
            'cancelled': 'text-white/40 bg-white/[0.05] border-white/[0.08]',
        };
        return map[status] || 'text-white/40 bg-white/[0.05] border-white/[0.08]';
    },
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Leave Overview</h1>
            <p class="text-[13px] text-white/40 mt-0.5">{{ now()->format('Y') }} leave balances and recent activity</p>
        </div>
        <a href="{{ route('hr.leave.apply') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Apply Leave
        </a>
    </div>

    {{-- Leave Balance Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <template x-for="(balance, idx) in balances" :key="idx">
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 cursor-pointer"
                 @click="toggleCard(idx)">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-2.5 h-2.5 rounded-full shrink-0" :style="'background-color:' + balance.color"></div>
                            <span class="text-[13px] font-semibold text-white/80 truncate" x-text="balance.type_name"></span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5 ml-[18px]">
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded"
                                  :class="balance.is_paid ? 'text-emerald-400/80 bg-emerald-500/10' : 'text-amber-400/80 bg-amber-500/10'"
                                  x-text="balance.is_paid ? 'Paid' : 'Unpaid'"></span>
                            <span class="text-[10px] text-white/30 font-mono" x-text="balance.type_code"></span>
                        </div>
                    </div>

                    {{-- Circular Progress --}}
                    <div class="relative w-14 h-14 shrink-0">
                        <svg class="w-14 h-14 -rotate-90" viewBox="0 0 56 56">
                            <circle cx="28" cy="28" r="22" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="4"/>
                            <circle cx="28" cy="28" r="22" fill="none"
                                    :stroke="balance.color"
                                    stroke-width="4"
                                    stroke-linecap="round"
                                    :stroke-dasharray="(2 * Math.PI * 22)"
                                    :stroke-dashoffset="(2 * Math.PI * 22) * (1 - progressPercent(balance.available, balance.total) / 100)"
                                    class="transition-all duration-700"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-[13px] font-bold text-white/85 tabular-nums" x-text="balance.available"></span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-[12px] text-white/35">Available</span>
                    <span class="text-[12px] text-white/50 font-medium tabular-nums">
                        <span x-text="balance.available"></span> / <span x-text="balance.total"></span> days
                    </span>
                </div>

                {{-- Expanded Details --}}
                <div x-show="expandedCard === idx" x-collapse class="mt-3 pt-3 border-t border-white/[0.06] space-y-1.5">
                    <div class="flex justify-between text-[11px]">
                        <span class="text-white/35">Opening Balance</span>
                        <span class="text-white/55 tabular-nums" x-text="balance.opening"></span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-white/35">Accrued</span>
                        <span class="text-white/55 tabular-nums" x-text="balance.accrued"></span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-white/35">Used</span>
                        <span class="text-red-400/70 tabular-nums" x-text="'-' + balance.used"></span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-white/35">Adjusted</span>
                        <span class="text-white/55 tabular-nums" x-text="balance.adjusted"></span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-white/35">Carried Forward</span>
                        <span class="text-white/55 tabular-nums" x-text="balance.carried_forward"></span>
                    </div>
                    <div class="flex justify-between text-[11px]">
                        <span class="text-white/35">Encashed</span>
                        <span class="text-white/55 tabular-nums" x-text="balance.encashed"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty state for balances --}}
    <template x-if="balances.length === 0">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-10 text-center">
            <svg class="w-10 h-10 text-white/15 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-[14px] text-white/40 font-medium">No leave balances found</p>
            <p class="text-[12px] text-white/25 mt-1">Leave types have not been configured yet</p>
        </div>
    </template>

    {{-- Recent Leave Requests --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">Recent Leave Requests</h2>
                <p class="text-[12px] text-white/35 mt-0.5">Your latest leave applications</p>
            </div>
            <a href="{{ route('hr.leave.my') }}"
               class="text-[12px] font-medium prod-text hover:underline">
                View All
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="text-left text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Date Range</th>
                        <th class="text-left text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Type</th>
                        <th class="text-center text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Days</th>
                        <th class="text-center text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-left text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    <template x-for="(req, ri) in requests" :key="ri">
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[13px] text-white/65 font-medium" x-text="req.start_date"></span>
                                    <template x-if="req.start_date !== req.end_date">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            <span class="text-[13px] text-white/65 font-medium" x-text="req.end_date"></span>
                                        </span>
                                    </template>
                                    <template x-if="req.is_half_day">
                                        <span class="text-[9px] font-semibold text-violet-400/80 bg-violet-500/10 px-1.5 py-0.5 rounded ml-1">HALF</span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full shrink-0" :style="'background-color:' + req.type_color"></div>
                                    <span class="text-[13px] text-white/65" x-text="req.type_name"></span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-[13px] text-white/65 font-semibold tabular-nums" x-text="req.days"></span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full capitalize border"
                                      :class="statusClass(req.status)"
                                      x-text="req.status"></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] text-white/45 truncate max-w-[200px] block" x-text="req.reason || '—'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Empty state --}}
        <template x-if="requests.length === 0">
            <div class="px-5 py-10 text-center">
                <svg class="w-10 h-10 text-white/15 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-[14px] text-white/40 font-medium">No leave requests yet</p>
                <p class="text-[12px] text-white/25 mt-1">Your leave applications will appear here</p>
            </div>
        </template>
    </div>

</div>

</x-layouts.hr>
