<x-layouts.hr title="Attendance Reports" currentView="attendance-reports">
<div class="p-5 lg:p-6 space-y-6" x-data="attendanceReports()" x-init="init()">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Attendance Reports</h1>
            <p class="text-[13px] text-white/45 mt-0.5">Organization-wide attendance analytics and insights</p>
        </div>
        <div class="flex items-center gap-2">
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
            {{-- Export Button --}}
            <button @click="exportReport()"
                    class="h-9 px-4 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] font-medium text-white/60 hover:text-white/80 hover:bg-white/[0.1] transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Avg Attendance --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/12 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-white/35 uppercase tracking-wider">Avg Attendance</div>
                    <div class="text-[26px] font-bold text-emerald-400 leading-tight" x-text="avgAttendancePct + '%'"></div>
                </div>
            </div>
            <div class="mt-3 h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="'width: ' + avgAttendancePct + '%'"></div>
            </div>
        </div>

        {{-- Total Late Marks --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-orange-500/12 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-white/35 uppercase tracking-wider">Total Late Marks</div>
                    <div class="text-[26px] font-bold text-orange-400 leading-tight" x-text="totalLateMarks"></div>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-white/35">Across all employees this month</div>
        </div>

        {{-- Total Absentees --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-red-500/12 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div>
                    <div class="text-[11px] font-medium text-white/35 uppercase tracking-wider">Total Absentees</div>
                    <div class="text-[26px] font-bold text-red-400 leading-tight" x-text="totalAbsentees"></div>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-white/35">Total absent days across all employees</div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-white/[0.06] flex items-center justify-between">
            <h2 class="text-[15px] font-semibold text-white/80">Employee Attendance Summary</h2>
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="searchQuery" placeholder="Search employees..."
                       class="pl-8 pr-3 py-1.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[12px] text-white/70 placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 w-48"/>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-white/40 uppercase tracking-wider cursor-pointer hover:text-white/60" @click="sortBy('name')">
                            <div class="flex items-center gap-1">Employee <span x-show="sortField === 'name'" x-text="sortDir === 'asc' ? '\u2191' : '\u2193'" class="text-cyan-400"></span></div>
                        </th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-white/40 uppercase tracking-wider cursor-pointer hover:text-white/60" @click="sortBy('present')">
                            <div class="flex items-center justify-center gap-1">Present <span x-show="sortField === 'present'" x-text="sortDir === 'asc' ? '\u2191' : '\u2193'" class="text-cyan-400"></span></div>
                        </th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-white/40 uppercase tracking-wider cursor-pointer hover:text-white/60" @click="sortBy('absent')">
                            <div class="flex items-center justify-center gap-1">Absent <span x-show="sortField === 'absent'" x-text="sortDir === 'asc' ? '\u2191' : '\u2193'" class="text-cyan-400"></span></div>
                        </th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-white/40 uppercase tracking-wider cursor-pointer hover:text-white/60" @click="sortBy('late')">
                            <div class="flex items-center justify-center gap-1">Late <span x-show="sortField === 'late'" x-text="sortDir === 'asc' ? '\u2191' : '\u2193'" class="text-cyan-400"></span></div>
                        </th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-white/40 uppercase tracking-wider cursor-pointer hover:text-white/60" @click="sortBy('halfDays')">
                            <div class="flex items-center justify-center gap-1">Half Days <span x-show="sortField === 'halfDays'" x-text="sortDir === 'asc' ? '\u2191' : '\u2193'" class="text-cyan-400"></span></div>
                        </th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-white/40 uppercase tracking-wider cursor-pointer hover:text-white/60" @click="sortBy('overtime')">
                            <div class="flex items-center justify-center gap-1">Overtime <span x-show="sortField === 'overtime'" x-text="sortDir === 'asc' ? '\u2191' : '\u2193'" class="text-cyan-400"></span></div>
                        </th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-white/40 uppercase tracking-wider cursor-pointer hover:text-white/60" @click="sortBy('avgHours')">
                            <div class="flex items-center justify-center gap-1">Avg Hrs <span x-show="sortField === 'avgHours'" x-text="sortDir === 'asc' ? '\u2191' : '\u2193'" class="text-cyan-400"></span></div>
                        </th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-white/40 uppercase tracking-wider">Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="row in filteredRows" :key="row.id">
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full prod-bg-muted prod-text text-[10px] font-bold flex items-center justify-center shrink-0" x-text="row.initials"></div>
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-medium text-white/75 truncate" x-text="row.name"></div>
                                        <div class="text-[11px] text-white/35 truncate" x-text="row.department"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[13px] font-semibold text-emerald-400" x-text="row.present"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[13px] font-semibold" :class="row.absent > 0 ? 'text-red-400' : 'text-white/30'" x-text="row.absent"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[13px] font-semibold" :class="row.late > 0 ? 'text-orange-400' : 'text-white/30'" x-text="row.late"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[13px] font-semibold" :class="row.halfDays > 0 ? 'text-amber-400' : 'text-white/30'" x-text="row.halfDays"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[13px] font-mono" :class="row.overtime > 0 ? 'text-cyan-400' : 'text-white/30'" x-text="row.overtime ? row.overtime + 'h' : '--'"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-[13px] font-mono text-white/65" x-text="row.avgHours ? row.avgHours + 'h' : '--'"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             :class="row.attendancePct >= 90 ? 'bg-emerald-500' : (row.attendancePct >= 75 ? 'bg-amber-500' : 'bg-red-500')"
                                             :style="'width: ' + row.attendancePct + '%'"></div>
                                    </div>
                                    <span class="text-[12px] font-semibold min-w-[36px]"
                                          :class="row.attendancePct >= 90 ? 'text-emerald-400' : (row.attendancePct >= 75 ? 'text-amber-400' : 'text-red-400')"
                                          x-text="row.attendancePct + '%'"></span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredRows.length === 0">
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-[13px] text-white/30">No employee data found</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function attendanceReports() {
    return {
        selectedMonth: {{ $month }},
        selectedYear: {{ $year }},
        rows: [],
        searchQuery: '',
        sortField: 'name',
        sortDir: 'asc',

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

        get avgAttendancePct() {
            if (this.rows.length === 0) return 0;
            const total = this.rows.reduce((sum, r) => sum + (r.attendancePct || 0), 0);
            return Math.round(total / this.rows.length);
        },

        get totalLateMarks() {
            return this.rows.reduce((sum, r) => sum + (r.late || 0), 0);
        },

        get totalAbsentees() {
            return this.rows.reduce((sum, r) => sum + (r.absent || 0), 0);
        },

        get filteredRows() {
            let result = this.rows;
            if (this.searchQuery.trim()) {
                const q = this.searchQuery.toLowerCase();
                result = result.filter(r => r.name.toLowerCase().includes(q) || (r.department || '').toLowerCase().includes(q));
            }
            const field = this.sortField;
            const dir = this.sortDir === 'asc' ? 1 : -1;
            result = [...result].sort((a, b) => {
                if (field === 'name') return a.name.localeCompare(b.name) * dir;
                return ((a[field] || 0) - (b[field] || 0)) * dir;
            });
            return result;
        },

        init() {
            this.parseServerData();
        },

        parseServerData() {
            const employees = @json($employees ?? []);
            const summaryData = @json($attendanceSummary ?? []);

            const empArr = Array.isArray(employees) ? employees : (employees.data || Object.values(employees));
            const sumArr = Array.isArray(summaryData) ? summaryData : (summaryData.data || Object.values(summaryData));

            // Build a lookup from summary data by employee_id
            const summaryMap = {};
            sumArr.forEach(s => {
                const key = s.employee_id || s.id;
                if (key) summaryMap[key] = s;
            });

            // Calculate working days in the month (exclude weekends)
            const daysInMonth = new Date(this.selectedYear, this.selectedMonth, 0).getDate();
            let workingDays = 0;
            for (let d = 1; d <= daysInMonth; d++) {
                const dayOfWeek = new Date(this.selectedYear, this.selectedMonth - 1, d).getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) workingDays++;
            }
            // Only count up to today if current month
            const now = new Date();
            if (this.selectedYear === now.getFullYear() && this.selectedMonth === (now.getMonth() + 1)) {
                let wd = 0;
                for (let d = 1; d <= now.getDate(); d++) {
                    const dayOfWeek = new Date(this.selectedYear, this.selectedMonth - 1, d).getDay();
                    if (dayOfWeek !== 0 && dayOfWeek !== 6) wd++;
                }
                workingDays = wd;
            }

            this.rows = empArr.map(emp => {
                const name = emp.user ? emp.user.name : ((emp.first_name || '') + ' ' + (emp.last_name || '')).trim();
                const initials = name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
                const dept = emp.department ? (emp.department.name || emp.department) : '';
                const s = summaryMap[emp.id] || {};

                const present = parseInt(s.present_days || s.present || 0);
                const absent = parseInt(s.absent_days || s.absent || 0);
                const late = parseInt(s.late_days || s.late || 0);
                const halfDays = parseInt(s.half_days || s.half_day || 0);
                const overtime = parseFloat(s.overtime_hours || s.overtime || 0);
                const avgHours = parseFloat(s.avg_working_hours || s.avg_hours || 0);
                const attendancePct = workingDays > 0 ? Math.round((present / workingDays) * 100) : 0;

                return {
                    id: emp.id,
                    name: name,
                    initials: initials,
                    department: dept,
                    present,
                    absent,
                    late,
                    halfDays,
                    overtime: overtime ? parseFloat(overtime.toFixed(1)) : 0,
                    avgHours: avgHours ? parseFloat(avgHours.toFixed(1)) : 0,
                    attendancePct: Math.min(attendancePct, 100),
                };
            });
        },

        async fetchData() {
            try {
                const res = await fetch(`/api/hr/attendance/reports?month=${this.selectedMonth}&year=${this.selectedYear}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                if (res.ok) {
                    const data = await res.json();
                    const employees = data.employees || [];
                    const summaryData = data.attendanceSummary || data.summary || [];

                    const empArr = Array.isArray(employees) ? employees : Object.values(employees);
                    const sumArr = Array.isArray(summaryData) ? summaryData : Object.values(summaryData);

                    const summaryMap = {};
                    sumArr.forEach(s => {
                        const key = s.employee_id || s.id;
                        if (key) summaryMap[key] = s;
                    });

                    const daysInMonth = new Date(this.selectedYear, this.selectedMonth, 0).getDate();
                    let workingDays = 0;
                    for (let d = 1; d <= daysInMonth; d++) {
                        const dayOfWeek = new Date(this.selectedYear, this.selectedMonth - 1, d).getDay();
                        if (dayOfWeek !== 0 && dayOfWeek !== 6) workingDays++;
                    }
                    const now = new Date();
                    if (this.selectedYear === now.getFullYear() && this.selectedMonth === (now.getMonth() + 1)) {
                        let wd = 0;
                        for (let d = 1; d <= now.getDate(); d++) {
                            const dayOfWeek = new Date(this.selectedYear, this.selectedMonth - 1, d).getDay();
                            if (dayOfWeek !== 0 && dayOfWeek !== 6) wd++;
                        }
                        workingDays = wd;
                    }

                    this.rows = empArr.map(emp => {
                        const name = emp.user ? emp.user.name : ((emp.first_name || '') + ' ' + (emp.last_name || '')).trim();
                        const initials = name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2);
                        const dept = emp.department ? (emp.department.name || emp.department) : '';
                        const s = summaryMap[emp.id] || {};
                        const present = parseInt(s.present_days || s.present || 0);
                        const absent = parseInt(s.absent_days || s.absent || 0);
                        const late = parseInt(s.late_days || s.late || 0);
                        const halfDays = parseInt(s.half_days || s.half_day || 0);
                        const overtime = parseFloat(s.overtime_hours || s.overtime || 0);
                        const avgHours = parseFloat(s.avg_working_hours || s.avg_hours || 0);
                        const attendancePct = workingDays > 0 ? Math.round((present / workingDays) * 100) : 0;
                        return { id: emp.id, name, initials, department: dept, present, absent, late, halfDays, overtime: overtime ? parseFloat(overtime.toFixed(1)) : 0, avgHours: avgHours ? parseFloat(avgHours.toFixed(1)) : 0, attendancePct: Math.min(attendancePct, 100) };
                    });
                }
            } catch (e) { console.error('Fetch reports failed', e); }
        },

        sortBy(field) {
            if (this.sortField === field) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDir = 'asc';
            }
        },

        exportReport() {
            // Build CSV from current filtered rows
            const headers = ['Employee', 'Department', 'Present', 'Absent', 'Late', 'Half Days', 'Overtime (h)', 'Avg Hours', 'Attendance %'];
            const csvRows = [headers.join(',')];

            this.filteredRows.forEach(row => {
                csvRows.push([
                    '"' + row.name.replace(/"/g, '""') + '"',
                    '"' + (row.department || '').replace(/"/g, '""') + '"',
                    row.present,
                    row.absent,
                    row.late,
                    row.halfDays,
                    row.overtime,
                    row.avgHours,
                    row.attendancePct,
                ].join(','));
            });

            const blob = new Blob([csvRows.join('\n')], { type: 'text/csv' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `attendance-report-${this.selectedYear}-${String(this.selectedMonth).padStart(2, '0')}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },
    }
}
</script>
</x-layouts.hr>
