<x-layouts.hr title="Salary Structures" currentView="payroll">

@php
    $departments = $employees->pluck('department')->filter()->unique()->sort()->values();
    $withoutStructure = $employees->filter(fn($e) => !$e->currentSalaryStructure)->count();
@endphp

<div class="p-5 lg:p-7 space-y-6" x-data="{
    search: '',
    departmentFilter: '',

    formatINR(amount) {
        if (!amount && amount !== 0) return '--';
        return new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    },

    shouldShow(name, department) {
        let show = true;
        if (this.search) {
            show = name.toLowerCase().includes(this.search.toLowerCase());
        }
        if (show && this.departmentFilter) {
            show = department === this.departmentFilter;
        }
        return show;
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <div class="flex items-center gap-2 text-[12px] text-white/35 mb-2">
                <a href="{{ route('hr.payroll.index') }}" class="hover:text-white/55 transition-colors">Payroll</a>
                <svg class="w-3.5 h-3.5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-white/50">Salary Structures</span>
            </div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Salary Structures</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Manage employee CTC and salary breakdowns</p>
        </div>
        <a href="{{ route('hr.payroll.salary-components') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-white/65 text-[13px] font-medium hover:bg-white/[0.10] hover:text-white/80 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Manage Components
        </a>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-[20px] font-bold text-white/85">{{ $employees->count() }}</p>
                <p class="text-[11px] text-white/35">Total employees</p>
            </div>
        </div>
        @if($withoutStructure > 0)
        <div class="bg-[#17172A] border border-amber-500/20 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div>
                <p class="text-[20px] font-bold text-amber-400">{{ $withoutStructure }}</p>
                <p class="text-[11px] text-white/35">Without salary structure</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        {{-- Search --}}
        <div class="relative flex-1 max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="search" placeholder="Search by name..."
                   class="w-full pl-9 pr-4 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-sm text-white/70 placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
        </div>

        {{-- Department Filter --}}
        <select x-model="departmentFilter"
                class="px-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-sm text-white/65 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none cursor-pointer min-w-[160px]">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}">{{ $dept }}</option>
            @endforeach
        </select>
    </div>

    {{-- Employees Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06]">
            <h2 class="text-[14px] font-semibold text-white/85">Employee Salary Structures</h2>
            <p class="text-[12px] text-white/35 mt-0.5">Click Edit to configure an employee's CTC and salary breakdown</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Employee</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Employee ID</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Department</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Designation</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Annual CTC</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Monthly Gross</th>
                        <th class="px-5 py-3 text-left text-[10px] font-semibold text-white/30 uppercase tracking-widest">Effective From</th>
                        <th class="px-5 py-3 text-right text-[10px] font-semibold text-white/30 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04]">
                    @forelse($employees as $emp)
                        <tr class="hover:bg-white/[0.02] transition-colors"
                            x-show="shouldShow('{{ addslashes($emp->user->name) }}', '{{ addslashes($emp->department) }}')"
                            x-cloak>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @php
                                        $initials = collect(explode(' ', $emp->user->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                                    @endphp
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500/30 to-blue-500/30 flex items-center justify-center shrink-0 border border-white/[0.08]">
                                        <span class="text-[11px] font-semibold text-white/70">{{ $initials }}</span>
                                    </div>
                                    <span class="text-[13px] font-medium text-white/80">{{ $emp->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[12px] font-mono text-white/50">{{ $emp->employee_id }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[13px] text-white/60">{{ $emp->department ?? '--' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-[13px] text-white/60">{{ $emp->designation ?? '--' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($emp->currentSalaryStructure && $emp->currentSalaryStructure->annual_ctc)
                                    <span class="text-[13px] font-semibold text-emerald-400/80 tabular-nums">
                                        {{ '₹' . number_format($emp->currentSalaryStructure->annual_ctc) }}
                                    </span>
                                @else
                                    <span class="text-[12px] font-medium text-red-400/70">Not Set</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if($emp->currentSalaryStructure && $emp->currentSalaryStructure->annual_ctc)
                                    <span class="text-[13px] text-white/65 tabular-nums">
                                        {{ '₹' . number_format(round($emp->currentSalaryStructure->annual_ctc / 12)) }}
                                    </span>
                                @else
                                    <span class="text-[12px] text-white/25">--</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($emp->currentSalaryStructure && $emp->currentSalaryStructure->effective_from)
                                    <span class="text-[13px] text-white/55">
                                        {{ \Carbon\Carbon::parse($emp->currentSalaryStructure->effective_from)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-[12px] text-white/25">--</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('hr.payroll.edit-salary-structure', $emp) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/[0.06] border border-white/[0.07] text-[12px] font-medium text-white/55 hover:bg-white/[0.10] hover:text-white/80 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="w-14 h-14 rounded-2xl bg-white/[0.04] flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-7 h-7 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <p class="text-[14px] font-medium text-white/40">No employees found</p>
                                <p class="text-[12px] text-white/25 mt-1">Add employees in the People section first</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</x-layouts.hr>
