<x-layouts.smartprojects :project="$project" currentView="timeline" :canEdit="$canEdit">

<div
    class="flex flex-col"
    style="height: calc(100vh - 176px)"
    x-data="timelineView()"
    x-init="init()"
>

    {{-- ================================================================ --}}
    {{-- TOOLBAR                                                           --}}
    {{-- ================================================================ --}}
    <div class="shrink-0 flex items-center gap-3 px-4 py-3 border-b border-white/5 bg-neutral-950/80 backdrop-blur-sm">

        {{-- Left: title + task count --}}
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="text-xs font-semibold text-white/70">Timeline</span>
            <span class="text-[10px] text-white/30 bg-white/5 px-1.5 py-0.5 rounded-full font-mono"
                  x-text="totalTaskCount + ' tasks'"></span>
            <span class="text-[10px] text-white/25" x-text="rangeLabel"></span>
        </div>

        <div class="flex-1"></div>

        {{-- Center: Zoom buttons --}}
        <div class="flex items-center gap-0.5 rounded-lg border border-white/10 bg-white/5 p-0.5">
            <template x-for="z in ['week','month','quarter']" :key="z">
                <button
                    @click="setZoom(z)"
                    class="px-3 py-1 rounded-md text-[11px] font-medium transition-colors capitalize"
                    :class="zoom === z
                        ? 'bg-orange-500/25 text-orange-400 border border-orange-500/30'
                        : 'text-white/40 hover:text-white/70'"
                    x-text="z"
                ></button>
            </template>
        </div>

        <div class="flex-1"></div>

        {{-- Right: Today + Add Task --}}
        <div class="flex items-center gap-2">
            <button
                @click="scrollToToday()"
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
                Add Task
            </button>
            @endif
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- CONTENT AREA                                                      --}}
    {{-- ================================================================ --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- ── LEFT PANEL (task names) ─────────────────────────────── --}}
        <div class="w-56 shrink-0 border-r border-white/5 overflow-y-auto bg-neutral-950 z-10">

            {{-- Spacer matching date header --}}
            <div class="h-10 border-b border-white/5 flex items-center px-3">
                <span class="text-[10px] text-white/25 font-medium">Task</span>
            </div>

            {{-- Sections --}}
            <template x-for="section in tasksBySection" :key="section.listId">
                <div>
                    {{-- Section header --}}
                    <div
                        class="flex items-center gap-1.5 px-3 h-8 bg-white/[0.02] border-b border-white/5 cursor-pointer hover:bg-white/[0.04] transition-colors group"
                        @click="toggleSection(section.listId)"
                    >
                        <svg
                            class="w-3 h-3 text-white/30 transition-transform shrink-0"
                            :class="collapsedSections.has(section.listId) ? '-rotate-90' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <span class="text-[11px] font-semibold text-white/50 truncate" x-text="section.listName"></span>
                        <span class="ml-auto text-[9px] text-white/25 font-mono shrink-0" x-text="section.tasks.length"></span>
                    </div>

                    {{-- Task rows --}}
                    <template x-if="!collapsedSections.has(section.listId)">
                        <div>
                            <template x-for="task in section.tasks" :key="task.id">
                                <div
                                    class="flex items-center gap-2 px-3 h-10 border-b border-white/[0.03] hover:bg-white/[0.03] cursor-pointer transition-colors group"
                                    @click="openTask(task)"
                                >
                                    {{-- Priority dot --}}
                                    <span
                                        class="w-1.5 h-1.5 rounded-full shrink-0"
                                        :class="priorityDotClass(task.priority)"
                                    ></span>
                                    {{-- Title --}}
                                    <span
                                        class="text-xs text-white/60 truncate leading-none group-hover:text-white/80 transition-colors"
                                        :class="task.is_completed ? 'line-through text-white/25' : ''"
                                        x-text="task.title"
                                    ></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            {{-- No-dates section --}}
            <template x-if="tasksNoDates().length > 0">
                <div>
                    <div class="flex items-center gap-1.5 px-3 h-8 bg-white/[0.02] border-b border-white/5 cursor-pointer hover:bg-white/[0.04] transition-colors"
                         @click="toggleSection('__no_dates__')">
                        <svg
                            class="w-3 h-3 text-white/30 transition-transform shrink-0"
                            :class="collapsedSections.has('__no_dates__') ? '-rotate-90' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <span class="text-[11px] font-semibold text-white/30 truncate">No dates</span>
                        <span class="ml-auto text-[9px] text-white/20 font-mono shrink-0" x-text="tasksNoDates().length"></span>
                    </div>
                    <template x-if="!collapsedSections.has('__no_dates__')">
                        <div>
                            <template x-for="task in tasksNoDates()" :key="task.id">
                                <div
                                    class="flex items-center gap-2 px-3 h-10 border-b border-white/[0.03] hover:bg-white/[0.03] cursor-pointer transition-colors group"
                                    @click="openTask(task)"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="priorityDotClass(task.priority)"></span>
                                    <span class="text-xs text-white/40 truncate leading-none group-hover:text-white/60 transition-colors"
                                          :class="task.is_completed ? 'line-through text-white/20' : ''"
                                          x-text="task.title"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- ── RIGHT PANEL (Gantt) ──────────────────────────────────── --}}
        <div
            x-ref="ganttScroll"
            class="flex-1 overflow-x-auto overflow-y-auto relative"
        >
            <div :style="`width: ${totalWidth}px; min-width: 100%;`" class="relative">

                {{-- DATE HEADER ROW --}}
                <div class="sticky top-0 z-20 h-10 flex bg-neutral-900 border-b border-white/5">
                    <template x-for="(day, idx) in visibleDays" :key="day.dateStr">
                        <div
                            class="shrink-0 flex items-center justify-center border-r border-white/5 relative"
                            :style="`width: ${dayWidth}px`"
                            :class="day.isToday ? 'bg-orange-500/10' : (day.isWeekend ? 'bg-white/[0.01]' : '')"
                        >
                            <template x-if="zoom === 'week'">
                                <div class="flex flex-col items-center">
                                    <span class="text-[9px] text-white/30 uppercase" x-text="day.dayAbbr"></span>
                                    <span
                                        class="text-[11px] font-medium leading-none mt-0.5"
                                        :class="day.isToday ? 'text-orange-400 font-bold' : 'text-white/50'"
                                        x-text="day.dayNum"
                                    ></span>
                                </div>
                            </template>
                            <template x-if="zoom === 'month'">
                                <div class="flex flex-col items-center" x-show="idx === 0 || day.dayNum === 1 || dayWidth >= 28">
                                    <span class="text-[9px] text-white/25 uppercase leading-none" x-text="dayWidth >= 28 ? day.dayAbbr : ''"></span>
                                    <span
                                        class="text-[10px] font-medium leading-none"
                                        :class="day.isToday ? 'text-orange-400 font-bold' : (day.dayNum === 1 ? 'text-white/60' : 'text-white/35')"
                                        x-text="day.dayNum === 1 ? day.monthAbbr : day.dayNum"
                                    ></span>
                                </div>
                            </template>
                            <template x-if="zoom === 'quarter'">
                                <div x-show="day.dayNum === 1 || idx === 0">
                                    <span class="text-[10px] text-white/40 font-medium" x-text="day.monthAbbr + ' ' + day.year"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- GANTT ROWS PER SECTION --}}
                <template x-for="section in tasksBySection" :key="'g-' + section.listId">
                    <div>
                        {{-- Section header row (matches left panel) --}}
                        <div class="h-8 bg-white/[0.02] border-b border-white/5 relative">
                            <div class="absolute inset-0" :style="`background: repeating-linear-gradient(90deg, transparent, transparent ${dayWidth - 1}px, rgba(255,255,255,0.02) ${dayWidth - 1}px, rgba(255,255,255,0.02) ${dayWidth}px)`"></div>
                        </div>

                        {{-- Task bar rows --}}
                        <template x-if="!collapsedSections.has(section.listId)">
                            <div>
                                <template x-for="task in section.tasks" :key="'gr-' + task.id">
                                    <div class="relative h-10 border-b border-white/[0.03] hover:bg-white/[0.015] transition-colors group">
                                        {{-- Column grid lines --}}
                                        <div class="absolute inset-0 pointer-events-none" :style="`background: repeating-linear-gradient(90deg, transparent, transparent ${dayWidth - 1}px, rgba(255,255,255,0.02) ${dayWidth - 1}px, rgba(255,255,255,0.02) ${dayWidth}px)`"></div>

                                        {{-- Today highlight column --}}
                                        <div
                                            class="absolute top-0 bottom-0 pointer-events-none"
                                            :style="`left: ${todayOffset}px; width: ${dayWidth}px;`"
                                            style="background: rgba(245,158,11,0.04)"
                                        ></div>

                                        {{-- Gantt bar (task with start + due) --}}
                                        <template x-if="task.start_date && task.due_date">
                                            <div
                                                class="absolute top-1/2 -translate-y-1/2 h-6 rounded-md flex items-center px-2 cursor-pointer overflow-hidden transition-opacity hover:opacity-90"
                                                :style="barStyle(task)"
                                                @click="openTask(task)"
                                                :title="task.title"
                                            >
                                                <span
                                                    class="text-[10px] font-medium text-white/90 truncate leading-none select-none"
                                                    x-text="task.title"
                                                ></span>
                                            </div>
                                        </template>

                                        {{-- Milestone diamond (only due_date) --}}
                                        <template x-if="!task.start_date && task.due_date">
                                            <div
                                                class="absolute top-1/2 -translate-y-1/2 cursor-pointer"
                                                :style="markerStyle(task)"
                                                @click="openTask(task)"
                                                :title="task.title"
                                            >
                                                <div
                                                    class="w-4 h-4 rotate-45 rounded-sm"
                                                    :class="priorityBarBg(task.priority)"
                                                    style="opacity: 0.85"
                                                ></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- NO-DATES SECTION ROWS --}}
                <template x-if="tasksNoDates().length > 0 && !collapsedSections.has('__no_dates__')">
                    <div>
                        {{-- Section header --}}
                        <div class="h-8 bg-white/[0.02] border-b border-white/5 relative">
                            <div class="absolute inset-0" :style="`background: repeating-linear-gradient(90deg, transparent, transparent ${dayWidth - 1}px, rgba(255,255,255,0.02) ${dayWidth - 1}px, rgba(255,255,255,0.02) ${dayWidth}px)`"></div>
                        </div>
                        <template x-for="task in tasksNoDates()" :key="'nd-' + task.id">
                            <div class="relative h-10 border-b border-white/[0.03]">
                                <div class="absolute inset-0 pointer-events-none" :style="`background: repeating-linear-gradient(90deg, transparent, transparent ${dayWidth - 1}px, rgba(255,255,255,0.02) ${dayWidth - 1}px, rgba(255,255,255,0.02) ${dayWidth}px)`"></div>
                                <div class="absolute inset-0 pointer-events-none" :style="`left: ${todayOffset}px; width: ${dayWidth}px; background: rgba(245,158,11,0.04)`"></div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- TODAY VERTICAL LINE --}}
                <div
                    class="absolute top-0 bottom-0 pointer-events-none z-10"
                    :style="`left: ${todayOffset + Math.floor(dayWidth / 2)}px; width: 2px;`"
                    style="background: rgba(245,158,11,0.5)"
                ></div>

            </div>{{-- end width container --}}
        </div>{{-- end gantt scroll --}}

    </div>{{-- end content area --}}
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
function timelineView() {
    return {
        zoom: 'month',
        viewStartDate: null,
        selectedTask: null,
        collapsedSections: new Set(),

        // Raw data from PHP
        rawTaskLists: @js($project->taskLists->map(fn($tl) => [
            'id'    => $tl->id,
            'name'  => $tl->name,
            'tasks' => $tl->tasks->map(fn($t) => [
                'id'           => $t->id,
                'title'        => $t->title,
                'start_date'   => $t->start_date  ? $t->start_date->format('Y-m-d')  : null,
                'due_date'     => $t->due_date    ? $t->due_date->format('Y-m-d')    : null,
                'priority'     => $t->priority    ?? 'none',
                'issue_type'   => $t->issue_type  ?? 'task',
                'status'       => $t->status      ?? 'todo',
                'is_completed' => (bool) $t->is_completed,
                'assignee'     => $t->assignee ? ['id' => $t->assignee->id, 'name' => $t->assignee->name] : null,
            ])->values()->all(),
        ])->values()),

        // ── COMPUTED ──────────────────────────────────────────────

        get tasksBySection() {
            return this.rawTaskLists.map(tl => ({
                listId:   tl.id,
                listName: tl.name,
                tasks:    tl.tasks,
            }));
        },

        get totalTaskCount() {
            return this.rawTaskLists.reduce((sum, tl) => sum + tl.tasks.length, 0);
        },

        get dayWidth() {
            if (this.zoom === 'week')    return 80;
            if (this.zoom === 'month')   return 32;
            if (this.zoom === 'quarter') return 18;
            return 32;
        },

        get visibleDays() {
            if (!this.viewStartDate) return [];
            const count = this.zoom === 'week' ? 14 : this.zoom === 'month' ? 35 : 90;
            const days  = [];
            const today = this._todayStr();
            for (let i = 0; i < count; i++) {
                const d    = new Date(this.viewStartDate);
                d.setDate(d.getDate() + i);
                const ds   = this._dateToStr(d);
                const dow  = d.getDay();
                days.push({
                    dateStr:    ds,
                    dayNum:     d.getDate(),
                    dayAbbr:    ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][dow],
                    monthAbbr:  ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][d.getMonth()],
                    year:       d.getFullYear(),
                    isToday:    ds === today,
                    isWeekend:  dow === 0 || dow === 6,
                });
            }
            return days;
        },

        get totalWidth() {
            return Math.max(this.visibleDays.length * this.dayWidth, 600);
        },

        get rangeLabel() {
            if (!this.visibleDays.length) return '';
            const first = this.visibleDays[0];
            const last  = this.visibleDays[this.visibleDays.length - 1];
            return `${first.monthAbbr} ${first.dayNum} – ${last.monthAbbr} ${last.dayNum}, ${last.year}`;
        },

        get todayOffset() {
            return this.dayOffset(this._todayStr());
        },

        // ── METHODS ───────────────────────────────────────────────

        init() {
            const today = new Date();
            today.setHours(0,0,0,0);
            this._setViewStart(today);
            this.$nextTick(() => this.scrollToToday());

            // Listen for openTask events from child components
            this.$el.addEventListener('openTask', e => {
                if (e.detail) this.openTask(e.detail);
            });
        },

        setZoom(z) {
            this.zoom = z;
            const today = new Date();
            today.setHours(0,0,0,0);
            this._setViewStart(today);
            this.$nextTick(() => this.scrollToToday());
        },

        _setViewStart(today) {
            const d = new Date(today);
            if (this.zoom === 'week')    d.setDate(d.getDate() - 3);
            else if (this.zoom === 'month')   d.setDate(d.getDate() - 7);
            else if (this.zoom === 'quarter') d.setDate(d.getDate() - 14);
            d.setHours(0,0,0,0);
            this.viewStartDate = d;
        },

        scrollToToday() {
            const el = this.$refs.ganttScroll;
            if (!el) return;
            const offset = this.todayOffset - (el.clientWidth / 2) + (this.dayWidth / 2);
            el.scrollLeft = Math.max(0, offset);
        },

        toggleSection(id) {
            const s = new Set(this.collapsedSections);
            if (s.has(id)) s.delete(id);
            else s.add(id);
            this.collapsedSections = s;
        },

        tasksNoDates() {
            return this.rawTaskLists.flatMap(tl =>
                tl.tasks.filter(t => !t.start_date && !t.due_date)
            );
        },

        dayOffset(dateStr) {
            if (!this.viewStartDate || !dateStr) return 0;
            const target = new Date(dateStr + 'T00:00:00');
            const start  = new Date(this.viewStartDate);
            start.setHours(0,0,0,0);
            const diff = Math.round((target - start) / 86400000);
            return diff * this.dayWidth;
        },

        barStyle(task) {
            if (!task.start_date || !task.due_date) return null;
            const left   = this.dayOffset(task.start_date);
            const right  = this.dayOffset(task.due_date) + this.dayWidth;
            const width  = Math.max(right - left, this.dayWidth);
            const bg     = this._priorityBarColor(task.priority);
            return `left: ${left}px; width: ${width}px; background: ${bg};`;
        },

        markerStyle(task) {
            if (!task.due_date) return null;
            const left = this.dayOffset(task.due_date) + Math.floor(this.dayWidth / 2) - 8;
            return `left: ${left}px;`;
        },

        priorityBarBg(p) {
            const map = {
                critical: 'bg-red-500/60',
                high:     'bg-orange-500/60',
                medium:   'bg-orange-500/60',
                low:      'bg-blue-500/60',
                none:     'bg-indigo-500/60',
            };
            return map[p] || map.none;
        },

        _priorityBarColor(p) {
            const map = {
                critical: 'rgba(239,68,68,0.55)',
                high:     'rgba(249,115,22,0.55)',
                medium:   'rgba(245,158,11,0.55)',
                low:      'rgba(59,130,246,0.55)',
                none:     'rgba(99,102,241,0.55)',
            };
            return map[p] || map.none;
        },

        priorityDotClass(p) {
            const map = {
                critical: 'bg-red-500',
                high:     'bg-orange-500',
                medium:   'bg-orange-500',
                low:      'bg-blue-500',
                none:     'bg-white/20',
            };
            return map[p] || map.none;
        },

        async openTask(task) {
            try {
                const res  = await fetch(`/api/project-tasks/${task.id}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
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
                    // Refresh task in rawTaskLists
                    for (const tl of this.rawTaskLists) {
                        const idx = tl.tasks.findIndex(t => t.id === task.id);
                        if (idx !== -1) {
                            tl.tasks[idx] = { ...tl.tasks[idx], ...updated };
                        }
                    }
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
