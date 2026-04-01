<x-layouts.hr title="My Leaves" currentView="my-leaves">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    csrf: document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
    balances: @js($balances->map(fn($b) => [
        'type_name' => $b->leaveType->name,
        'type_code' => $b->leaveType->code,
        'color' => $b->leaveType->color ?? '#06b6d4',
        'available' => $b->available,
        'total' => $b->leaveType->max_days_per_year,
        'used' => $b->used,
    ])),
    leaves: @js($leaveHistory->items()),
    pagination: {
        current_page: {{ $leaveHistory->currentPage() }},
        last_page: {{ $leaveHistory->lastPage() }},
        total: {{ $leaveHistory->total() }},
        per_page: {{ $leaveHistory->perPage() }},
    },
    filterStatus: 'all',
    filterType: 'all',
    cancelling: null,
    cancelSuccess: null,

    get filteredLeaves() {
        return this.leaves.filter(l => {
            if (this.filterStatus !== 'all' && l.status !== this.filterStatus) return false;
            if (this.filterType !== 'all' && l.leave_type?.name !== this.filterType) return false;
            return true;
        });
    },

    get uniqueTypes() {
        const types = [...new Set(this.leaves.map(l => l.leave_type?.name).filter(Boolean))];
        return types;
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

    formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },

    cancelRequest(id) {
        this.$dispatch('confirm-modal', {
            title: 'Cancel Leave Request',
            message: 'Are you sure you want to cancel this leave request?',
            confirmLabel: 'Cancel Request',
            variant: 'danger',
            onConfirm: async () => {
                this.cancelling = id;
                this.cancelSuccess = null;

                try {
                    const res = await fetch(`/api/hr/leave-requests/${id}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                        },
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        alert(data.message || 'Failed to cancel leave request.');
                        return;
                    }

                    const leave = this.leaves.find(l => l.id === id);
                    if (leave) leave.status = 'cancelled';
                    this.cancelSuccess = id;

                    setTimeout(() => { this.cancelSuccess = null; }, 3000);

                } catch (err) {
                    alert('Network error. Please try again.');
                } finally {
                    this.cancelling = null;
                }
            }
        });
    },

    goToPage(page) {
        if (page < 1 || page > this.pagination.last_page) return;
        const url = new URL(window.location);
        url.searchParams.set('page', page);
        window.location.href = url.toString();
    },
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">My Leaves</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Your leave history and balances</p>
        </div>
        <a href="{{ route('hr.leave.apply') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Apply Leave
        </a>
    </div>

    {{-- Compact Balance Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 xl:grid-cols-6 gap-3">
        <template x-for="(bal, bi) in balances" :key="bi">
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-4 hover:bg-[#1D1D35] transition-colors">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full shrink-0" :style="'background-color:' + bal.color"></div>
                    <span class="text-[11px] font-medium text-white/50 truncate" x-text="bal.type_name"></span>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-[20px] font-bold text-white/85 tabular-nums" x-text="bal.available"></span>
                    <span class="text-[11px] text-white/30">/ <span x-text="bal.total"></span></span>
                </div>
                <div class="mt-2 h-1 bg-white/[0.06] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500"
                         :style="'background-color:' + bal.color + '; width:' + (bal.total > 0 ? Math.round((bal.available / bal.total) * 100) : 0) + '%'"></div>
                </div>
            </div>
        </template>
    </div>

    {{-- Filters --}}
    <div class="flex items-center gap-3 flex-wrap">
        {{-- Status Filter --}}
        <div class="flex items-center gap-1 bg-[#17172A] border border-white/[0.07] rounded-lg p-1">
            <template x-for="s in ['all', 'pending', 'approved', 'rejected', 'cancelled']" :key="s">
                <button @click="filterStatus = s"
                        class="px-3 py-1.5 rounded-md text-[12px] font-medium transition-colors capitalize"
                        :class="filterStatus === s ? 'bg-white/[0.08] text-white/80' : 'text-white/40 hover:text-white/60'"
                        x-text="s === 'all' ? 'All' : s"></button>
            </template>
        </div>

        {{-- Type Filter --}}
        <select x-model="filterType"
                class="px-3 py-2 rounded-lg bg-[#17172A] border border-white/[0.07] text-[12px] text-white/60 focus:outline-none focus:ring-1 focus:ring-cyan-500/30 appearance-none pr-8">
            <option value="all" class="bg-[#17172A]">All Types</option>
            <template x-for="t in uniqueTypes" :key="t">
                <option :value="t" class="bg-[#17172A]" x-text="t"></option>
            </template>
        </select>
    </div>

    {{-- Leave History Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">Leave History</h2>
                <p class="text-[12px] text-white/35 mt-0.5">
                    Showing <span x-text="filteredLeaves.length"></span> of <span x-text="pagination.total"></span> records
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="text-left text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Date Range</th>
                        <th class="text-left text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Type</th>
                        <th class="text-center text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Days</th>
                        <th class="text-center text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-left text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Applied</th>
                        <th class="text-right text-[10px] font-semibold text-white/30 uppercase tracking-wider px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    <template x-for="(leave, li) in filteredLeaves" :key="leave.id || li">
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[13px] text-white/65 font-medium" x-text="formatDate(leave.start_date)"></span>
                                    <template x-if="leave.start_date !== leave.end_date">
                                        <span class="flex items-center gap-1.5">
                                            <svg class="w-3 h-3 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            <span class="text-[13px] text-white/65 font-medium" x-text="formatDate(leave.end_date)"></span>
                                        </span>
                                    </template>
                                    <template x-if="leave.is_half_day">
                                        <span class="text-[9px] font-semibold text-violet-400/80 bg-violet-500/10 px-1.5 py-0.5 rounded ml-1">HALF</span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full shrink-0" :style="'background-color:' + (leave.leave_type?.color || '#06b6d4')"></div>
                                    <span class="text-[13px] text-white/65" x-text="leave.leave_type?.name || '—'"></span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-[13px] text-white/65 font-semibold tabular-nums" x-text="leave.days"></span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full capitalize border"
                                      :class="statusClass(leave.status)"
                                      x-text="leave.status"></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] text-white/40 tabular-nums" x-text="formatDate(leave.created_at)"></span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <template x-if="leave.status === 'pending'">
                                    <button @click="cancelRequest(leave.id)"
                                            :disabled="cancelling === leave.id"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-semibold text-red-400 bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 transition-colors disabled:opacity-50">
                                        <svg x-show="cancelling === leave.id" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span x-text="cancelling === leave.id ? 'Cancelling...' : 'Cancel'"></span>
                                    </button>
                                </template>
                                <template x-if="leave.status === 'cancelled' && cancelSuccess === leave.id">
                                    <span class="text-[11px] text-emerald-400 font-medium">Cancelled</span>
                                </template>
                                <template x-if="leave.status !== 'pending' && cancelSuccess !== leave.id">
                                    <span class="text-[11px] text-white/20">—</span>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Empty State --}}
        <template x-if="filteredLeaves.length === 0">
            <div class="px-5 py-12 text-center">
                <svg class="w-10 h-10 text-white/15 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <p class="text-[14px] text-white/40 font-medium">No leave records found</p>
                <p class="text-[12px] text-white/25 mt-1" x-text="filterStatus !== 'all' || filterType !== 'all' ? 'Try changing your filters' : 'You haven\'t applied for any leaves yet'"></p>
            </div>
        </template>

        {{-- Pagination --}}
        <template x-if="pagination.last_page > 1">
            <div class="px-5 py-4 border-t border-white/[0.06] flex items-center justify-between">
                <p class="text-[12px] text-white/35">
                    Page <span x-text="pagination.current_page"></span> of <span x-text="pagination.last_page"></span>
                </p>
                <div class="flex items-center gap-1.5">
                    <button @click="goToPage(pagination.current_page - 1)"
                            :disabled="pagination.current_page <= 1"
                            class="px-3 py-1.5 rounded-lg text-[12px] font-medium border border-white/[0.08] text-white/50 hover:bg-white/[0.06] hover:text-white/70 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                        Previous
                    </button>

                    <template x-for="p in pagination.last_page" :key="p">
                        <button x-show="p === 1 || p === pagination.last_page || Math.abs(p - pagination.current_page) <= 2"
                                @click="goToPage(p)"
                                class="w-8 h-8 rounded-lg text-[12px] font-medium transition-colors"
                                :class="p === pagination.current_page ? 'prod-bg text-white' : 'text-white/40 hover:bg-white/[0.06] hover:text-white/70'"
                                x-text="p"></button>
                    </template>

                    <button @click="goToPage(pagination.current_page + 1)"
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="px-3 py-1.5 rounded-lg text-[12px] font-medium border border-white/[0.08] text-white/50 hover:bg-white/[0.06] hover:text-white/70 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </template>
    </div>

</div>

</x-layouts.hr>
