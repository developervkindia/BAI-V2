<x-layouts.hr title="Attendance" currentView="attendance">
<div class="p-5 lg:p-6 space-y-6" x-data="attendanceIndex()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Attendance</h1>
            <p class="text-[13px] text-white/45 mt-0.5">View and manage attendance records</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Employee Selector (admin) --}}
            @if(isset($employees) && count($employees) > 0)
            <select x-model="selectedEmployee" @change="fetchData()"
                    class="h-9 pl-3 pr-8 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}">{{ $emp->user->name ?? ($emp->first_name . ' ' . $emp->last_name) }}</option>
                @endforeach
            </select>
            @endif
            {{-- Month Selector --}}
            <select x-model="selectedMonth" @change="fetchData()"
                    class="h-9 pl-3 pr-8 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none">
                <template x-for="m in months" :key="m.value">
                    <option :value="m.value" x-text="m.label" :selected="m.value == selectedMonth"></option>
                </template>
            </select>
            {{-- Year Selector --}}
            <select x-model="selectedYear" @change="fetchData()"
                    class="h-9 pl-3 pr-8 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none">
                <template x-for="y in years" :key="y">
                    <option :value="y" x-text="y" :selected="y == selectedYear"></option>
                </template>
            </select>
            {{-- View toggle --}}
            <div class="flex bg-white/[0.06] rounded-lg border border-white/[0.08] overflow-hidden">
                <button @click="viewMode = 'calendar'" class="px-3 py-1.5 text-[12px] font-medium transition-colors" :class="viewMode === 'calendar' ? 'prod-bg text-white' : 'text-white/50 hover:text-white/70'">Calendar</button>
                <button @click="viewMode = 'table'" class="px-3 py-1.5 text-[12px] font-medium transition-colors" :class="viewMode === 'table' ? 'prod-bg text-white' : 'text-white/50 hover:text-white/70'">Table</button>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <template x-for="stat in summaryStats" :key="stat.label">
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-4">
                <div class="text-[11px] font-medium uppercase tracking-wider" :class="stat.labelColor" x-text="stat.label"></div>
                <div class="text-[22px] font-bold mt-1" :class="stat.valueColor" x-text="stat.value"></div>
            </div>
        </template>
    </div>

    {{-- Calendar View --}}
    <div x-show="viewMode === 'calendar'" class="bg-[#17172A] border border-white/[0.07] rounded-xl">
        <div class="p-4 border-b border-white/[0.06]">
            <h2 class="text-[15px] font-semibold text-white/80" x-text="monthYearLabel"></h2>
        </div>
        <div class="p-4">
            {{-- Day headers --}}
            <div class="grid grid-cols-7 gap-1.5 mb-2">
                <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                    <div class="text-center text-[11px] font-medium text-white/30 py-1" x-text="day"></div>
                </template>
            </div>
            {{-- Calendar grid --}}
            <div class="grid grid-cols-7 gap-1.5">
                <template x-for="cell in calendarCells" :key="cell.key">
                    <div @click="cell.date && cell.status ? showDayDetail(cell) : null"
                         class="min-h-[70px] rounded-lg p-1.5 flex flex-col transition-colors"
                         :class="cell.date ? (cellBgClass(cell) + ' cursor-pointer') : 'opacity-0'">
                        <span class="text-[12px] font-medium" :class="cell.isToday ? 'prod-text font-bold' : 'text-white/55'" x-text="cell.day || ''"></span>
                        <template x-if="cell.status">
                            <div class="flex-1 flex flex-col justify-end">
                                <span class="text-[9px] font-bold uppercase tracking-wide" :class="cellTextClass(cell.status)" x-text="cellShortLabel(cell.status)"></span>
                                <template x-if="cell.clockIn">
                                    <span class="text-[9px] text-white/30 font-mono" x-text="cell.clockIn"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
        {{-- Legend --}}
        <div class="px-4 pb-4 flex flex-wrap gap-x-4 gap-y-1">
            <template x-for="item in legendItems" :key="item.label">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-sm" :class="item.color"></span>
                    <span class="text-[11px] text-white/40" x-text="item.label"></span>
                </div>
            </template>
        </div>
    </div>

    {{-- Table View --}}
    <div x-show="viewMode === 'table'" class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider">Clock In</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider">Clock Out</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider">Hours</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider">OT</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider">Source</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="log in attendanceLogs" :key="log.id || log.date">
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 text-[13px] text-white/70 font-medium" x-text="formatDate(log.date)"></td>
                            <td class="px-4 py-3 text-[13px] text-white/60 font-mono" x-text="formatTime(log.clock_in)"></td>
                            <td class="px-4 py-3 text-[13px] text-white/60 font-mono" x-text="formatTime(log.clock_out)"></td>
                            <td class="px-4 py-3 text-[13px] text-white/60 font-mono" x-text="log.total_hours ? log.total_hours + 'h' : '--'"></td>
                            <td class="px-4 py-3 text-[13px] font-mono" :class="log.overtime_hours > 0 ? 'text-amber-400' : 'text-white/35'" x-text="log.overtime_hours ? log.overtime_hours + 'h' : '--'"></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold" :class="statusBadgeClass(log.status)" x-text="statusLabel(log.status)"></span>
                            </td>
                            <td class="px-4 py-3 text-[12px] text-white/40" x-text="log.source || 'Manual'"></td>
                        </tr>
                    </template>
                    <template x-if="attendanceLogs.length === 0">
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-[13px] text-white/30">No attendance records found for this period</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Day Detail Modal --}}
    <template x-if="detailDay">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="detailDay = null">
            <div class="fixed inset-0 bg-black/60" @click="detailDay = null"></div>
            <div class="relative bg-[#17172A] border border-white/[0.1] rounded-xl w-full max-w-md p-6 z-10" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[16px] font-semibold text-white/85" x-text="'Attendance Details - ' + formatDate(detailDay.date)"></h3>
                    <button @click="detailDay = null" class="p-1 rounded-lg hover:bg-white/[0.06] text-white/40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-white/[0.05]">
                        <span class="text-[13px] text-white/45">Status</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold" :class="statusBadgeClass(detailDay.status)" x-text="statusLabel(detailDay.status)"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-white/[0.05]">
                        <span class="text-[13px] text-white/45">Clock In</span>
                        <span class="text-[13px] text-white/75 font-mono" x-text="formatTime(detailDay.clockInFull)"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-white/[0.05]">
                        <span class="text-[13px] text-white/45">Clock Out</span>
                        <span class="text-[13px] text-white/75 font-mono" x-text="detailDay.clockOutFull ? formatTime(detailDay.clockOutFull) : '--:--'"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-white/[0.05]">
                        <span class="text-[13px] text-white/45">Total Hours</span>
                        <span class="text-[13px] text-white/75 font-mono" x-text="detailDay.totalHours ? detailDay.totalHours + 'h' : '--'"></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-white/[0.05]">
                        <span class="text-[13px] text-white/45">Overtime</span>
                        <span class="text-[13px] font-mono" :class="detailDay.overtimeHours > 0 ? 'text-amber-400' : 'text-white/35'" x-text="detailDay.overtimeHours ? detailDay.overtimeHours + 'h' : '--'"></span>
                    </div>
                    <template x-if="detailDay.remarks">
                        <div class="flex justify-between items-start py-2">
                            <span class="text-[13px] text-white/45">Remarks</span>
                            <span class="text-[13px] text-white/65 text-right max-w-[200px]" x-text="detailDay.remarks"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

