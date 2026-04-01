<x-layouts.hr title="Salary Components" currentView="payroll">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    components: @js($components),
    showModal: false,
    editing: null,
    saving: false,
    deleting: null,
    form: {
        name: '',
        code: '',
        type: 'earning',
        calculation_type: 'fixed',
        percentage_of: '',
        is_taxable: false,
        is_statutory: false,
        sort_order: 0,
    },

    openAdd() {
        this.editing = null;
        this.form = { name: '', code: '', type: 'earning', calculation_type: 'fixed', percentage_of: '', is_taxable: false, is_statutory: false, sort_order: 0 };
        this.showModal = true;
    },

    openEdit(comp) {
        this.editing = comp;
        this.form = {
            name: comp.name,
            code: comp.code,
            type: comp.type,
            calculation_type: comp.calculation_type,
            percentage_of: comp.percentage_of || '',
            is_taxable: !!comp.is_taxable,
            is_statutory: !!comp.is_statutory,
            sort_order: comp.sort_order || 0,
        };
        this.showModal = true;
    },

    async save() {
        this.saving = true;
        try {
            const url = this.editing
                ? '/api/hr/salary-components/' + this.editing.id
                : '/api/hr/salary-components';
            const method = this.editing ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(this.form),
            });

            if (res.ok) {
                window.location.reload();
            } else {
                const data = await res.json();
                alert(data.message || 'Failed to save component.');
            }
        } catch (e) {
            alert('Network error. Please try again.');
        }
        this.saving = false;
    },

    deleteComponent(id) {
        this.$dispatch('confirm-modal', {
            title: 'Delete Component',
            message: 'Are you sure you want to delete this component? This cannot be undone.',
            confirmLabel: 'Delete',
            variant: 'danger',
            onConfirm: async () => {
                this.deleting = id;
                try {
                    const res = await fetch('/api/hr/salary-components/' + id, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        },
                    });
                    if (res.ok) {
                        window.location.reload();
                    } else {
                        alert('Failed to delete component.');
                    }
                } catch (e) {
                    alert('Network error.');
                }
                this.deleting = null;
            }
        });
    },

    typeBadge(type) {
        const map = {
            earning:               'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
            deduction:             'text-red-400 bg-red-500/10 border-red-500/20',
            employer_contribution: 'text-blue-400 bg-blue-500/10 border-blue-500/20',
        };
        return map[type] || 'text-white/45 bg-white/[0.06] border-white/[0.08]';
    },

    typeLabel(type) {
        const map = { earning: 'Earning', deduction: 'Deduction', employer_contribution: 'Employer Contribution' };
        return map[type] || type;
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-[12px] text-white/35 mb-2">
                <a href="{{ route('hr.payroll.index') }}" class="hover:text-white/55 transition-colors">Payroll</a>
                <svg class="w-3.5 h-3.5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-white/50">Salary Components</span>
            </div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Salary Components</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Configure earning and deduction components</p>
        </div>
        <button @click="openAdd()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Component
        </button>
    </div>

    {{-- Components Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
            <div>
                <h2 class="text-[14px] font-semibold text-white/85">All Components</h2>
                <p class="text-[12px] text-white/35 mt-0.5">Earnings, deductions, and employer contributions</p>
            </div>
            <span class="text-[11px] font-semibold prod-text prod-bg-muted px-2.5 py-1 rounded-full"
                  x-text="components.length + ' components'"></span>
        </div>

        <div class="overflow-x-auto">
            <template x-if="components.length > 0">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/[0.06]">
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Name</th>
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Code</th>
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Type</th>
                            <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Calculation</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Taxable</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Statutory</th>
                            <th class="px-5 py-3 text-center text-[10px] font-semibold text-white/30 uppercase tracking-widest">Order</th>
                            <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        <template x-for="comp in components" :key="comp.id">
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-5 py-3.5">
                                    <span class="text-[13px] font-medium text-white/80" x-text="comp.name"></span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-[12px] font-mono text-white/50 bg-white/[0.04] px-2 py-0.5 rounded" x-text="comp.code"></span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold capitalize border"
                                          :class="typeBadge(comp.type)"
                                          x-text="typeLabel(comp.type)"></span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-[13px] text-white/65" x-text="comp.calculation_type === 'percentage' ? comp.percentage_of ? comp.percentage_of + ' (%)' : 'Percentage' : 'Fixed'"></span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <template x-if="comp.is_taxable">
                                        <svg class="w-4 h-4 text-emerald-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <template x-if="!comp.is_taxable">
                                        <svg class="w-4 h-4 text-white/20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </template>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <template x-if="comp.is_statutory">
                                        <svg class="w-4 h-4 text-emerald-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </template>
                                    <template x-if="!comp.is_statutory">
                                        <svg class="w-4 h-4 text-white/20 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </template>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="text-[13px] text-white/50 tabular-nums" x-text="comp.sort_order"></span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEdit(comp)"
                                                class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/70 transition-colors"
                                                title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="deleteComponent(comp.id)"
                                                :disabled="deleting === comp.id"
                                                class="p-1.5 rounded-lg hover:bg-red-500/10 text-white/25 hover:text-red-400 transition-colors"
                                                title="Delete">
                                            <svg x-show="deleting !== comp.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <svg x-show="deleting === comp.id" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </template>
        </div>

        {{-- Empty State --}}
        <template x-if="components.length === 0">
            <div class="px-5 py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-white/[0.04] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <p class="text-[14px] font-medium text-white/40">No salary components configured</p>
                <p class="text-[12px] text-white/25 mt-1">Add earning and deduction components to build salary structures</p>
                <button @click="openAdd()"
                        class="inline-flex items-center gap-2 mt-5 px-4 py-2 rounded-lg prod-bg text-white text-[12px] font-semibold hover:opacity-90 transition-opacity">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Component
                </button>
            </div>
        </template>
    </div>

    {{-- Add/Edit Modal --}}
    <template x-if="showModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition>
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false"></div>

            {{-- Modal Content --}}
            <div class="relative bg-[#17172A] border border-white/[0.10] rounded-2xl w-full max-w-lg shadow-2xl" @click.stop>
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                    <h3 class="text-[16px] font-semibold text-white/85" x-text="editing ? 'Edit Component' : 'Add Component'"></h3>
                    <button @click="showModal = false" class="p-1 rounded-lg hover:bg-white/[0.06] text-white/30 hover:text-white/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Form --}}
                <div class="p-6 space-y-4">
                    {{-- Name --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-1.5">Name</label>
                        <input type="text" x-model="form.name" placeholder="e.g. Basic Salary"
                               class="w-full px-3 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
                    </div>

                    {{-- Code --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-1.5">Code</label>
                        <input type="text" x-model="form.code" placeholder="e.g. BASIC"
                               class="w-full px-3 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] text-white/80 font-mono placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
                    </div>

                    {{-- Type + Calculation Type Row --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-1.5">Type</label>
                            <select x-model="form.type"
                                    class="w-full px-3 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none cursor-pointer">
                                <option value="earning">Earning</option>
                                <option value="deduction">Deduction</option>
                                <option value="employer_contribution">Employer Contribution</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-1.5">Calculation</label>
                            <select x-model="form.calculation_type"
                                    class="w-full px-3 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none cursor-pointer">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                    </div>

                    {{-- Percentage Of (conditional) --}}
                    <div x-show="form.calculation_type === 'percentage'" x-transition>
                        <label class="block text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-1.5">Percentage Of</label>
                        <input type="text" x-model="form.percentage_of" placeholder="e.g. BASIC"
                               class="w-full px-3 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] text-white/80 font-mono placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
                        <p class="text-[11px] text-white/25 mt-1">Enter the component code this is calculated against</p>
                    </div>

                    {{-- Toggles Row --}}
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Taxable --}}
                        <div class="flex items-center justify-between bg-white/[0.03] rounded-lg px-3 py-3 border border-white/[0.05]">
                            <span class="text-[12px] text-white/60">Taxable</span>
                            <button type="button" @click="form.is_taxable = !form.is_taxable"
                                    :class="form.is_taxable ? 'bg-cyan-500' : 'bg-white/[0.12]'"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out">
                                <span :class="form.is_taxable ? 'translate-x-4' : 'translate-x-0.5'"
                                      class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                            </button>
                        </div>
                        {{-- Statutory --}}
                        <div class="flex items-center justify-between bg-white/[0.03] rounded-lg px-3 py-3 border border-white/[0.05]">
                            <span class="text-[12px] text-white/60">Statutory</span>
                            <button type="button" @click="form.is_statutory = !form.is_statutory"
                                    :class="form.is_statutory ? 'bg-cyan-500' : 'bg-white/[0.12]'"
                                    class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out">
                                <span :class="form.is_statutory ? 'translate-x-4' : 'translate-x-0.5'"
                                      class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out mt-0.5"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Sort Order --}}
                    <div>
                        <label class="block text-[11px] font-semibold text-white/40 uppercase tracking-wider mb-1.5">Sort Order</label>
                        <input type="number" x-model.number="form.sort_order" min="0"
                               class="w-full px-3 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-end gap-3">
                    <button @click="showModal = false"
                            class="px-4 py-2 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/70 hover:bg-white/[0.06] transition-colors">
                        Cancel
                    </button>
                    <button @click="save()" :disabled="saving || !form.name || !form.code"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed">
                        <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="editing ? 'Update Component' : 'Add Component'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

</x-layouts.hr>
