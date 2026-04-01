<x-layouts.hr title="Review Cycles" currentView="perf-cycles">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    cycles: {{ Js::from($cycles) }},
    statusFilter: 'all',

    get filteredCycles() {
        if (this.statusFilter === 'all') return this.cycles;
        return this.cycles.filter(c => c.status === this.statusFilter);
    },

    statusColor(status) {
        const colors = {
            'draft': 'text-white/45 bg-white/[0.06] border-white/[0.08]',
            'active': 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
            'self_review': 'text-amber-400 bg-amber-500/10 border-amber-500/20',
            'manager_review': 'text-blue-400 bg-blue-500/10 border-blue-500/20',
            'calibration': 'text-violet-400 bg-violet-500/10 border-violet-500/20',
            'closed': 'text-red-400 bg-red-500/10 border-red-500/20',
        };
        return colors[status] || 'text-white/45 bg-white/[0.06] border-white/[0.08]';
    },

    statusDot(status) {
        const colors = {
            'draft': 'bg-white/30',
            'active': 'bg-emerald-400',
            'self_review': 'bg-amber-400',
            'manager_review': 'bg-blue-400',
            'calibration': 'bg-violet-400',
            'closed': 'bg-red-400',
        };
        return colors[status] || 'bg-white/30';
    },

    typeBadgeColor(type) {
        const colors = {
            'quarterly': 'text-cyan-400 bg-cyan-500/10',
            'half_yearly': 'text-indigo-400 bg-indigo-500/10',
            'annual': 'text-amber-400 bg-amber-500/10',
        };
        return colors[type] || 'text-white/45 bg-white/[0.06]';
    },

    formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },

    formatDateShort(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    },

    reviewCount(cycle) {
        return cycle.reviews_count ?? cycle.reviews?.length ?? 0;
    }
}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Review Cycles</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Manage and track performance review cycles</p>
        </div>
        <div class="flex items-center gap-2">
            <template x-for="f in ['all', 'draft', 'active', 'closed']" :key="f">
                <button @click="statusFilter = f"
                        :class="statusFilter === f ? 'prod-bg text-white' : 'text-white/45 bg-white/[0.04] hover:bg-white/[0.08] hover:text-white/65'"
                        class="px-3 py-1.5 rounded-lg text-[12px] font-semibold capitalize transition-all duration-150"
                        x-text="f === 'all' ? 'All (' + cycles.length + ')' : f.charAt(0).toUpperCase() + f.slice(1)">
                </button>
            </template>
        </div>
    </div>

    {{-- Cycles Grid --}}
    <template x-if="filteredCycles.length === 0">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-white/10 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <p class="text-[14px] text-white/35 font-medium">No review cycles found</p>
            <p class="text-[12px] text-white/25 mt-1">Try adjusting your filter</p>
        </div>
    </template>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <template x-for="cycle in filteredCycles" :key="cycle.id">
            <a :href="'{{ url('hr/performance/cycles') }}/' + cycle.id"
               class="block bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">

                {{-- Card Top Accent --}}
                <div class="h-[3px] w-full"
                     :class="{
                        'bg-white/10': cycle.status === 'draft',
                        'bg-emerald-500/60': cycle.status === 'active',
                        'bg-amber-500/60': cycle.status === 'self_review',
                        'bg-blue-500/60': cycle.status === 'manager_review',
                        'bg-violet-500/60': cycle.status === 'calibration',
                        'bg-red-500/60': cycle.status === 'closed',
                     }"></div>

                <div class="p-5">
                    {{-- Header --}}
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-[14px] font-semibold text-white/85 truncate group-hover:text-white transition-colors" x-text="cycle.name"></h3>
                        </div>
                        <svg class="w-4 h-4 text-white/15 group-hover:text-white/40 transition-colors shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>

                    {{-- Badges --}}
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-[10px] font-semibold capitalize px-2 py-0.5 rounded-full" :class="typeBadgeColor(cycle.type)" x-text="cycle.type.replace('_', ' ')"></span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold capitalize px-2 py-0.5 rounded-full" :class="statusColor(cycle.status)">
                            <span class="w-1.5 h-1.5 rounded-full" :class="statusDot(cycle.status)"></span>
                            <span x-text="cycle.status.replace('_', ' ')"></span>
                        </span>
                    </div>

                    {{-- Date Range --}}
                    <div class="flex items-center gap-2 text-[12px] text-white/40 mb-3">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-text="formatDateShort(cycle.start_date) + ' — ' + formatDateShort(cycle.end_date)"></span>
                    </div>

                    {{-- Deadlines --}}
                    <div class="space-y-1.5 mb-4">
                        <template x-if="cycle.self_review_deadline">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-white/30">Self Review Deadline</span>
                                <span class="text-white/50 font-medium" x-text="formatDateShort(cycle.self_review_deadline)"></span>
                            </div>
                        </template>
                        <template x-if="cycle.manager_review_deadline">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-white/30">Manager Review Deadline</span>
                                <span class="text-white/50 font-medium" x-text="formatDateShort(cycle.manager_review_deadline)"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Footer --}}
                    <div class="pt-3 border-t border-white/[0.06] flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-[11px] text-white/30">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span x-text="reviewCount(cycle) + ' employee' + (reviewCount(cycle) !== 1 ? 's' : '')"></span>
                        </div>
                        <span class="text-[10px] font-medium text-white/20" x-text="'#' + cycle.id"></span>
                    </div>
                </div>
            </a>
        </template>
    </div>

</div>

</x-layouts.hr>
