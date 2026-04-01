<x-layouts.hr title="Payroll" currentView="payroll">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    runs: @js($recentRuns),
    totalEmployees: {{ $totalEmployees }},
    latestRun: @js($latestRun),
    deleting: null,

    formatAmount(amount) {
        if (!amount && amount !== 0) return '--';
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    },

    statusBadge(status) {
        const map = {
            draft:      'text-amber-400 bg-amber-500/10 border-amber-500/20',
            processing: 'text-blue-400 bg-blue-500/10 border-blue-500/20',
            finalized:  'text-green-400 bg-green-500/10 border-green-500/20',
            paid:       'text-emerald-300 bg-emerald-500/10 border-emerald-500/20',
        };
        return map[status] || 'text-white/45 bg-white/[0.06] border-white/[0.08]';
    },

    monthName(m) {
        const months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return months[m] || m;
    },

    deleteRun(id) {
        this.$dispatch('confirm-modal', {
            title: 'Delete Payroll Run',
            message: 'Are you sure you want to delete this payroll run?',
            confirmLabel: 'Delete',
            variant: 'danger',
            onConfirm: async () => {
                this.deleting = id;
                try {
                    const res = await fetch(`/api/hr/payroll-runs/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    });
                    if (res.ok) {
                        this.runs = this.runs.filter(r => r.id !== id);
                    } else {
                        alert('Failed to delete payroll run.');
                    }
                } catch (e) {
                    alert('Network error.');
                }
                this.deleting = null;
            }
        });
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Payroll</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Manage salary processing and payslips</p>
        </div>
        <a href="{{ route('hr.payroll.run') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
            </svg>
            Run Payroll
        </a>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        {{-- Total Employees on Payroll --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Employees on Payroll</p>
                    <p class="text-[32px] font-bold text-white/85 leading-tight mt-1" x-text="totalEmployees.toLocaleString()">{{ number_format($totalEmployees) }}</p>
                    <p class="text-[12px] text-white/35 mt-1">Active employees</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center shrink-0 group-hover:bg-cyan-500/15 transition-colors">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Last Payroll Amount --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Last Payroll Amount</p>
                    <p class="text-[32px] font-bold text-emerald-400/90 leading-tight mt-1" x-text="latestRun ? formatAmount(latestRun.total_net) : '--'">
                        {{ $latestRun ? '₹' . number_format($latestRun->total_net) : '--' }}
                    </p>
                    <p class="text-[12px] text-white/35 mt-1">
                        <template x-if="latestRun">
                            <span x-text="monthName(latestRun.month) + ' ' + latestRun.year"></span>
                        </template>
                        <template x-if="!latestRun">
                            <span>No runs yet</span>
                        </template>
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0 group-hover:bg-emerald-500/15 transition-colors">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Payroll Status --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Payroll Status</p>
                    <template x-if="latestRun">
                        <p class="text-[28px] font-bold leading-tight mt-1 capitalize"
                           :class="{
                               'text-amber-400/90': latestRun.status === 'draft',
                               'text-blue-400/90': latestRun.status === 'processing',
                               'text-green-400/90': latestRun.status === 'finalized',
                               'text-emerald-300/90': latestRun.status === 'paid',
                           }"
                           x-text="latestRun.status">
                        </p>
                    </template>
                    <template x-if="!latestRun">
                        <p class="text-[28px] font-bold text-white/30 leading-tight mt-1">N/A</p>
                    </template>
                    <p class="text-[12px] text-white/35 mt-1">Current cycle status</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center shrink-0 group-hover:bg-violet-500/15 transition-colors">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Payroll Runs --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">Recent Payroll Runs</h2>
                <p class="text-[12px] text-white/35 mt-0.5">History of all payroll processing cycles</p>
            </div>
            <span class="text-[11px] font-semibold prod-text prod-bg-muted px-2.5 py-1 rounded-full"
                  x-text="runs.length + ' runs'"></span>
        </div>

        <div class="overflow-x-auto">
            <template x-if="runs.length > 0">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/[0.06]">
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Period</th>
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Status</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Employees</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Gross</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Deductions</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Net Pay</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        <template x-for="run in runs" :key="run.id">
                            <tr class="hover:bg-white/[0.02] transition-colors cursor-pointer"
                                @click="window.location.href = '/hr/payroll/runs/' + run.id">
                                <td class="px-5 py-3.5">
                                    <p class="text-[13px] font-medium text-white/80" x-text="monthName(run.month) + ' ' + run.year"></p>
                                    <p class="text-[11px] text-white/30 mt-0.5" x-text="run.created_at ? new Date(run.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }) : ''"></p>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold capitalize border"
                                          :class="statusBadge(run.status)"
                                          x-text="run.status"></span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="text-[13px] text-white/65 font-medium tabular-nums" x-text="run.employee_count"></span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-[13px] text-white/65 font-medium tabular-nums" x-text="formatAmount(run.total_gross)"></span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-[13px] text-red-400/70 font-medium tabular-nums" x-text="formatAmount(run.total_deductions)"></span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-[13px] text-emerald-400/80 font-semibold tabular-nums" x-text="formatAmount(run.total_net)"></span>
                                </td>
                                <td class="px-5 py-3.5 text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-1">
                                        <a :href="'/hr/payroll/runs/' + run.id"
                                           class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/70 transition-colors"
                                           title="View Details">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <template x-if="run.status === 'draft'">
                                            <button @click="deleteRun(run.id)"
                                                    class="p-1.5 rounded-lg hover:bg-red-500/10 text-white/25 hover:text-red-400 transition-colors"
                                                    :disabled="deleting === run.id"
                                                    title="Delete Run">
                                                <svg x-show="deleting !== run.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <svg x-show="deleting === run.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </template>
        </div>

        {{-- Empty State --}}
        <template x-if="runs.length === 0">
            <div class="px-5 py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-white/[0.04] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-[14px] font-medium text-white/40">No payroll runs yet</p>
                <p class="text-[12px] text-white/25 mt-1">Click "Run Payroll" to start your first payroll cycle</p>
                <a href="{{ route('hr.payroll.run') }}"
                   class="inline-flex items-center gap-2 mt-5 px-4 py-2 rounded-lg prod-bg text-white text-[12px] font-semibold hover:opacity-90 transition-opacity">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Run Payroll
                </a>
            </div>
        </template>
    </div>

</div>

</x-layouts.hr>
