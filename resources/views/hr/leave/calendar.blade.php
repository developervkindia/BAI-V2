<x-layouts.hr title="Leave Calendar" currentView="leave-calendar">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    month: {{ $month }},
    year: {{ $year }},
    leaveRequests: @js($leaveRequests->map(fn($r) => [
        'id' => $r->id,
        'employee_name' => $r->employeeProfile?->user?->name ?? 'Unknown',
        'employee_initials' => strtoupper(collect(explode(' ', $r->employeeProfile?->user?->name ?? 'U'))->map(fn($w) => substr($w, 0, 1))->take(2)->join('')),
        'type_name' => $r->leaveType->name ?? 'Leave',
        'type_color' => $r->leaveType->color ?? '#06b6d4',
        'start_date' => $r->start_date?->format('Y-m-d'),
        'end_date' => $r->end_date?->format('Y-m-d'),
        'days' => $r->days,
        'reason' => $r->reason,
        'status' => $r->status,
        'is_half_day' => $r->is_half_day,
    ])),
    selectedEntry: null,
    showDetail: false,

    get monthName() {
        return new Date(this.year, this.month - 1).toLocaleString('en-US', { month: 'long' });
    },

    get calendarDays() {
        const firstDay = new Date(this.year, this.month - 1, 1);
        const lastDay = new Date(this.year, this.month, 0);
        const startPad = firstDay.getDay(); // 0=Sun
        const totalDays = lastDay.getDate();
        const days = [];

        // Padding for previous month
        for (let i = 0; i < startPad; i++) {
            days.push({ date: null, day: null, isCurrentMonth: false });
        }
        // Current month days
        for (let d = 1; d <= totalDays; d++) {
            const dateStr = `${this.year}-${String(this.month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const isToday = dateStr === new Date().toISOString().split('T')[0];
            const isWeekend = new Date(this.year, this.month - 1, d).getDay() === 0 || new Date(this.year, this.month - 1, d).getDay() === 6;
            days.push({ date: dateStr, day: d, isCurrentMonth: true, isToday, isWeekend });
        }
        // Padding for next month
        const remaining = 7 - (days.length % 7);
        if (remaining < 7) {
            for (let i = 0; i < remaining; i++) {
                days.push({ date: null, day: null, isCurrentMonth: false });
            }
        }
        return days;
    },

    leavesOnDate(dateStr) {
        if (!dateStr) return [];
        return this.leaveRequests.filter(r => {
            if (r.status !== 'approved' && r.status !== 'pending') return false;
            return dateStr >= r.start_date && dateStr <= r.end_date;
        });
    },

    get uniqueLeaveTypes() {
        const types = new Map();
        this.leaveRequests.forEach(r => {
            if (!types.has(r.type_name)) {
                types.set(r.type_name, r.type_color);
            }
        });
        return Array.from(types, ([name, color]) => ({ name, color }));
    },

    prevMonth() {
        const url = new URL(window.location);
        let m = this.month - 1;
        let y = this.year;
        if (m < 1) { m = 12; y--; }
        url.searchParams.set('month', m);
        url.searchParams.set('year', y);
        window.location.href = url.toString();
    },

    nextMonth() {
        const url = new URL(window.location);
        let m = this.month + 1;
        let y = this.year;
        if (m > 12) { m = 1; y++; }
        url.searchParams.set('month', m);
        url.searchParams.set('year', y);
        window.location.href = url.toString();
    },

    showEntryDetail(entry) {
        this.selectedEntry = entry;
        this.showDetail = true;
    },

    closeDetail() {
        this.showDetail = false;
        this.selectedEntry = null;
    },
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Leave Calendar</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Team leave overview for <span x-text="monthName + ' ' + year"></span></p>
        </div>
    </div>

    {{-- Month Navigation --}}
    <div class="flex items-center justify-between bg-[#17172A] border border-white/[0.07] rounded-xl px-5 py-3">
        <button @click="prevMonth()"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/80 hover:bg-white/[0.06] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Previous
        </button>

        <h2 class="text-[16px] font-bold text-white/85">
            <span x-text="monthName"></span> <span x-text="year"></span>
        </h2>

        <button @click="nextMonth()"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/80 hover:bg-white/[0.06] transition-colors">
            Next
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Legend --}}
    <div x-show="uniqueLeaveTypes.length > 0" class="flex items-center gap-4 flex-wrap">
        <span class="text-[11px] font-semibold text-white/30 uppercase tracking-wider">Legend:</span>
        <template x-for="type in uniqueLeaveTypes" :key="type.name">
            <div class="flex items-center gap-1.5">
                <div class="w-2.5 h-2.5 rounded-full" :style="'background-color:' + type.color"></div>
                <span class="text-[12px] text-white/50" x-text="type.name"></span>
            </div>
        </template>
    </div>

    {{-- Calendar Grid --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">

        {{-- Day Headers --}}
        <div class="grid grid-cols-7 border-b border-white/[0.06]">
            <template x-for="dayName in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="dayName">
                <div class="px-2 py-3 text-center">
                    <span class="text-[10px] font-semibold text-white/30 uppercase tracking-wider" x-text="dayName"></span>
                </div>
            </template>
        </div>

        {{-- Day Cells --}}
        <div class="grid grid-cols-7">
            <template x-for="(cell, ci) in calendarDays" :key="ci">
                <div class="min-h-[100px] lg:min-h-[120px] border-b border-r border-white/[0.04] p-1.5 transition-colors"
                     :class="{
                         'bg-white/[0.01]': cell.isCurrentMonth && !cell.isWeekend,
                         'bg-white/[0.015]': cell.isToday,
                         'opacity-30': !cell.isCurrentMonth,
                         'bg-transparent': cell.isWeekend && cell.isCurrentMonth,
                     }">

                    {{-- Day Number --}}
                    <div class="flex items-center justify-between mb-1" x-show="cell.day">
                        <span class="text-[12px] font-medium tabular-nums w-6 h-6 flex items-center justify-center rounded-full"
                              :class="cell.isToday ? 'prod-bg text-white font-bold' : (cell.isWeekend ? 'text-white/20' : 'text-white/45')"
                              x-text="cell.day"></span>
                    </div>

                    {{-- Leave Entries --}}
                    <div class="space-y-0.5" x-show="cell.date">
                        <template x-for="(entry, ei) in leavesOnDate(cell.date).slice(0, 3)" :key="entry.id + '-' + ei">
                            <button @click="showEntryDetail(entry)"
                                    class="w-full flex items-center gap-1 px-1.5 py-0.5 rounded text-left hover:bg-white/[0.06] transition-colors group cursor-pointer">
                                <div class="w-1.5 h-1.5 rounded-full shrink-0" :style="'background-color:' + entry.type_color"></div>
                                <span class="text-[10px] text-white/55 truncate group-hover:text-white/75 leading-tight" x-text="entry.employee_name"></span>
                            </button>
                        </template>
                        <template x-if="cell.date && leavesOnDate(cell.date).length > 3">
                            <div class="px-1.5">
                                <span class="text-[9px] text-white/30 font-medium" x-text="'+' + (leavesOnDate(cell.date).length - 3) + ' more'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div x-show="showDetail" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="closeDetail()">
        <div class="absolute inset-0 bg-black/60" @click="closeDetail()"></div>
        <div class="relative bg-[#1A1A2E] border border-white/[0.1] rounded-xl w-full max-w-md shadow-2xl" x-show="showDetail" x-transition>
            <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <h3 class="text-[15px] font-semibold text-white/85">Leave Details</h3>
                <button @click="closeDetail()" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/60 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4" x-show="selectedEntry">
                {{-- Employee --}}
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-[12px] font-bold shrink-0"
                         :style="'background-color:' + selectedEntry?.type_color + '20; color:' + selectedEntry?.type_color"
                         x-text="selectedEntry?.employee_initials"></div>
                    <div>
                        <p class="text-[14px] font-semibold text-white/85" x-text="selectedEntry?.employee_name"></p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <div class="w-2 h-2 rounded-full" :style="'background-color:' + selectedEntry?.type_color"></div>
                            <span class="text-[12px] text-white/50" x-text="selectedEntry?.type_name"></span>
                        </div>
                    </div>
                </div>

                {{-- Details --}}
                <div class="space-y-3 bg-white/[0.03] rounded-lg p-4">
                    <div class="flex justify-between">
                        <span class="text-[12px] text-white/40">Period</span>
                        <span class="text-[12px] text-white/70 font-medium">
                            <span x-text="selectedEntry?.start_date"></span>
                            <span x-show="selectedEntry?.start_date !== selectedEntry?.end_date"> to <span x-text="selectedEntry?.end_date"></span></span>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[12px] text-white/40">Duration</span>
                        <span class="text-[12px] text-white/70 font-medium">
                            <span x-text="selectedEntry?.days"></span> day<span x-show="selectedEntry?.days !== 1">s</span>
                            <span x-show="selectedEntry?.is_half_day" class="text-violet-400/70 text-[10px] ml-1">(Half Day)</span>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[12px] text-white/40">Status</span>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full capitalize"
                              :class="{
                                  'text-amber-400 bg-amber-500/10': selectedEntry?.status === 'pending',
                                  'text-emerald-400 bg-emerald-500/10': selectedEntry?.status === 'approved',
                                  'text-red-400 bg-red-500/10': selectedEntry?.status === 'rejected',
                                  'text-white/40 bg-white/[0.05]': selectedEntry?.status === 'cancelled',
                              }"
                              x-text="selectedEntry?.status"></span>
                    </div>
                </div>

                {{-- Reason --}}
                <div x-show="selectedEntry?.reason">
                    <p class="text-[11px] font-semibold text-white/30 uppercase tracking-wider mb-1.5">Reason</p>
                    <p class="text-[13px] text-white/60 leading-relaxed" x-text="selectedEntry?.reason"></p>
                </div>
            </div>
        </div>
    </div>

</div>

</x-layouts.hr>
