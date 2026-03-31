<x-layouts.smartprojects :project="$project" currentView="calendar" :canEdit="$canEdit">

<div
    class="flex flex-col"
    style="height: calc(100vh - 176px)"
    x-data="calendarView()"
    x-init="init()"
>

    {{-- ================================================================ --}}
    {{-- TOOLBAR                                                           --}}
    {{-- ================================================================ --}}
    <div class="shrink-0 flex items-center gap-3 px-4 py-3 border-b border-white/5 bg-neutral-950/80 backdrop-blur-sm">

        {{-- Left: month navigation --}}
        <div class="flex items-center gap-1">
            <button
                @click="prev()"
                class="w-7 h-7 flex items-center justify-center rounded-lg border border-white/10 text-white/40 hover:text-white/70 hover:border-white/20 transition-colors"
                aria-label="Previous month"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            <h2
                class="text-sm font-semibold text-white/70 min-w-[120px] text-center"
                x-text="monthLabel"
            ></h2>

            <button
                @click="next()"
                class="w-7 h-7 flex items-center justify-center rounded-lg border border-white/10 text-white/40 hover:text-white/70 hover:border-white/20 transition-colors"
                aria-label="Next month"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <div class="flex-1"></div>

        {{-- Right: Today + New Task --}}
        <div class="flex items-center gap-2">
            <button
                @click="goToday()"
                class="px-3 py-1.5 rounded-lg border border-white/10 text-white/50 hover:text-white/80 hover:border-white/20 text-xs font-medium transition-colors"
            >Today</button>

            @if($canEdit)
            <button
                @click="$dispatch('open-create-task')"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium transition-colors border border-orange-500/20"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Task
            </button>
            @endif
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- CALENDAR GRID                                                     --}}
    {{-- ================================================================ --}}
    <div class="flex-1 overflow-auto px-4 py-4">

        {{-- Day-of-week headers --}}
        <div class="grid grid-cols-7 mb-1">
            <template x-for="dow in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="dow">
                <div class="text-center py-1.5">
                    <span class="text-[10px] font-semibold text-white/30 uppercase tracking-wide" x-text="dow"></span>
                </div>
            </template>
        </div>

        {{-- Calendar weeks --}}
        <div class="grid grid-cols-7 gap-px bg-white/5 rounded-xl overflow-hidden border border-white/5">
            <template x-for="day in calendarDays()" :key="day.dateStr">
                <div
                    class="min-h-24 bg-neutral-950 p-2 flex flex-col gap-1 relative group transition-colors hover:bg-white/[0.015]"
                    :class="!day.isCurrentMonth ? 'opacity-50' : ''"
                >
                    {{-- Day number --}}
                    <div class="flex justify-end mb-0.5">
                        <span
                            class="text-[11px] font-medium leading-none flex items-center justify-center"
                            :class="day.isToday
                                ? 'w-5 h-5 rounded-full bg-orange-500 text-white font-bold'
                                : (day.isCurrentMonth ? 'text-white/40' : 'text-white/15')"
                            x-text="day.dayNum"
                        ></span>
                    </div>

                    {{-- Task chips (up to 3) --}}
                    <template x-for="task in tasksForDay(day.dateStr).slice(0, 3)" :key="task.id">
                        <button
                            class="w-full text-left flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium truncate transition-opacity hover:opacity-80"
                            :class="chipClass(task)"
                            @click.stop="openTask(task)"
                            :title="task.title"
                        >
                            <span
                                class="w-1.5 h-1.5 rounded-full shrink-0"
                                :class="task.is_completed ? 'bg-white/20' : priorityDotClass(task.priority)"
                            ></span>
                            <span class="truncate" x-text="task.title"></span>
                        </button>
                    </template>

                    {{-- +N more --}}
                    <template x-if="tasksForDay(day.dateStr).length > 3">
                        <button
                            class="text-[10px] text-white/35 hover:text-white/60 text-left px-1.5 py-0.5 transition-colors"
                            @click.stop="showMore(day.dateStr, tasksForDay(day.dateStr))"
                            x-text="`+${tasksForDay(day.dateStr).length - 3} more`"
                        ></button>
                    </template>
                </div>
            </template>
        </div>

    </div>{{-- end calendar grid --}}

    {{-- ================================================================ --}}
    {{-- OVERFLOW POPUP                                                    --}}
    {{-- ================================================================ --}}
    <div
        x-show="showOverflow"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="showOverflow = false"
        @keydown.escape.window="showOverflow = false"
    >
        <div class="absolute inset-0 bg-black/50" @click="showOverflow = false"></div>

        <div
            class="relative bg-neutral-900 border border-white/10 rounded-2xl w-72 shadow-2xl overflow-hidden"
            @click.stop
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-white/5">
                <span class="text-xs font-semibold text-white/70" x-text="overflowDayLabel"></span>
                <button
                    @click="showOverflow = false"
                    class="w-6 h-6 flex items-center justify-center rounded-lg text-white/30 hover:text-white/70 hover:bg-white/5 transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Task list --}}
            <div class="p-2 max-h-80 overflow-y-auto space-y-1">
                <template x-for="task in overflowTasks" :key="task.id">
                    <button
                        class="w-full text-left flex items-center gap-2 px-2 py-1.5 rounded-lg text-[11px] font-medium truncate transition-opacity hover:opacity-80"
                        :class="chipClass(task)"
                        @click="showOverflow = false; openTask(task)"
                        :title="task.title"
                    >
                        <span
                            class="w-1.5 h-1.5 rounded-full shrink-0"
                            :class="task.is_completed ? 'bg-white/20' : priorityDotClass(task.priority)"
                        ></span>
                        <span class="truncate" x-text="task.title"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

