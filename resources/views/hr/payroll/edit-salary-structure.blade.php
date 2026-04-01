<x-layouts.hr title="Edit Salary Structure" currentView="payroll">

@php
    $structure = $employeeProfile->currentSalaryStructure;
    $empName = $employeeProfile->user->name;
    $initials = collect(explode(' ', $empName))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');

    // Prepare existing structure data for Alpine
    $existingData = null;
    if ($structure) {
        $existingComponents = [];
        if ($structure->components && $structure->components->count() > 0) {
            foreach ($structure->components as $sc) {
                $existingComponents[] = [
                    'component_id' => $sc->component_id,
                    'monthly_amount' => round($sc->annual_amount / 12, 2),
                    'annual_amount' => $sc->annual_amount,
                ];
            }
        }
        $existingData = [
            'annual_ctc' => $structure->annual_ctc,
            'effective_from' => $structure->effective_from ? \Carbon\Carbon::parse($structure->effective_from)->format('Y-m-d') : '',
            'components' => $existingComponents,
        ];
    }

    // Prepare components lookup for Alpine
    $componentsList = $components->map(fn($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'code' => $c->code,
        'type' => $c->type,
        'calculation_type' => $c->calculation_type,
        'percentage_of' => $c->percentage_of,
    ])->values()->toArray();
@endphp