</div>

<script>
function attendanceIndex() {
    return {
        viewMode: 'calendar',
        selectedMonth: {{ $month }},
        selectedYear: {{ $year }},
        selectedEmployee: '',
        attendanceLogs: @json($attendanceLogs ?? []),
        monthlyData: {},
        calendarCells: [],
        detailDay: null,
        summary: { present: 0, absent: 0, late: 0, halfDay: 0, leaves: 0, avgHours: 0 },

        months: [
            { value: 1, label: 'January' }, { value: 2, label: 'February' }, { value: 3, label: 'March' },
            { value: 4, label: 'April' }, { value: 5, label: 'May' }, { value: 6, label: 'June' },
            { value: 7, label: 'July' }, { value: 8, label: 'August' }, { value: 9, label: 'September' },
            { value: 10, label: 'October' }, { value: 11, label: 'November' }, { value: 12, label: 'December' },
        ],

        get years() {
            const cur = new Date().getFullYear();
            return [cur - 2, cur - 1, cur, cur + 1];
        },

        get monthYearLabel() {
            return this.months.find(m => m.value == this.selectedMonth)?.label + ' ' + this.selectedYear;
        },

        get summaryStats() {
            return [
                { label: 'Present', value: this.summary.present, labelColor: 'text-emerald-400/60', valueColor: 'text-emerald-400' },
                { label: 'Absent', value: this.summary.absent, labelColor: 'text-red-400/60', valueColor: 'text-red-400' },
                { label: 'Late', value: this.summary.late, labelColor: 'text-orange-400/60', valueColor: 'text-orange-400' },
                { label: 'Half Days', value: this.summary.halfDay, labelColor: 'text-amber-400/60', valueColor: 'text-amber-400' },
                { label: 'Leaves', value: this.summary.leaves, labelColor: 'text-blue-400/60', valueColor: 'text-blue-400' },
                { label: 'Avg Hours', value: this.summary.avgHours, labelColor: 'text-white/35', valueColor: 'text-white/75' },
            ];
        },

        legendItems: [
            { label: 'Present', color: 'bg-emerald-500' },
            { label: 'Absent', color: 'bg-red-500' },
            { label: 'Half Day', color: 'bg-amber-500' },
            { label: 'Late', color: 'bg-orange-500' },
            { label: 'Leave', color: 'bg-blue-500' },
            { label: 'Holiday', color: 'bg-purple-500' },
            { label: 'Weekend', color: 'bg-white/20' },
        ],

        init() {
            this.processLogs(this.attendanceLogs);
            this.buildCalendar();
        },

        processLogs(logs) {
            const arr = Array.isArray(logs) ? logs : (logs.data || Object.values(logs) || []);
            this.attendanceLogs = arr;
            this.monthlyData = {};
            let present = 0, absent = 0, late = 0, halfDay = 0, leaves = 0, totalHours = 0, hoursCount = 0;

            arr.forEach(log => {
                const d = log.date || (log.clock_in ? log.clock_in.substring(0, 10) : null);
                if (d) this.monthlyData[d] = log;
                const s = (log.status || '').toLowerCase();
                if (s === 'present') present++;
                else if (s === 'absent') absent++;
                else if (s === 'late') { late++; present++; }
                else if (s === 'half_day') halfDay++;
                else if (s === 'on_leave') leaves++;
                if (log.total_hours) { totalHours += parseFloat(log.total_hours); hoursCount++; }
            });

            this.summary = {
                present,
                absent,
                late,
                halfDay,
                leaves,
                avgHours: hoursCount > 0 ? (totalHours / hoursCount).toFixed(1) : '0',
            };
        },

        async fetchData() {
            try {
                let url = `/api/hr/attendance?month=${this.selectedMonth}&year=${this.selectedYear}`;
                if (this.selectedEmployee) url += `&employee_id=${this.selectedEmployee}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.processLogs(data.logs || data);
                    this.buildCalendar();
                }
            } catch (e) { console.error('Fetch attendance failed', e); }
        },

        buildCalendar() {
            const month = this.selectedMonth - 1;
            const year = this.selectedYear;
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const today = new Date();
            const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
            const cells = [];

            for (let i = 0; i < firstDay; i++) {
                cells.push({ key: 'e' + i, day: null, date: null, status: null });
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                const dayOfWeek = new Date(year, month, d).getDay();
                const log = this.monthlyData[dateStr];
                let status = null, clockIn = null;

                if (log) {
                    status = log.status || 'present';
                    if (log.clock_in) clockIn = this.formatTime(log.clock_in);
                } else if (dayOfWeek === 0 || dayOfWeek === 6) {
                    status = 'weekend';
                }

                cells.push({
                    key: 'd' + d,
                    day: d,
                    date: dateStr,
                    status: status,
                    clockIn: clockIn,
                    isToday: dateStr === todayStr,
                    log: log,
                });
            }

            this.calendarCells = cells;
        },

        showDayDetail(cell) {
            if (!cell.log) return;
            this.detailDay = {
                date: cell.date,
                status: cell.log.status,
                clockInFull: cell.log.clock_in,
                clockOutFull: cell.log.clock_out,
                totalHours: cell.log.total_hours,
                overtimeHours: cell.log.overtime_hours,
                remarks: cell.log.remarks,
                source: cell.log.source,
            };
        },

        formatTime(datetime) {
            if (!datetime) return '--:--';
            const d = new Date(datetime);
            return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        },

        statusLabel(status) {
            const map = { present: 'Present', absent: 'Absent', half_day: 'Half Day', late: 'Late', on_leave: 'On Leave', holiday: 'Holiday', weekend: 'Weekend' };
            return map[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ') : 'Unknown');
        },

        statusBadgeClass(status) {
            const map = {
                present: 'bg-emerald-500/15 text-emerald-400',
                absent: 'bg-red-500/15 text-red-400',
                half_day: 'bg-amber-500/15 text-amber-400',
                late: 'bg-orange-500/15 text-orange-400',
                on_leave: 'bg-blue-500/15 text-blue-400',
                holiday: 'bg-purple-500/15 text-purple-400',
                weekend: 'bg-white/[0.06] text-white/40',
            };
            return map[status] || 'bg-white/[0.06] text-white/50';
        },

        cellBgClass(cell) {
            if (cell.isToday) return 'bg-cyan-500/10 ring-1 ring-cyan-500/30';
            if (!cell.status) return 'bg-white/[0.02] hover:bg-white/[0.04]';
            const map = {
                present: 'bg-emerald-500/8 hover:bg-emerald-500/15',
                absent: 'bg-red-500/8 hover:bg-red-500/15',
                half_day: 'bg-amber-500/8 hover:bg-amber-500/15',
                late: 'bg-orange-500/8 hover:bg-orange-500/15',
                on_leave: 'bg-blue-500/8 hover:bg-blue-500/15',
                holiday: 'bg-purple-500/8 hover:bg-purple-500/15',
                weekend: 'bg-white/[0.02] hover:bg-white/[0.04]',
            };
            return map[cell.status] || 'bg-white/[0.02] hover:bg-white/[0.04]';
        },

        cellTextClass(status) {
            const map = {
                present: 'text-emerald-400',
                absent: 'text-red-400',
                half_day: 'text-amber-400',
                late: 'text-orange-400',
                on_leave: 'text-blue-400',
                holiday: 'text-purple-400',
                weekend: 'text-white/20',
            };
            return map[status] || 'text-white/30';
        },

        cellShortLabel(status) {
            const map = { present: 'P', absent: 'A', half_day: 'HD', late: 'LT', on_leave: 'L', holiday: 'H', weekend: 'WO' };
            return map[status] || '';
        },
    }
}
</script>
</x-layouts.hr>