</div>{{-- end root --}}

{{-- ================================================================ --}}
{{-- TASK DETAIL SLIDE-OVER                                            --}}
{{-- ================================================================ --}}
<x-projects.task-detail
    :project="$project"
    :members="$project->members"
    :milestones="$project->milestones"
    :labels="$project->labels"
    :canEdit="$canEdit"
/>

<script>
function calendarView() {
    return {
        year:  new Date().getFullYear(),
        month: new Date().getMonth(), // 0-11

        tasks:        @js($tasks),
        selectedTask: null,

        // Overflow popup
        showOverflow:  false,
        overflowDay:   null,
        overflowTasks: [],

        // ── COMPUTED ──────────────────────────────────────────────

        get monthLabel() {
            const months = ['January','February','March','April','May','June',
                            'July','August','September','October','November','December'];
            return `${months[this.month]} ${this.year}`;
        },

        get overflowDayLabel() {
            if (!this.overflowDay) return '';
            const d = new Date(this.overflowDay + 'T00:00:00');
            const months = ['Jan','Feb','Mar','Apr','May','Jun',
                            'Jul','Aug','Sep','Oct','Nov','Dec'];
            const days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            return `${days[d.getDay()]}, ${months[d.getMonth()]} ${d.getDate()}`;
        },

        // ── METHODS ───────────────────────────────────────────────

        init() {
            const today = new Date();
            this.year  = today.getFullYear();
            this.month = today.getMonth();
        },

        prev() {
            if (this.month === 0) { this.month = 11; this.year--; }
            else { this.month--; }
        },

        next() {
            if (this.month === 11) { this.month = 0; this.year++; }
            else { this.month++; }
        },

        goToday() {
            const today = new Date();
            this.year  = today.getFullYear();
            this.month = today.getMonth();
        },

        calendarDays() {
            const todayStr   = this._todayStr();
            const firstOfMonth = new Date(this.year, this.month, 1);
            const lastOfMonth  = new Date(this.year, this.month + 1, 0);

            // Start from Sunday on or before the 1st
            const startDate = new Date(firstOfMonth);
            startDate.setDate(startDate.getDate() - startDate.getDay());

            // End on Saturday on or after last day
            const endDate = new Date(lastOfMonth);
            const endDow  = endDate.getDay();
            if (endDow !== 6) endDate.setDate(endDate.getDate() + (6 - endDow));

            const days = [];
            const cur  = new Date(startDate);
            while (cur <= endDate) {
                const ds = this._dateToStr(cur);
                days.push({
                    date:           new Date(cur),
                    dateStr:        ds,
                    dayNum:         cur.getDate(),
                    isCurrentMonth: cur.getMonth() === this.month && cur.getFullYear() === this.year,
                    isToday:        ds === todayStr,
                });
                cur.setDate(cur.getDate() + 1);
            }
            return days;
        },

        tasksForDay(dateStr) {
            const priorityOrder = { critical: 0, high: 1, medium: 2, low: 3, none: 4 };
            return this.tasks
                .filter(t => t.due_date === dateStr)
                .sort((a, b) => {
                    const pa = priorityOrder[a.priority] ?? 4;
                    const pb = priorityOrder[b.priority] ?? 4;
                    return pa - pb;
                });
        },

        isToday(dateStr) {
            return dateStr === this._todayStr();
        },

        chipClass(task) {
            if (task.is_completed) {
                return 'bg-white/5 text-white/20 line-through';
            }
            const map = {
                critical: 'bg-red-500/20 text-red-400',
                high:     'bg-orange-500/20 text-orange-400',
                medium:   'bg-amber-500/20 text-amber-400',
                low:      'bg-blue-500/20 text-blue-400',
                none:     'bg-white/5 text-white/30',
            };
            return map[task.priority] || map.none;
        },

        priorityDotClass(p) {
            const map = {
                critical: 'bg-red-500',
                high:     'bg-orange-500',
                medium:   'bg-amber-500',
                low:      'bg-blue-500',
                none:     'bg-white/20',
            };
            return map[p] || map.none;
        },

        showMore(dateStr, tasks) {
            this.overflowDay   = dateStr;
            this.overflowTasks = tasks;
            this.showOverflow  = true;
        },

        async openTask(task) {
            try {
                const res = await fetch(`/api/project-tasks/${task.id}`, {
                    headers: {
                        'Accept':           'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (res.ok) {
                    this.selectedTask = await res.json();
                } else {
                    this.selectedTask = task;
                }
            } catch (e) {
                this.selectedTask = task;
            }
        },

        async updateTask(task, data) {
            try {
                const res = await fetch(`/api/project-tasks/${task.id}`, {
                    method:  'PATCH',
                    headers: {
                        'Content-Type':     'application/json',
                        'Accept':           'application/json',
                        'X-CSRF-TOKEN':     document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(data),
                });
                if (res.ok) {
                    const updated = await res.json();
                    const idx = this.tasks.findIndex(t => t.id === task.id);
                    if (idx !== -1) this.tasks[idx] = { ...this.tasks[idx], ...updated };
                }
            } catch (e) { /* silent */ }
        },

        // ── HELPERS ───────────────────────────────────────────────

        _todayStr() {
            return this._dateToStr(new Date());
        },

        _dateToStr(d) {
            const y  = d.getFullYear();
            const m  = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${dd}`;
        },
    };
}
</script>

</x-layouts.smartprojects>