<div class="p-5 lg:p-7 space-y-6" x-data="{
    allComponents: {{ json_encode($componentsList) }},
    annualCtc: {{ $existingData ? $existingData['annual_ctc'] : 0 }},
    effectiveFrom: '{{ $existingData ? $existingData['effective_from'] : now()->format('Y-m-d') }}',
    componentRows: {{ json_encode($existingData ? $existingData['components'] : []) }},
    saving: false,
    success: false,
    errors: {},

    init() {
        // If no existing rows and no CTC, start empty
    },

    formatINR(amount) {
        if (!amount && amount !== 0) return '₹0';
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    },

    getComponent(id) {
        return this.allComponents.find(c => c.id == id) || null;
    },

    getComponentType(id) {
        const comp = this.getComponent(id);
        return comp ? comp.type : '';
    },

    addRow() {
        this.componentRows.push({ component_id: '', monthly_amount: 0, annual_amount: 0 });
    },

    removeRow(index) {
        this.componentRows.splice(index, 1);
    },

    updateMonthly(index) {
        const row = this.componentRows[index];
        row.annual_amount = Math.round(row.monthly_amount * 12 * 100) / 100;
    },

    updateAnnual(index) {
        const row = this.componentRows[index];
        row.monthly_amount = Math.round(row.annual_amount / 12 * 100) / 100;
    },

    autoDistribute() {
        if (!this.annualCtc || this.annualCtc <= 0) return;

        const ctc = parseFloat(this.annualCtc);
        const basicAnnual = Math.round(ctc * 0.40);
        const hraAnnual = Math.round(basicAnnual * 0.50);
        const specialAnnual = ctc - basicAnnual - hraAnnual;

        // Find component IDs by code
        const basicComp = this.allComponents.find(c => c.code && c.code.toUpperCase() === 'BASIC');
        const hraComp = this.allComponents.find(c => c.code && c.code.toUpperCase() === 'HRA');
        const specialComp = this.allComponents.find(c => c.code && (c.code.toUpperCase() === 'SPECIAL' || c.code.toUpperCase() === 'SA' || c.code.toUpperCase() === 'SPECIAL_ALLOWANCE'));

        this.componentRows = [];

        if (basicComp) {
            this.componentRows.push({
                component_id: basicComp.id,
                monthly_amount: Math.round(basicAnnual / 12),
                annual_amount: basicAnnual,
            });
        }

        if (hraComp) {
            this.componentRows.push({
                component_id: hraComp.id,
                monthly_amount: Math.round(hraAnnual / 12),
                annual_amount: hraAnnual,
            });
        }

        if (specialComp) {
            this.componentRows.push({
                component_id: specialComp.id,
                monthly_amount: Math.round(specialAnnual / 12),
                annual_amount: specialAnnual,
            });
        }

        // If no matching components found, just add empty rows
        if (this.componentRows.length === 0) {
            this.componentRows.push({ component_id: '', monthly_amount: Math.round(basicAnnual / 12), annual_amount: basicAnnual });
            this.componentRows.push({ component_id: '', monthly_amount: Math.round(hraAnnual / 12), annual_amount: hraAnnual });
            this.componentRows.push({ component_id: '', monthly_amount: Math.round(specialAnnual / 12), annual_amount: specialAnnual });
        }
    },

    get totalEarningsMonthly() {
        return this.componentRows
            .filter(r => this.getComponentType(r.component_id) === 'earning')
            .reduce((sum, r) => sum + (parseFloat(r.monthly_amount) || 0), 0);
    },

    get totalDeductionsMonthly() {
        return this.componentRows
            .filter(r => this.getComponentType(r.component_id) === 'deduction')
            .reduce((sum, r) => sum + (parseFloat(r.monthly_amount) || 0), 0);
    },

    get netMonthlyPay() {
        return this.totalEarningsMonthly - this.totalDeductionsMonthly;
    },

    get totalEarningsAnnual() {
        return this.componentRows
            .filter(r => this.getComponentType(r.component_id) === 'earning')
            .reduce((sum, r) => sum + (parseFloat(r.annual_amount) || 0), 0);
    },

    get totalDeductionsAnnual() {
        return this.componentRows
            .filter(r => this.getComponentType(r.component_id) === 'deduction')
            .reduce((sum, r) => sum + (parseFloat(r.annual_amount) || 0), 0);
    },

    async save() {
        this.saving = true;
        this.errors = {};
        this.success = false;

        try {
            const payload = {
                annual_ctc: parseFloat(this.annualCtc) || 0,
                effective_from: this.effectiveFrom,
                components: this.componentRows
                    .filter(r => r.component_id)
                    .map(r => ({
                        component_id: parseInt(r.component_id),
                        monthly_amount: parseFloat(r.monthly_amount) || 0,
                        annual_amount: parseFloat(r.annual_amount) || 0,
                    })),
            };

            const res = await fetch('/api/hr/salary-structures/{{ $employeeProfile->id }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();

            if (res.ok) {
                this.success = true;
                setTimeout(() => {
                    window.location.href = '{{ route('hr.payroll.salary-structures') }}';
                }, 1200);
            } else if (res.status === 422) {
                this.errors = data.errors || {};
            } else {
                this.errors = { general: [data.message || 'Failed to save salary structure.'] };
            }
        } catch (e) {
            this.errors = { general: ['Network error. Please try again.'] };
        }
        this.saving = false;
    }
}">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-[12px] text-white/35">
        <a href="{{ route('hr.payroll.index') }}" class="hover:text-white/55 transition-colors">Payroll</a>
        <svg class="w-3.5 h-3.5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('hr.payroll.salary-structures') }}" class="hover:text-white/55 transition-colors">Salary Structures</a>
        <svg class="w-3.5 h-3.5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white/50">{{ $empName }}</span>
    </div>

    {{-- Success Message --}}
    <div x-show="success" x-transition
         class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-5 py-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-[13px] text-emerald-400 font-medium">Salary structure saved successfully. Redirecting...</p>
    </div>

    {{-- General Errors --}}
    <template x-if="errors.general">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-[13px] text-red-400 font-medium" x-text="errors.general[0]"></p>
        </div>
    </template>

    {{-- Employee Card Header --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-cyan-500/30 to-blue-500/30 flex items-center justify-center shrink-0 border border-white/[0.08]">
                <span class="text-[18px] font-bold text-white/70">{{ $initials }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-[18px] font-bold text-white/85">{{ $empName }}</h2>
                <div class="flex items-center gap-3 mt-1 flex-wrap">
                    <span class="text-[12px] font-mono text-white/40">{{ $employeeProfile->employee_id }}</span>
                    @if($employeeProfile->department)
                        <span class="text-[11px] text-white/15">|</span>
                        <span class="text-[12px] text-white/50">{{ $employeeProfile->department }}</span>
                    @endif
                    @if($employeeProfile->designation)
                        <span class="text-[11px] text-white/15">|</span>
                        <span class="text-[12px] text-white/50">{{ $employeeProfile->designation }}</span>
                    @endif
                </div>
            </div>
            @if($structure)
                <div class="text-right hidden sm:block">
                    <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Current CTC</p>
                    <p class="text-[20px] font-bold text-emerald-400/80 tabular-nums mt-0.5">{{ '₹' . number_format($structure->annual_ctc) }}</p>
                    @if($structure->effective_from)
                        <p class="text-[11px] text-white/30 mt-0.5">Since {{ \Carbon\Carbon::parse($structure->effective_from)->format('d M Y') }}</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Annual CTC & Effective From --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Annual CTC --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <label class="block text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-3">Annual CTC (Cost to Company)</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[16px] font-semibold text-white/30">₹</span>
                <input type="number" x-model.number="annualCtc" min="0" step="1000"
                       class="w-full pl-10 pr-4 py-3.5 rounded-xl bg-white/[0.06] border border-white/[0.07] text-[20px] font-bold text-white/85 placeholder-white/20 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors tabular-nums"
                       placeholder="0"/>
            </div>
            <p class="text-[12px] text-white/30 mt-2">
                Monthly: <span class="text-white/50 font-medium tabular-nums" x-text="formatINR(Math.round(annualCtc / 12))"></span>
            </p>
            <template x-if="errors.annual_ctc">
                <p class="text-[12px] text-red-400 mt-1" x-text="errors.annual_ctc[0]"></p>
            </template>
        </div>

        {{-- Effective From --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <label class="block text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-3">Effective From</label>
            <input type="date" x-model="effectiveFrom"
                   class="w-full px-4 py-3.5 rounded-xl bg-white/[0.06] border border-white/[0.07] text-[15px] font-medium text-white/75 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
            <p class="text-[12px] text-white/30 mt-2">Date from which this salary structure becomes active</p>
            <template x-if="errors.effective_from">
                <p class="text-[12px] text-red-400 mt-1" x-text="errors.effective_from[0]"></p>
            </template>
        </div>
    </div>

    {{-- Component Breakdown --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">Salary Component Breakdown</h2>
                <p class="text-[12px] text-white/35 mt-0.5">Define each component of the salary structure</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="autoDistribute()"
                        :disabled="!annualCtc || annualCtc <= 0"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20 text-[12px] font-medium text-amber-400 hover:bg-amber-500/15 transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                        title="Auto-distribute: Basic 40%, HRA 20%, Special Allowance remaining">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Auto-distribute
                </button>
                <button @click="addRow()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg prod-bg text-white text-[12px] font-semibold hover:opacity-90 transition-opacity">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Component
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest w-[35%]">Component</th>
                        <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest w-[12%]">Type</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest w-[20%]">Monthly Amount</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest w-[20%]">Annual Amount</th>
                        <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest w-[8%]"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    <template x-for="(row, index) in componentRows" :key="index">
                        <tr class="hover:bg-white/[0.02] transition-colors">
                            {{-- Component Dropdown --}}
                            <td class="px-5 py-3">
                                <select x-model="row.component_id"
                                        class="w-full px-3 py-2 rounded-lg bg-[#1a1a2e] border border-white/[0.1] text-[13px] text-white/80 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 cursor-pointer"
                                        style="color-scheme: dark;">
                                    <option value="" style="background:#1a1a2e;color:#999">Select component...</option>
                                    @foreach($components as $comp)
                                        <option value="{{ $comp->id }}" style="background:#1a1a2e;color:#ddd">{{ $comp->name }} ({{ $comp->code }})</option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Type Badge --}}
                            <td class="px-5 py-3 text-center">
                                <template x-if="getComponentType(row.component_id) === 'earning'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20">Earning</span>
                                </template>
                                <template x-if="getComponentType(row.component_id) === 'deduction'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold text-red-400 bg-red-500/10 border border-red-500/20">Deduction</span>
                                </template>
                                <template x-if="getComponentType(row.component_id) === 'employer_contribution'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold text-blue-400 bg-blue-500/10 border border-blue-500/20">Employer</span>
                                </template>
                            </td>

                            {{-- Monthly Amount --}}
                            <td class="px-5 py-3">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-white/25">₹</span>
                                    <input type="number" x-model.number="row.monthly_amount" @input="updateMonthly(index)"
                                           min="0" step="100"
                                           class="w-full pl-8 pr-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] text-white/80 text-right font-medium tabular-nums focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
                                </div>
                            </td>

                            {{-- Annual Amount --}}
                            <td class="px-5 py-3">
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[12px] text-white/25">₹</span>
                                    <input type="number" x-model.number="row.annual_amount" @input="updateAnnual(index)"
                                           min="0" step="1000"
                                           class="w-full pl-8 pr-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] text-white/80 text-right font-medium tabular-nums focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
                                </div>
                            </td>

                            {{-- Remove --}}
                            <td class="px-5 py-3 text-center">
                                <button @click="removeRow(index)"
                                        class="p-1.5 rounded-lg hover:bg-red-500/10 text-white/25 hover:text-red-400 transition-colors"
                                        title="Remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Empty Components State --}}
        <template x-if="componentRows.length === 0">
            <div class="px-5 py-12 text-center border-t border-white/[0.04]">
                <p class="text-[13px] text-white/30">No components added yet.</p>
                <p class="text-[11px] text-white/20 mt-1">Click "Add Component" or "Auto-distribute" to get started.</p>
            </div>
        </template>

        {{-- Validation Errors for components --}}
        <template x-if="errors.components">
            <div class="px-5 py-3 bg-red-500/5 border-t border-red-500/10">
                <p class="text-[12px] text-red-400" x-text="errors.components[0]"></p>
            </div>
        </template>
    </div>

    {{-- Summary Section --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
        <h3 class="text-[14px] font-semibold text-white/85 mb-4">Summary</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Earnings --}}
            <div class="bg-white/[0.03] rounded-lg p-4 border border-white/[0.05]">
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Total Earnings</p>
                <p class="text-[20px] font-bold text-emerald-400/80 mt-1 tabular-nums" x-text="formatINR(totalEarningsMonthly)"></p>
                <p class="text-[11px] text-white/25 mt-0.5">per month</p>
            </div>

            {{-- Total Deductions --}}
            <div class="bg-white/[0.03] rounded-lg p-4 border border-white/[0.05]">
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Total Deductions</p>
                <p class="text-[20px] font-bold text-red-400/80 mt-1 tabular-nums" x-text="formatINR(totalDeductionsMonthly)"></p>
                <p class="text-[11px] text-white/25 mt-0.5">per month</p>
            </div>

            {{-- Net Monthly --}}
            <div class="bg-white/[0.03] rounded-lg p-4 border border-cyan-500/10">
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Net Monthly Pay</p>
                <p class="text-[20px] font-bold prod-text mt-1 tabular-nums" x-text="formatINR(netMonthlyPay)"></p>
                <p class="text-[11px] text-white/25 mt-0.5">take-home</p>
            </div>

            {{-- Annual CTC Comparison --}}
            <div class="bg-white/[0.03] rounded-lg p-4 border border-white/[0.05]">
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Annual CTC</p>
                <p class="text-[20px] font-bold text-white/80 mt-1 tabular-nums" x-text="formatINR(annualCtc)"></p>
                <p class="text-[11px] mt-0.5"
                   :class="Math.abs(totalEarningsAnnual - annualCtc) < 100 ? 'text-emerald-400/60' : 'text-amber-400/60'"
                   x-text="Math.abs(totalEarningsAnnual - annualCtc) < 100 ? 'Components match CTC' : 'Components total: ' + formatINR(totalEarningsAnnual - totalDeductionsAnnual)"></p>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <a href="{{ route('hr.payroll.salary-structures') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] font-medium text-white/50 hover:bg-white/[0.10] hover:text-white/70 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Salary Structures
        </a>
        <button @click="save()" :disabled="saving || !annualCtc || !effectiveFrom"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/10 disabled:opacity-40 disabled:cursor-not-allowed">
            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span x-text="saving ? 'Saving...' : 'Save Salary Structure'"></span>
        </button>
    </div>

</div>

</x-layouts.hr>
