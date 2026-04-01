<x-layouts.hr title="Payroll Run Details" currentView="payroll">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    run: @js($payrollRun),
    entries: @js($payrollRun->entries),
    finalizing: false,
    markingPaid: false,
    error: null,
    successMsg: null,

    formatAmount(amount) {
        if (!amount && amount !== 0) return '--';
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    },

    monthName(m) {
        const months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return months[m] || m;
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

    entryStatusBadge(status) {
        const map = {
            calculated: 'text-blue-400 bg-blue-500/10',
            finalized:  'text-green-400 bg-green-500/10',
            paid:       'text-emerald-300 bg-emerald-500/10',
            error:      'text-red-400 bg-red-500/10',
        };
        return map[status] || 'text-white/45 bg-white/[0.06]';
    },

    finalize() {
        this.$dispatch('confirm-modal', {
            title: 'Finalize Payroll Run',
            message: 'Are you sure you want to finalize this payroll run? This cannot be undone.',
            confirmLabel: 'Finalize',
            variant: 'warning',
            onConfirm: async () => {
                this.finalizing = true;
                this.error = null;
                this.successMsg = null;
                try {
                    const res = await fetch(`/api/hr/payroll-runs/${this.run.id}/finalize`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.run.status = 'finalized';
                        this.entries.forEach(e => e.status = 'finalized');
                        this.successMsg = 'Payroll run has been finalized successfully.';
                    } else {
                        this.error = data.message || 'Failed to finalize payroll run.';
                    }
                } catch (e) {
                    this.error = 'Network error. Please try again.';
                }
                this.finalizing = false;
            }
        });
    },

    markPaid() {
        this.$dispatch('confirm-modal', {
            title: 'Mark as Paid',
            message: 'Mark this payroll run as paid? This indicates salaries have been disbursed.',
            confirmLabel: 'Mark as Paid',
            variant: 'warning',
            onConfirm: async () => {
                this.markingPaid = true;
                this.error = null;
                this.successMsg = null;
                try {
                    const res = await fetch(`/api/hr/payroll-runs/${this.run.id}/mark-paid`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    if (res.ok) {
                        this.run.status = 'paid';
                        this.entries.forEach(e => e.status = 'paid');
                        this.successMsg = 'Payroll run marked as paid.';
                    } else {
                        this.error = data.message || 'Failed to mark as paid.';
                    }
                } catch (e) {
                    this.error = 'Network error. Please try again.';
                }
                this.markingPaid = false;
            }
        });
    },

    get totalGross() {
        return this.entries.reduce((sum, e) => sum + Number(e.gross_earnings || 0), 0);
    },
    get totalDeductions() {
        return this.entries.reduce((sum, e) => sum + Number(e.total_deductions || 0), 0);
    },
    get totalNet() {
        return this.entries.reduce((sum, e) => sum + Number(e.net_pay || 0), 0);
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('hr.payroll.index') }}"
               class="p-2 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/70 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-[22px] font-bold text-white/85 tracking-tight" x-text="monthName(run.month) + ' ' + run.year + ' Payroll'"></h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold capitalize border"
                          :class="statusBadge(run.status)"
                          x-text="run.status"></span>
                </div>
                <p class="text-[13px] text-white/40 mt-0.5">
                    Processed on <span x-text="run.created_at ? new Date(run.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' }) : '--'"></span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            {{-- Finalize Button --}}
            <template x-if="run.status === 'draft'">
                <button @click="finalize()"
                        :disabled="finalizing"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-green-500/15 border border-green-500/25 text-green-400 text-[13px] font-semibold hover:bg-green-500/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <template x-if="!finalizing">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="finalizing">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </template>
                    <span x-text="finalizing ? 'Finalizing...' : 'Finalize'"></span>
                </button>
            </template>

            {{-- Mark Paid Button --}}
            <template x-if="run.status === 'finalized'">
                <button @click="markPaid()"
                        :disabled="markingPaid"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-cyan-500/10">
                    <template x-if="!markingPaid">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </template>
                    <template x-if="markingPaid">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </template>
                    <span x-text="markingPaid ? 'Processing...' : 'Mark as Paid'"></span>
                </button>
            </template>
        </div>
    </div>

    {{-- Alert Messages --}}
    <template x-if="error">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-3.5 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <p class="text-[13px] text-red-400 font-medium" x-text="error"></p>
            <button @click="error = null" class="ml-auto text-red-400/60 hover:text-red-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
    <template x-if="successMsg">
        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-5 py-3.5 flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-[13px] text-emerald-400 font-medium" x-text="successMsg"></p>
            <button @click="successMsg = null" class="ml-auto text-emerald-400/60 hover:text-emerald-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Employees</p>
            <p class="text-[28px] font-bold text-white/85 leading-tight mt-1" x-text="entries.length"></p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Total Gross</p>
            <p class="text-[28px] font-bold text-white/85 leading-tight mt-1" x-text="formatAmount(totalGross)"></p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Deductions</p>
            <p class="text-[28px] font-bold text-red-400/80 leading-tight mt-1" x-text="formatAmount(totalDeductions)"></p>
        </div>
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Net Payable</p>
            <p class="text-[28px] font-bold text-emerald-400/90 leading-tight mt-1" x-text="formatAmount(totalNet)"></p>
        </div>
    </div>

    {{-- Entries Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">Employee Payroll Entries</h2>
                <p class="text-[12px] text-white/35 mt-0.5">Individual salary breakdown for each employee</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <template x-if="entries.length > 0">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/[0.06]">
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Employee</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Gross Earnings</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Deductions</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Net Pay</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Working Days</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Present</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">LOP</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        <template x-for="entry in entries" :key="entry.id">
                            <tr class="hover:bg-white/[0.02] transition-colors cursor-pointer"
                                @click="window.location.href = '/hr/payroll/payslip/' + entry.id">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-cyan-500/15 text-cyan-400 text-[10px] font-bold flex items-center justify-center shrink-0"
                                             x-text="(entry.employee_profile?.user?.name || 'E').split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase()">
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[13px] font-medium text-white/80 truncate"
                                               x-text="entry.employee_profile?.user?.name || ('EMP #' + entry.employee_profile_id)"></p>
                                            <p class="text-[11px] text-white/30 truncate"
                                               x-text="entry.employee_profile?.employee_id || ''"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-[13px] text-white/65 font-medium tabular-nums" x-text="formatAmount(entry.gross_earnings)"></span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-[13px] text-red-400/70 font-medium tabular-nums" x-text="formatAmount(entry.total_deductions)"></span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-[13px] text-emerald-400/80 font-semibold tabular-nums" x-text="formatAmount(entry.net_pay)"></span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="text-[13px] text-white/50 tabular-nums" x-text="entry.working_days || '--'"></span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="text-[13px] text-white/50 tabular-nums" x-text="entry.days_present || '--'"></span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="text-[13px] tabular-nums"
                                          :class="entry.lop_days > 0 ? 'text-amber-400/80 font-semibold' : 'text-white/50'"
                                          x-text="entry.lop_days || '0'"></span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold capitalize"
                                          :class="entryStatusBadge(entry.status)"
                                          x-text="entry.status"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    {{-- Summary Footer --}}
                    <tfoot>
                        <tr class="border-t-2 border-white/[0.08] bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <span class="text-[13px] font-bold text-white/70">TOTAL</span>
                                <span class="text-[11px] text-white/30 ml-2" x-text="'(' + entries.length + ' employees)'"></span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-[13px] text-white/80 font-bold tabular-nums" x-text="formatAmount(totalGross)"></span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-[13px] text-red-400/80 font-bold tabular-nums" x-text="formatAmount(totalDeductions)"></span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <span class="text-[14px] text-emerald-400 font-bold tabular-nums" x-text="formatAmount(totalNet)"></span>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </template>
        </div>

        <template x-if="entries.length === 0">
            <div class="px-5 py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-white/[0.04] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-[14px] font-medium text-white/40">No entries found</p>
                <p class="text-[12px] text-white/25 mt-1">This payroll run has no employee entries yet.</p>
            </div>
        </template>
    </div>

</div>

</x-layouts.hr>
