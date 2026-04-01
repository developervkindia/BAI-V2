<x-layouts.hr title="Payslip" currentView="payroll">

@php
    $components = is_string($payrollEntry->components) ? json_decode($payrollEntry->components, true) : ($payrollEntry->components ?? []);
    $earnings = collect($components)->where('type', 'earning')->values();
    $deductions = collect($components)->where('type', 'deduction')->values();
    $employeeName = $payrollEntry->employeeProfile->user->name ?? ($payrollEntry->employeeProfile->first_name . ' ' . $payrollEntry->employeeProfile->last_name);
    $employeeId = $payrollEntry->employeeProfile->employee_id ?? ('EMP-' . $payrollEntry->employeeProfile->id);
    $department = $payrollEntry->employeeProfile->department->name ?? 'N/A';
    $designation = $payrollEntry->employeeProfile->designation ?? $payrollEntry->employeeProfile->job_title ?? 'N/A';
    $monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    $period = ($monthNames[$payrollEntry->payrollRun->month] ?? $payrollEntry->payrollRun->month) . ' ' . $payrollEntry->payrollRun->year;
@endphp

<div class="p-5 lg:p-7 space-y-6" x-data="{
    downloading: false,

    printPayslip() {
        window.print();
    },

    async downloadPdf() {
        this.downloading = true;
        try {
            const res = await fetch('/api/hr/payroll-entries/{{ $payrollEntry->id }}/pdf', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/pdf',
                },
            });
            if (res.ok) {
                const blob = await res.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'payslip-{{ Str::slug($employeeName) }}-{{ $payrollEntry->payrollRun->month }}-{{ $payrollEntry->payrollRun->year }}.pdf';
                a.click();
                window.URL.revokeObjectURL(url);
            } else {
                alert('PDF download is not available yet.');
            }
        } catch (e) {
            alert('PDF download is not available yet.');
        }
        this.downloading = false;
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4 print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('hr.payroll.show-run', $payrollEntry->payrollRun) }}"
               class="p-2 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/70 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Payslip</h1>
                <p class="text-[13px] text-white/40 mt-0.5">{{ $period }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="printPayslip()"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-white/60 text-[13px] font-medium hover:bg-white/[0.1] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print
            </button>
            <button @click="downloadPdf()"
                    :disabled="downloading"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed">
                <template x-if="!downloading">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </template>
                <template x-if="downloading">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </template>
                <span x-text="downloading ? 'Downloading...' : 'Download PDF'"></span>
            </button>
        </div>
    </div>

    {{-- Payslip Card --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden print:bg-white print:border-gray-200 print:rounded-none print:shadow-none" id="payslip">

        {{-- Payslip Header --}}
        <div class="px-6 py-5 border-b border-white/[0.06] print:border-gray-200 bg-gradient-to-r from-cyan-500/5 to-transparent">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <h2 class="text-[18px] font-bold text-white/90 print:text-gray-900">{{ config('app.name', 'BAI Technologies') }}</h2>
                    <p class="text-[12px] text-white/35 print:text-gray-500 mt-1">Salary Slip for the month of {{ $period }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-semibold capitalize
                        @if($payrollEntry->payrollRun->status === 'paid') text-emerald-400 bg-emerald-500/10 print:text-emerald-700 print:bg-emerald-50
                        @elseif($payrollEntry->payrollRun->status === 'finalized') text-green-400 bg-green-500/10 print:text-green-700 print:bg-green-50
                        @else text-amber-400 bg-amber-500/10 print:text-amber-700 print:bg-amber-50
                        @endif">
                        {{ $payrollEntry->payrollRun->status }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Employee Info --}}
        <div class="px-6 py-5 border-b border-white/[0.06] print:border-gray-200">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
                <div>
                    <p class="text-[10px] font-semibold text-white/25 print:text-gray-400 uppercase tracking-widest">Employee Name</p>
                    <p class="text-[14px] font-semibold text-white/85 print:text-gray-900 mt-1">{{ $employeeName }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-white/25 print:text-gray-400 uppercase tracking-widest">Employee ID</p>
                    <p class="text-[14px] font-medium text-white/65 print:text-gray-700 mt-1">{{ $employeeId }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-white/25 print:text-gray-400 uppercase tracking-widest">Department</p>
                    <p class="text-[14px] font-medium text-white/65 print:text-gray-700 mt-1">{{ $department }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-semibold text-white/25 print:text-gray-400 uppercase tracking-widest">Designation</p>
                    <p class="text-[14px] font-medium text-white/65 print:text-gray-700 mt-1">{{ $designation }}</p>
                </div>
            </div>
        </div>

        {{-- Attendance Info --}}
        <div class="px-6 py-4 border-b border-white/[0.06] print:border-gray-200 bg-white/[0.01]">
            <div class="grid grid-cols-3 gap-5">
                <div class="text-center">
                    <p class="text-[10px] font-semibold text-white/25 print:text-gray-400 uppercase tracking-widest">Working Days</p>
                    <p class="text-[20px] font-bold text-white/80 print:text-gray-800 mt-1">{{ $payrollEntry->working_days ?? '--' }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] font-semibold text-white/25 print:text-gray-400 uppercase tracking-widest">Days Present</p>
                    <p class="text-[20px] font-bold text-white/80 print:text-gray-800 mt-1">{{ $payrollEntry->days_present ?? '--' }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] font-semibold text-white/25 print:text-gray-400 uppercase tracking-widest">LOP Days</p>
                    <p class="text-[20px] font-bold mt-1 {{ ($payrollEntry->lop_days ?? 0) > 0 ? 'text-amber-400/90' : 'text-white/80 print:text-gray-800' }}">{{ $payrollEntry->lop_days ?? '0' }}</p>
                </div>
            </div>
        </div>

        {{-- Earnings & Deductions Side by Side --}}
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-white/[0.06] print:divide-gray-200">

            {{-- Earnings --}}
            <div class="p-6">
                <h3 class="text-[12px] font-semibold text-white/40 print:text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                    Earnings
                </h3>
                <div class="space-y-3">
                    @forelse($earnings as $component)
                        <div class="flex items-center justify-between">
                            <span class="text-[13px] text-white/65 print:text-gray-600">{{ $component['name'] ?? $component['code'] ?? 'Component' }}</span>
                            <span class="text-[13px] text-white/80 print:text-gray-800 font-medium tabular-nums">{{ '₹' . number_format($component['amount'] ?? 0) }}</span>
                        </div>
                    @empty
                        <p class="text-[13px] text-white/30 print:text-gray-400">No earning components</p>
                    @endforelse
                </div>
                <div class="mt-4 pt-3 border-t border-white/[0.06] print:border-gray-200 flex items-center justify-between">
                    <span class="text-[13px] font-semibold text-white/70 print:text-gray-700">Total Earnings</span>
                    <span class="text-[14px] font-bold text-emerald-400/90 print:text-emerald-700 tabular-nums">{{ '₹' . number_format($payrollEntry->gross_earnings) }}</span>
                </div>
            </div>

            {{-- Deductions --}}
            <div class="p-6">
                <h3 class="text-[12px] font-semibold text-white/40 print:text-gray-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-red-400"></div>
                    Deductions
                </h3>
                <div class="space-y-3">
                    @forelse($deductions as $component)
                        <div class="flex items-center justify-between">
                            <span class="text-[13px] text-white/65 print:text-gray-600">{{ $component['name'] ?? $component['code'] ?? 'Component' }}</span>
                            <span class="text-[13px] text-red-400/80 print:text-red-600 font-medium tabular-nums">{{ '₹' . number_format($component['amount'] ?? 0) }}</span>
                        </div>
                    @empty
                        <p class="text-[13px] text-white/30 print:text-gray-400">No deduction components</p>
                    @endforelse
                </div>
                <div class="mt-4 pt-3 border-t border-white/[0.06] print:border-gray-200 flex items-center justify-between">
                    <span class="text-[13px] font-semibold text-white/70 print:text-gray-700">Total Deductions</span>
                    <span class="text-[14px] font-bold text-red-400/90 print:text-red-700 tabular-nums">{{ '₹' . number_format($payrollEntry->total_deductions) }}</span>
                </div>
            </div>
        </div>

        {{-- Net Pay Summary --}}
        <div class="px-6 py-6 border-t border-white/[0.06] print:border-gray-200 bg-gradient-to-r from-cyan-500/5 via-emerald-500/5 to-transparent">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-white/30 print:text-gray-400 uppercase tracking-widest">Net Pay</p>
                    <p class="text-[12px] text-white/35 print:text-gray-500 mt-0.5">Gross Earnings minus Total Deductions</p>
                </div>
                <div class="text-right">
                    <p class="text-[28px] font-bold text-emerald-400 print:text-emerald-700 tabular-nums">{{ '₹' . number_format($payrollEntry->net_pay) }}</p>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-3 border-t border-white/[0.04] print:border-gray-100">
            <p class="text-[10px] text-white/20 print:text-gray-400 text-center">
                This is a system-generated payslip. For any queries, please contact the HR department.
            </p>
        </div>
    </div>

</div>

{{-- Print Styles --}}
<style>
    @media print {
        body { background: white !important; }
        aside, header, .print\\:hidden { display: none !important; }
        .lg\\:pl-\\[220px\\] { padding-left: 0 !important; }
        #payslip { break-inside: avoid; }
    }
</style>

</x-layouts.hr>
