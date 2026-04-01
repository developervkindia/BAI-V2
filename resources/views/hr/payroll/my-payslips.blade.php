<x-layouts.hr title="My Payslips" currentView="my-payslips">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    payslips: @js($payslips->items()),
    employee: @js($employee),
    pagination: {
        currentPage: {{ $payslips->currentPage() }},
        lastPage: {{ $payslips->lastPage() }},
        total: {{ $payslips->total() }},
        perPage: {{ $payslips->perPage() }},
    },
    loading: false,

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
            calculated: 'text-blue-400 bg-blue-500/10 border-blue-500/20',
            finalized:  'text-green-400 bg-green-500/10 border-green-500/20',
            paid:       'text-emerald-300 bg-emerald-500/10 border-emerald-500/20',
        };
        return map[status] || 'text-white/45 bg-white/[0.06] border-white/[0.08]';
    },

    async goToPage(page) {
        if (page < 1 || page > this.pagination.lastPage || page === this.pagination.currentPage) return;
        this.loading = true;
        try {
            const res = await fetch(`/api/hr/my-payslips?page=${page}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            if (res.ok && data.data) {
                this.payslips = data.data;
                this.pagination.currentPage = data.current_page;
                this.pagination.lastPage = data.last_page;
                this.pagination.total = data.total;
                // Update URL without reload
                const url = new URL(window.location);
                url.searchParams.set('page', page);
                history.pushState({}, '', url);
            }
        } catch (e) {
            console.error('Failed to load payslips:', e);
        }
        this.loading = false;
    },

    viewPayslip(entryId) {
        window.location.href = '/hr/payroll/payslip/' + entryId;
    },

    get pageNumbers() {
        const pages = [];
        const current = this.pagination.currentPage;
        const last = this.pagination.lastPage;
        const delta = 2;
        for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
            pages.push(i);
        }
        return pages;
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">My Payslips</h1>
            <p class="text-[13px] text-white/40 mt-0.5">
                View and download your salary payslips
            </p>
        </div>
    </div>

    {{-- Employee Info Card --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-cyan-500/15 text-cyan-400 text-[14px] font-bold flex items-center justify-center shrink-0"
                 x-text="(employee?.user?.name || 'U').split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase()">
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[15px] font-semibold text-white/85" x-text="employee?.user?.name || 'Employee'"></p>
                <div class="flex items-center gap-3 mt-0.5 flex-wrap">
                    <span class="text-[12px] text-white/35" x-text="employee?.employee_id || ('EMP-' + (employee?.id || ''))"></span>
                    <template x-if="employee?.department?.name">
                        <span class="text-[12px] text-white/35 flex items-center gap-1">
                            <span class="w-1 h-1 rounded-full bg-white/20"></span>
                            <span x-text="employee.department.name"></span>
                        </span>
                    </template>
                    <template x-if="employee?.designation || employee?.job_title">
                        <span class="text-[12px] text-white/35 flex items-center gap-1">
                            <span class="w-1 h-1 rounded-full bg-white/20"></span>
                            <span x-text="employee?.designation || employee?.job_title"></span>
                        </span>
                    </template>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Total Payslips</p>
                <p class="text-[20px] font-bold text-white/80 mt-0.5" x-text="pagination.total"></p>
            </div>
        </div>
    </div>

    {{-- Payslips List --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden" :class="{ 'opacity-60': loading }">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">Payslip History</h2>
                <p class="text-[12px] text-white/35 mt-0.5">All your salary slips in one place</p>
            </div>
            <template x-if="loading">
                <svg class="w-5 h-5 animate-spin text-cyan-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </template>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <template x-if="payslips.length > 0">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/[0.06]">
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Period</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Gross Earnings</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Deductions</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Net Pay</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Status</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        <template x-for="slip in payslips" :key="slip.id">
                            <tr class="hover:bg-white/[0.02] transition-colors cursor-pointer"
                                @click="viewPayslip(slip.id)">
                                <td class="px-5 py-4">
                                    <p class="text-[13px] font-medium text-white/80"
                                       x-text="monthName(slip.payroll_run?.month) + ' ' + slip.payroll_run?.year"></p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="text-[13px] text-white/65 font-medium tabular-nums" x-text="formatAmount(slip.gross_earnings)"></span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="text-[13px] text-red-400/70 font-medium tabular-nums" x-text="formatAmount(slip.total_deductions)"></span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <span class="text-[14px] text-emerald-400/90 font-semibold tabular-nums" x-text="formatAmount(slip.net_pay)"></span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold capitalize border"
                                          :class="statusBadge(slip.status)"
                                          x-text="slip.status"></span>
                                </td>
                                <td class="px-5 py-4 text-right" @click.stop>
                                    <button @click="viewPayslip(slip.id)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/[0.04] border border-white/[0.08] text-white/50 text-[11px] font-medium hover:bg-white/[0.08] hover:text-white/70 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        View
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </template>
        </div>

        {{-- Empty State --}}
        <template x-if="payslips.length === 0 && !loading">
            <div class="px-5 py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-white/[0.04] flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-[16px] font-semibold text-white/40">No payslips yet</p>
                <p class="text-[13px] text-white/25 mt-1.5 max-w-sm mx-auto">Your payslips will appear here once payroll has been processed for your account.</p>
            </div>
        </template>

        {{-- Pagination --}}
        <template x-if="pagination.lastPage > 1">
            <div class="px-5 py-4 border-t border-white/[0.06] flex items-center justify-between">
                <p class="text-[12px] text-white/30">
                    Showing page <span class="text-white/50 font-medium" x-text="pagination.currentPage"></span>
                    of <span class="text-white/50 font-medium" x-text="pagination.lastPage"></span>
                    (<span x-text="pagination.total"></span> total)
                </p>
                <div class="flex items-center gap-1">
                    {{-- Previous --}}
                    <button @click="goToPage(pagination.currentPage - 1)"
                            :disabled="pagination.currentPage <= 1"
                            class="p-2 rounded-lg text-white/35 hover:text-white/70 hover:bg-white/[0.06] transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    {{-- Page Numbers --}}
                    <template x-for="page in pageNumbers" :key="page">
                        <button @click="goToPage(page)"
                                class="w-8 h-8 rounded-lg text-[12px] font-medium transition-colors"
                                :class="page === pagination.currentPage
                                    ? 'prod-bg text-white'
                                    : 'text-white/40 hover:text-white/70 hover:bg-white/[0.06]'"
                                x-text="page">
                        </button>
                    </template>

                    {{-- Next --}}
                    <button @click="goToPage(pagination.currentPage + 1)"
                            :disabled="pagination.currentPage >= pagination.lastPage"
                            class="p-2 rounded-lg text-white/35 hover:text-white/70 hover:bg-white/[0.06] transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

</div>

</x-layouts.hr>
