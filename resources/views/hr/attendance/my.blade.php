<x-layouts.hr title="My Attendance" currentView="my-attendance">
<div class="p-5 lg:p-6 space-y-6" x-data="myAttendance()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">My Attendance</h1>
            <p class="text-[13px] text-white/45 mt-0.5">Track your daily clock-in and clock-out</p>
        </div>
        <div class="text-right">
            <div class="text-[13px] text-white/45" x-text="todayDateStr"></div>
            <div class="text-[20px] font-mono font-semibold text-white/80" x-text="currentTimeStr"></div>
        </div>
    </div>

    {{-- Clock In/Out Card --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-6">
        <div class="flex flex-col lg:flex-row lg:items-center gap-6">

            {{-- Status Indicator --}}
            <div class="flex-1 flex items-center gap-4">
                <div class="w-14 h-14 rounded-full flex items-center justify-center shrink-0"
                     :class="isClockedIn ? 'bg-emerald-500/15' : (todayLog && todayLog.clock_out ? 'bg-cyan-500/15' : 'bg-white/[0.06]')">
                    <template x-if="!isClockedIn && !(todayLog && todayLog.clock_out)">
                        <svg class="w-7 h-7 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                    <template x-if="isClockedIn">
                        <div class="relative flex items-center justify-center">
                            <span class="animate-ping absolute inline-flex h-5 w-5 rounded-full bg-emerald-400 opacity-40"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-400"></span>
                        </div>
                    </template>
                    <template x-if="!isClockedIn && todayLog && todayLog.clock_out">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
                <div>
                    <template x-if="!todayLog || (!todayLog.clock_in && !todayLog.clock_out)">
                        <div>
                            <div class="text-[15px] font-semibold text-white/70">Not clocked in</div>
                            <div class="text-[13px] text-white/35 mt-0.5">Clock in to start your work day</div>
                        </div>
                    </template>
                    <template x-if="isClockedIn">
                        <div>
                            <div class="text-[15px] font-semibold text-emerald-400">Working since <span x-text="formatTime(todayLog.clock_in)"></span></div>
                            <div class="text-[13px] text-white/45 mt-0.5">Elapsed: <span class="font-mono" x-text="elapsedTime"></span></div>
                        </div>
                    </template>
                    <template x-if="!isClockedIn && todayLog && todayLog.clock_out">
                        <div>
                            <div class="text-[15px] font-semibold text-cyan-400">Completed &mdash; <span x-text="todayLog.total_hours ? todayLog.total_hours + ' hours' : '0 hours'"></span></div>
                            <div class="text-[13px] text-white/45 mt-0.5"><span x-text="formatTime(todayLog.clock_in)"></span> &rarr; <span x-text="formatTime(todayLog.clock_out)"></span></div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Clock In / Clock Out Button --}}
            <div class="shrink-0">
                <template x-if="!isClockedIn && !(todayLog && todayLog.clock_out)">
                    <button @click="clockIn()" :disabled="loading"
                            class="px-8 py-3 rounded-xl text-[14px] font-semibold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors disabled:opacity-50 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span x-text="loading ? 'Clocking In...' : 'Clock In'"></span>
                    </button>
                </template>
                <template x-if="isClockedIn">
                    <button @click="clockOut()" :disabled="loading"
                            class="px-8 py-3 rounded-xl text-[14px] font-semibold text-white bg-red-600 hover:bg-red-500 transition-colors disabled:opacity-50 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span x-text="loading ? 'Clocking Out...' : 'Clock Out'"></span>
                    </button>
                </template>
                <template x-if="!isClockedIn && todayLog && todayLog.clock_out">
                    <div class="px-6 py-3 rounded-xl text-[14px] font-medium text-white/40 bg-white/[0.04] border border-white/[0.06] text-center">
                        Day Complete
                    </div>
                </template>
            </div>
        </div>

        {{-- Today's Details --}}
        <template x-if="todayLog && todayLog.clock_in">
            <div class="mt-5 pt-5 border-t border-white/[0.06] grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <div class="text-[11px] font-medium text-white/35 uppercase tracking-wider">Clock In</div>
                    <div class="text-[15px] font-mono text-white/75 mt-1" x-text="formatTime(todayLog.clock_in)"></div>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-white/35 uppercase tracking-wider">Clock Out</div>
                    <div class="text-[15px] font-mono text-white/75 mt-1" x-text="todayLog.clock_out ? formatTime(todayLog.clock_out) : '--:--'"></div>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-white/35 uppercase tracking-wider">Total Hours</div>
                    <div class="text-[15px] font-mono text-white/75 mt-1" x-text="todayLog.total_hours ? todayLog.total_hours + 'h' : '--'"></div>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-white/35 uppercase tracking-wider">Status</div>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold"
                              :class="statusClass(todayLog.status)" x-text="statusLabel(todayLog.status)"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Monthly Calendar --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl">
        <div class="p-4 border-b border-white/[0.06] flex items-center justify-between">
            <h2 class="text-[15px] font-semibold text-white/80">Monthly Attendance</h2>
            <div class="flex items-center gap-2">
                <button @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/40 hover:text-white/70 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <span class="text-[13px] font-medium text-white/65 min-w-[120px] text-center" x-text="monthYearLabel"></span>
                <button @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/40 hover:text-white/70 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        <div class="p-4">
            {{-- Day headers --}}
            <div class="grid grid-cols-7 gap-1 mb-2">
                <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                    <div class="text-center text-[11px] font-medium text-white/30 py-1" x-text="day"></div>
                </template>
            </div>
            {{-- Calendar cells --}}
            <div class="grid grid-cols-7 gap-1">
                <template x-for="cell in calendarCells" :key="cell.key">
                    <div class="aspect-square rounded-lg flex flex-col items-center justify-center text-[12px] relative cursor-default transition-colors"
                         :class="cell.date ? cellClass(cell) : ''"
                         :title="cell.tooltip">
                        <span class="font-medium" :class="cell.date ? 'text-white/65' : 'text-transparent'" x-text="cell.day || ''"></span>
                        <template x-if="cell.status">
                            <span class="text-[8px] font-bold mt-0.5 uppercase" :class="cellLabelClass(cell.status)" x-text="cellShortLabel(cell.status)"></span>
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

</div>

<script>
function myAttendance() {
    return {
        isClockedIn: false,
        todayLog: @json($todayLog),
        loading: false,
        currentTimeStr: '',
        todayDateStr: '',
        elapsedTime: '',
        calMonth: new Date().getMonth(),
        calYear: new Date().getFullYear(),
        monthlyData: {},
        calendarCells: [],
        clockInterval: null,

        legendItems: [
            { label: 'Present', color: 'bg-emerald-500' },
            { label: 'Absent', color: 'bg-red-500' },
            { label: 'Half Day', color: 'bg-amber-500' },
            { label: 'Late', color: 'bg-orange-500' },
            { label: 'Leave', color: 'bg-blue-500' },
            { label: 'Holiday', color: 'bg-purple-500' },
            { label: 'Weekend', color: 'bg-white/20' },
        ],

        get monthYearLabel() {
            const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            return months[this.calMonth] + ' ' + this.calYear;
        },

        init() {
            if (this.todayLog && this.todayLog.clock_in && !this.todayLog.clock_out) {
                this.isClockedIn = true;
            }
            this.updateClock();
            this.clockInterval = setInterval(() => this.updateClock(), 1000);
            this.buildCalendar();
            this.fetchMonthlyData();
        },

        updateClock() {
            const now = new Date();
            this.currentTimeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            this.todayDateStr = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });

            if (this.isClockedIn && this.todayLog && this.todayLog.clock_in) {
                const clockIn = new Date(this.todayLog.clock_in);
                const diff = Math.floor((now - clockIn) / 1000);
                const hrs = Math.floor(diff / 3600);
                const mins = Math.floor((diff % 3600) / 60);
                const secs = diff % 60;
                this.elapsedTime = String(hrs).padStart(2,'0') + ':' + String(mins).padStart(2,'0') + ':' + String(secs).padStart(2,'0');
            }
        },

        async clockIn() {
            this.loading = true;
            try {
                const res = await fetch('/api/hr/attendance/clock-in', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (res.ok) {
                    this.todayLog = data.log || data;
                    this.isClockedIn = true;
                }
            } catch (e) { console.error('Clock in failed', e); }
            this.loading = false;
        },

        async clockOut() {
            this.loading = true;
            try {
                const res = await fetch('/api/hr/attendance/clock-out', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (res.ok) {
                    this.todayLog = data.log || data;
                    this.isClockedIn = false;
                }
            } catch (e) { console.error('Clock out failed', e); }
            this.loading = false;
        },

        async fetchMonthlyData() {
            try {
                const res = await fetch(`/api/hr/attendance/monthly?month=${this.calMonth + 1}&year=${this.calYear}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (res.ok) {
                    const data = await res.json();
                    this.monthlyData = {};
                    (data.logs || data || []).forEach(log => {
                        const d = log.date || (log.clock_in ? log.clock_in.substring(0, 10) : null);
                        if (d) this.monthlyData[d] = log;
                    });
                }
            } catch (e) { console.error('Fetch monthly data failed', e); }
            this.buildCalendar();
        },

        prevMonth() {
            if (this.calMonth === 0) { this.calMonth = 11; this.calYear--; }
            else this.calMonth--;
            this.fetchMonthlyData();
        },

        nextMonth() {
            if (this.calMonth === 11) { this.calMonth = 0; this.calYear++; }
            else this.calMonth++;
            this.fetchMonthlyData();
        },

        buildCalendar() {
            const firstDay = new Date(this.calYear, this.calMonth, 1).getDay();
            const daysInMonth = new Date(this.calYear, this.calMonth + 1, 0).getDate();
            const cells = [];

            for (let i = 0; i < firstDay; i++) {
                cells.push({ key: 'e' + i, day: null, date: null, status: null, tooltip: '' });
            }

            for (let d = 1; d <= daysInMonth; d++) {
                const dateStr = this.calYear + '-' + String(this.calMonth + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
                const dayOfWeek = new Date(this.calYear, this.calMonth, d).getDay();
                const log = this.monthlyData[dateStr];
                let status = null;
                let tooltip = dateStr;

                if (log) {
                    status = log.status || 'present';
                    tooltip = dateStr + ' | ' + (status.charAt(0).toUpperCase() + status.slice(1));
                    if (log.clock_in) tooltip += ' | In: ' + this.formatTime(log.clock_in);
                    if (log.clock_out) tooltip += ' | Out: ' + this.formatTime(log.clock_out);
                } else if (dayOfWeek === 0 || dayOfWeek === 6) {
                    status = 'weekend';
                    tooltip = dateStr + ' | Weekend';
                }

                cells.push({ key: 'd' + d, day: d, date: dateStr, status: status, tooltip: tooltip });
            }

            this.calendarCells = cells;
        },

        formatTime(datetime) {
            if (!datetime) return '--:--';
            const d = new Date(datetime);
            return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
        },

        statusClass(status) {
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

        statusLabel(status) {
            const map = { present: 'Present', absent: 'Absent', half_day: 'Half Day', late: 'Late', on_leave: 'On Leave', holiday: 'Holiday', weekend: 'Weekend' };
            return map[status] || (status ? status.charAt(0).toUpperCase() + status.slice(1).replace('_',' ') : 'Unknown');
        },

        cellClass(cell) {
            if (!cell.status) return 'bg-white/[0.02] hover:bg-white/[0.05]';
            const map = {
                present: 'bg-emerald-500/10 hover:bg-emerald-500/20',
                absent: 'bg-red-500/10 hover:bg-red-500/20',
                half_day: 'bg-amber-500/10 hover:bg-amber-500/20',
                late: 'bg-orange-500/10 hover:bg-orange-500/20',
                on_leave: 'bg-blue-500/10 hover:bg-blue-500/20',
                holiday: 'bg-purple-500/10 hover:bg-purple-500/20',
                weekend: 'bg-white/[0.03] hover:bg-white/[0.06]',
            };
            return map[cell.status] || 'bg-white/[0.02] hover:bg-white/[0.05]';
        },

        cellLabelClass(status) {
            const map = {
                present: 'text-emerald-400',
                absent: 'text-red-400',
                half_day: 'text-amber-400',
                late: 'text-orange-400',
                on_leave: 'text-blue-400',
                holiday: 'text-purple-400',
                weekend: 'text-white/25',
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
