<x-layouts.hr title="Team Attendance" currentView="team-attendance">
<div class="p-5 lg:p-6 space-y-6" x-data="teamAttendance()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Team Attendance</h1>
            <p class="text-[13px] text-white/45 mt-0.5">Monitor your direct reports' attendance at a glance</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Month Navigation --}}
            <button @click="prevMonth()" class="h-9 w-9 rounded-lg bg-white/[0.06] border border-white/[0.08] flex items-center justify-center text-white/50 hover:text-white/80 hover:bg-white/[0.1] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="h-9 px-4 rounded-lg bg-white/[0.06] border border-white/[0.08] flex items-center">
                <span class="text-[13px] font-medium text-white/70 min-w-[120px] text-center" x-text="monthYearLabel"></span>
            </div>
            <button @click="nextMonth()" class="h-9 w-9 rounded-lg bg-white/[0.06] border border-white/[0.08] flex items-center justify-center text-white/50 hover:text-white/80 hover:bg-white/[0.1] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-x-4 gap-y-1.5">
        <template x-for="item in legendItems" :key="item.label">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm text-[8px] font-bold flex items-center justify-center" :class="item.bgClass" x-text="item.code"></span>
                <span class="text-[11px] text-white/40" x-text="item.label"></span>
            </div>
        </template>
    </div>

    {{-- Grid --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="overflow-x-auto scrollbar-none">
            <table class="w-full min-w-[900px]">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="sticky left-0 z-10 bg-[#17172A] px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider min-w-[200px] border-r border-white/[0.06]">Employee</th>
                        <template x-for="d in daysInMonth" :key="d">
                            <th class="px-0 py-3 text-center min-w-[32px]">
                                <div class="text-[10px] font-medium" :class="isWeekend(d) ? 'text-white/20' : 'text-white/40'" x-text="d"></div>
                                <div class="text-[9px]" :class="isWeekend(d) ? 'text-white/15' : 'text-white/25'" x-text="dayLabel(d)"></div>
                            </th>
                        </template>
                        <th class="sticky right-0 z-10 bg-[#17172A] px-3 py-3 text-center text-[11px] font-semibold text-white/40 uppercase tracking-wider min-w-[130px] border-l border-white/[0.06]">Summary</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="emp in employees" :key="emp.id">
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.015] transition-colors">
                            <td class="sticky left-0 z-10 bg-[#17172A] px-4 py-2.5 border-r border-white/[0.06]">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full prod-bg-muted prod-text text-[10px] font-bold flex items-center justify-center shrink-0" x-text="emp.initials"></div>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-medium text-white/75 truncate" x-text="emp.name"></div>
                                        <div class="text-[11px] text-white/35 truncate" x-text="emp.designation || emp.department || ''"></div>
                                    </div>
                                </div>
                            </td>
                            <template x-for="d in daysInMonth" :key="'c' + emp.id + '-' + d">
                                <td class="px-0 py-2.5 text-center">
                                    <div class="mx-auto w-6 h-6 rounded-md flex items-center justify-center text-[9px] font-bold cursor-default transition-colors"
                                         :class="cellClass(emp.id, d)"
                                         :title="cellTooltip(emp.id, d)"
                                         x-text="cellCode(emp.id, d)">
                                    </div>
                                </td>
                            </template>
                            <td class="sticky right-0 z-10 bg-[#17172A] px-3 py-2.5 border-l border-white/[0.06]">
                                <div class="flex items-center justify-center gap-2 text-[10px]">
                                    <span class="text-emerald-400 font-semibold" :title="'Present'" x-text="empSummary(emp.id).present + 'P'"></span>
                                    <span class="text-white/15">|</span>
                                    <span class="text-red-400 font-semibold" :title="'Absent'" x-text="empSummary(emp.id).absent + 'A'"></span>
                                    <span class="text-white/15">|</span>
                                    <span class="text-orange-400 font-semibold" :title="'Late'" x-text="empSummary(emp.id).late + 'L'"></span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="employees.length === 0">
                        <tr>
                            <td :colspan="daysInMonth.length + 2" class="px-4 py-12 text-center text-[13px] text-white/30">No direct reports found</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function teamAttendance() {
    return {
        selectedMonth: {{ $month }},
        selectedYear: {{ $year }},
        employees: [],
        attendanceGrid: {},

        legendItems: [
            { label: 'Present', code: 'P', bgClass: 'bg-emerald-500/20 text-emerald-400' },
            { label: 'Absent', code: 'A', bgClass: 'bg-red-500/20 text-red-400' },
            { label: 'Half Day', code: 'H', bgClass: 'bg-amber-500/20 text-amber-400' },
            { label: 'Late', code: 'LT', bgClass: 'bg-orange-500/20 text-orange-400' },
            { label: 'Leave', code: 'L', bgClass: 'bg-blue-500/20 text-blue-400' },
            { label: 'Holiday', code: 'HO', bgClass: 'bg-purple-500/20 text-purple-400' },
            { label: 'Weekend', code: 'WO', bgClass: 'bg-white/[0.06] text-white/25' },
        ],

        get monthYearLabel() {
            const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            return months[this.selectedMonth - 1] + ' ' + this.selectedYear;
        },

        get daysInMonth() {
            const count = new Date(this.selectedYear, this.selectedMonth, 0).getDate();
            return Array.from({ length: count }, (_, i) => i + 1);
        },

        init() {
            this.parseServerData();
        },

        parseServerData() {
            const directReports = @json($directReports ?? []);
            const gridData = @json($attendanceGrid ?? []);

            const arr = Array.isArray(directReports) ? directReports : (directReports.data || Object.values(directReports));
            this.employees = arr.map(emp => {
                const name = emp.user ? emp.user.name : ((emp.first_name || '') + ' ' + (emp.last_name || '')).trim();
                const initials = name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
                return {
                    id: emp.id,
                    name: name,
                    initials: initials,
                    designation: emp.designation || '',
                    department: emp.department ? (emp.department.name || emp.department) : '',
                };
            });

            // Parse the attendance grid: { employee_id: { "2026-03-01": { status, clock_in, ... }, ... } }
            this.attendanceGrid = {};
            if (typeof gridData === 'object' && gridData !== null) {
                for (const [empId, days] of Object.entries(gridData)) {
                    this.attendanceGrid[empId] = {};
                    if (typeof days === 'object' && days !== null) {
                        for (const [date, info] of Object.entries(days)) {
                            this.attendanceGrid[empId][date] = info;
                        }
                    }
                }
            }
        },

        async fetchData() {
            try {
                const res = await fetch(`/api/hr/attendance/team?month=${this.selectedMonth}&year=${this.selectedYear}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.directReports || data.employees) {
                        const raw = data.directReports || data.employees || [];
                        const arr = Array.isArray(raw) ? raw : Object.values(raw);
                        this.employees = arr.map(emp => {
                            const name = emp.user ? emp.user.name : ((emp.first_name || '') + ' ' + (emp.last_name || '')).trim();
                            const initials = name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
                            return { id: emp.id, name, initials, designation: emp.designation || '', department: emp.department ? (emp.department.name || emp.department) : '' };
                        });
                    }
                    if (data.attendanceGrid || data.grid) {
                        this.attendanceGrid = data.attendanceGrid || data.grid || {};
                    }
                }
            } catch (e) { console.error('Fetch team attendance failed', e); }
        },

        prevMonth() {
            if (this.selectedMonth === 1) { this.selectedMonth = 12; this.selectedYear--; }
            else this.selectedMonth--;
            this.fetchData();
        },

        nextMonth() {
            if (this.selectedMonth === 12) { this.selectedMonth = 1; this.selectedYear++; }
            else this.selectedMonth++;
            this.fetchData();
        },

        isWeekend(day) {
            const d = new Date(this.selectedYear, this.selectedMonth - 1, day);
            return d.getDay() === 0 || d.getDay() === 6;
        },

        dayLabel(day) {
            const d = new Date(this.selectedYear, this.selectedMonth - 1, day);
            return ['S','M','T','W','T','F','S'][d.getDay()];
        },

        getCell(empId, day) {
            const dateStr = this.selectedYear + '-' + String(this.selectedMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            const empData = this.attendanceGrid[empId] || this.attendanceGrid[String(empId)] || {};
            return empData[dateStr] || null;
        },

        cellCode(empId, day) {
            const cell = this.getCell(empId, day);
            if (cell) {
                const s = (cell.status || '').toLowerCase();
                const map = { present: 'P', absent: 'A', half_day: 'H', late: 'LT', on_leave: 'L', holiday: 'HO', weekend: 'WO' };
                return map[s] || s.charAt(0).toUpperCase();
            }
            if (this.isWeekend(day)) return 'WO';
            // Future date
            const dateObj = new Date(this.selectedYear, this.selectedMonth - 1, day);
            if (dateObj > new Date()) return '';
            return '';
        },

        cellClass(empId, day) {
            const cell = this.getCell(empId, day);
            if (cell) {
                const s = (cell.status || '').toLowerCase();
                const map = {
                    present: 'bg-emerald-500/15 text-emerald-400',
                    absent: 'bg-red-500/15 text-red-400',
                    half_day: 'bg-amber-500/15 text-amber-400',
                    late: 'bg-orange-500/15 text-orange-400',
                    on_leave: 'bg-blue-500/15 text-blue-400',
                    holiday: 'bg-purple-500/15 text-purple-400',
                    weekend: 'bg-white/[0.04] text-white/20',
                };
                return map[s] || 'bg-white/[0.04] text-white/30';
            }
            if (this.isWeekend(day)) return 'bg-white/[0.04] text-white/20';
            return 'bg-transparent text-white/10';
        },

        cellTooltip(empId, day) {
            const cell = this.getCell(empId, day);
            const dateStr = this.selectedYear + '-' + String(this.selectedMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            if (!cell) return dateStr + (this.isWeekend(day) ? ' (Weekend)' : '');
            let tip = dateStr + ' | ' + (cell.status || 'Unknown').replace('_', ' ');
            if (cell.clock_in) tip += ' | In: ' + new Date(cell.clock_in).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            if (cell.clock_out) tip += ' | Out: ' + new Date(cell.clock_out).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
            if (cell.total_hours) tip += ' | ' + cell.total_hours + 'h';
            return tip;
        },

        empSummary(empId) {
            const empData = this.attendanceGrid[empId] || this.attendanceGrid[String(empId)] || {};
            let present = 0, absent = 0, late = 0;
            for (const [date, info] of Object.entries(empData)) {
                const s = (info.status || '').toLowerCase();
                if (s === 'present') present++;
                else if (s === 'late') { late++; present++; }
                else if (s === 'absent') absent++;
            }
            return { present, absent, late };
        },
    }
}
</script>
</x-layouts.hr>
