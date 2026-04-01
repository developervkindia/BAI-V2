<x-layouts.hr title="Run Payroll" currentView="payroll-run">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    step: 1,
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    employees: @js($employees),
    selectedEmployees: @js($employees->pluck('id')),
    selectAll: true,
    processing: false,
    error: null,
    result: null,
    searchQuery: '',

    get filteredEmployees() {
        if (!this.searchQuery) return this.employees;
        const q = this.searchQuery.toLowerCase();
        return this.employees.filter(e => {
            const name = (e.user?.name || 'Employee').toLowerCase();
            const empId = (e.employee_id || '').toLowerCase();
            return name.includes(q) || empId.includes(q);
        });
    },

    get selectedCount() {
        return this.selectedEmployees.length;
    },

    toggleAll() {
        if (this.selectAll) {
            this.selectedEmployees = this.employees.map(e => e.id);
        } else {
            this.selectedEmployees = [];
        }
    },

    toggleEmployee(id) {
        const idx = this.selectedEmployees.indexOf(id);
        if (idx > -1) {
            this.selectedEmployees.splice(idx, 1);
        } else {
            this.selectedEmployees.push(id);
        }
        this.selectAll = this.selectedEmployees.length === this.employees.length;
    },

    isSelected(id) {
        return this.selectedEmployees.includes(id);
    },

    monthName(m) {
        const months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return months[m] || m;
    },

    nextStep() {
        if (this.selectedEmployees.length === 0) {
            this.error = 'Please select at least one employee.';
            return;
        }
        this.error = null;
        this.step = 2;
    },

    prevStep() {
        this.step = 1;
        this.error = null;
    },

    async processPayroll() {
        this.processing = true;
        this.error = null;
        try {
            const res = await fetch('/api/hr/payroll-runs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    month: this.month,
                    year: this.year,
                    employee_ids: this.selectedEmployees,
                }),
            });
            const data = await res.json();
            if (res.ok) {
                this.result = data;
                this.step = 3;
                // Redirect to show-run after brief display
                if (data.id) {
                    setTimeout(() => {
                        window.location.href = '/hr/payroll/runs/' + data.id;
                    }, 2000);
                }
            } else {
                this.error = data.message || 'Failed to process payroll.';
            }
        } catch (e) {
            this.error = 'Network error. Please try again.';
        }
        this.processing = false;
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
                <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Run Payroll</h1>
                <p class="text-[13px] text-white/40 mt-0.5">Process monthly payroll for employees</p>
            </div>
        </div>
    </div>

    {{-- Step Indicator --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
        <div class="flex items-center justify-between max-w-lg mx-auto">
            {{-- Step 1 --}}
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-bold transition-colors"
                     :class="step >= 1 ? 'prod-bg text-white' : 'bg-white/[0.06] text-white/30'">1</div>
                <span class="text-[13px] font-medium hidden sm:inline"
                      :class="step >= 1 ? 'text-white/80' : 'text-white/30'">Select & Review</span>
            </div>
            <div class="flex-1 h-px mx-4" :class="step >= 2 ? 'bg-cyan-500/40' : 'bg-white/[0.08]'"></div>
            {{-- Step 2 --}}
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-bold transition-colors"
                     :class="step >= 2 ? 'prod-bg text-white' : 'bg-white/[0.06] text-white/30'">2</div>
                <span class="text-[13px] font-medium hidden sm:inline"
                      :class="step >= 2 ? 'text-white/80' : 'text-white/30'">Confirm & Process</span>
            </div>
            <div class="flex-1 h-px mx-4" :class="step >= 3 ? 'bg-cyan-500/40' : 'bg-white/[0.08]'"></div>
            {{-- Step 3 --}}
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-bold transition-colors"
                     :class="step >= 3 ? 'prod-bg text-white' : 'bg-white/[0.06] text-white/30'">3</div>
                <span class="text-[13px] font-medium hidden sm:inline"
                      :class="step >= 3 ? 'text-white/80' : 'text-white/30'">Complete</span>
            </div>
        </div>
    </div>

    {{-- Error Banner --}}
    <template x-if="error">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-3.5 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <p class="text-[13px] text-red-400 font-medium" x-text="error"></p>
        </div>
    </template>

    {{-- STEP 1: Select Month/Year & Review Employees --}}
    <div x-show="step === 1" x-cloak class="space-y-5">

        {{-- Month/Year Selection --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.06]">
                <h2 class="text-[14px] font-semibold text-white/85">Payroll Period</h2>
                <p class="text-[12px] text-white/35 mt-0.5">Select the month and year to process</p>
            </div>
            <div class="p-5 flex flex-wrap gap-4">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[11px] font-semibold text-white/30 uppercase tracking-widest mb-2">Month</label>
                    <select x-model.number="month"
                            class="w-full bg-white/[0.06] border border-white/[0.08] rounded-lg px-3 py-2.5 text-[13px] text-white/80 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30">
                        <template x-for="m in [1,2,3,4,5,6,7,8,9,10,11,12]" :key="m">
                            <option :value="m" x-text="monthName(m)" :selected="m === month"></option>
                        </template>
                    </select>
                </div>
                <div class="flex-1 min-w-[120px]">
                    <label class="block text-[11px] font-semibold text-white/30 uppercase tracking-widest mb-2">Year</label>
                    <select x-model.number="year"
                            class="w-full bg-white/[0.06] border border-white/[0.08] rounded-lg px-3 py-2.5 text-[13px] text-white/80 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30">
                        <template x-for="y in [year - 1, year, year + 1]" :key="y">
                            <option :value="y" x-text="y" :selected="y === year"></option>
                        </template>
                    </select>
                </div>
            </div>
        </div>

        {{-- Employee List --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-[14px] font-semibold text-white/85">Employees</h2>
                    <p class="text-[12px] text-white/35 mt-0.5">
                        <span x-text="selectedCount"></span> of <span x-text="employees.length"></span> selected
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="searchQuery" placeholder="Search employees..."
                               class="pl-8 pr-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[12px] text-white/70 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 w-52"/>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                               class="w-4 h-4 rounded border-white/20 bg-white/[0.06] text-cyan-500 focus:ring-cyan-500/40 focus:ring-offset-0"/>
                        <span class="text-[12px] text-white/50 font-medium">Select All</span>
                    </label>
                </div>
            </div>

            <div class="max-h-[400px] overflow-y-auto scrollbar-none divide-y divide-white/[0.04]">
                <template x-for="emp in filteredEmployees" :key="emp.id">
                    <label class="flex items-center gap-4 px-5 py-3 hover:bg-white/[0.02] transition-colors cursor-pointer">
                        <input type="checkbox"
                               :checked="isSelected(emp.id)"
                               @change="toggleEmployee(emp.id)"
                               class="w-4 h-4 rounded border-white/20 bg-white/[0.06] text-cyan-500 focus:ring-cyan-500/40 focus:ring-offset-0 shrink-0"/>
                        <div class="w-8 h-8 rounded-full bg-cyan-500/15 text-cyan-400 text-[10px] font-bold flex items-center justify-center shrink-0"
                             x-text="(emp.user?.name || 'Employee').split(' ').map(w => w[0]).slice(0,2).join('').toUpperCase()">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-white/80 truncate"
                               x-text="emp.user?.name || 'Employee'"></p>
                            <p class="text-[11px] text-white/30 truncate"
                               x-text="(emp.employee_id || 'EMP-' + emp.id) + (emp.department?.name ? ' &middot; ' + emp.department.name : '')"></p>
                        </div>
                        <span class="text-[11px] text-white/25 font-medium shrink-0"
                              x-text="emp.designation || emp.job_title || 'Employee'"></span>
                    </label>
                </template>
            </div>

            <template x-if="filteredEmployees.length === 0">
                <div class="px-5 py-10 text-center">
                    <p class="text-[13px] text-white/30">No employees found</p>
                </div>
            </template>
        </div>

        {{-- Step Actions --}}
        <div class="flex justify-end">
            <button @click="nextStep()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity">
                Continue to Confirm
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </div>

    {{-- STEP 2: Confirm & Process --}}
    <div x-show="step === 2" x-cloak class="space-y-5">

        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.06]">
                <h2 class="text-[14px] font-semibold text-white/85">Confirm Payroll Processing</h2>
                <p class="text-[12px] text-white/35 mt-0.5">Review the details below before processing</p>
            </div>
            <div class="p-6 space-y-6">
                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white/[0.03] border border-white/[0.06] rounded-xl p-4 text-center">
                        <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Period</p>
                        <p class="text-[18px] font-bold text-white/85 mt-1" x-text="monthName(month) + ' ' + year"></p>
                    </div>
                    <div class="bg-white/[0.03] border border-white/[0.06] rounded-xl p-4 text-center">
                        <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Employees</p>
                        <p class="text-[18px] font-bold text-cyan-400/90 mt-1" x-text="selectedCount"></p>
                    </div>
                    <div class="bg-white/[0.03] border border-white/[0.06] rounded-xl p-4 text-center">
                        <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Status</p>
                        <p class="text-[18px] font-bold text-amber-400/90 mt-1">Draft</p>
                    </div>
                </div>

                {{-- Notice --}}
                <div class="bg-cyan-500/5 border border-cyan-500/15 rounded-xl px-5 py-4 flex gap-3">
                    <svg class="w-5 h-5 text-cyan-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-[13px] text-cyan-300/80 font-medium">This will calculate salary for all selected employees</p>
                        <p class="text-[12px] text-white/35 mt-1">The payroll run will be created in "draft" status. You can review and finalize it afterwards.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step Actions --}}
        <div class="flex items-center justify-between">
            <button @click="prevStep()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-white/60 text-[13px] font-medium hover:bg-white/[0.1] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back
            </button>
            <button @click="processPayroll()"
                    :disabled="processing"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-cyan-500/10">
                <template x-if="!processing">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                        Process Payroll
                    </span>
                </template>
                <template x-if="processing">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Processing...
                    </span>
                </template>
            </button>
        </div>
    </div>

    {{-- STEP 3: Success --}}
    <div x-show="step === 3" x-cloak class="space-y-5">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
            <div class="p-10 text-center">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-[20px] font-bold text-white/85">Payroll Processed Successfully</h2>
                <p class="text-[13px] text-white/40 mt-2">
                    <span x-text="monthName(month) + ' ' + year"></span> payroll has been created for
                    <span class="text-white/65 font-semibold" x-text="selectedCount"></span> employees.
                </p>
                <p class="text-[12px] text-white/25 mt-1">Redirecting to payroll details...</p>

                <div class="mt-6 flex items-center justify-center gap-3">
                    <a href="{{ route('hr.payroll.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-white/60 text-[13px] font-medium hover:bg-white/[0.1] transition-colors">
                        Back to Payroll
                    </a>
                    <template x-if="result && result.id">
                        <a :href="'/hr/payroll/runs/' + result.id"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity">
                            View Details
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>

</div>

</x-layouts.hr>
