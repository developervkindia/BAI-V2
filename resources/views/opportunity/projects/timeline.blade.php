<x-layouts.opportunity title="Timeline" :project="$project" currentView="timeline">

@php
$tlData = [
    'projectId' => $project->id,
    'sections' => $project->sections->map(fn($s) => [
        'id' => $s->id, 'name' => $s->name,
        'tasks' => $s->tasks->map(fn($t) => [
            'id' => $t->id, 'title' => $t->title, 'status' => $t->status,
            'start_date' => $t->start_date?->format('Y-m-d'),
            'due_date' => $t->due_date?->format('Y-m-d'),
            'assignee' => $t->assignee ? ['id' => $t->assignee->id, 'name' => $t->assignee->name] : null,
        ])->values(),
    ])->values(),
];
@endphp

<div class="flex-1 flex flex-col overflow-hidden" x-data="oppTimeline({{ Js::from($tlData) }})">

    {{-- Toolbar --}}
    <div class="shrink-0 flex items-center gap-3 px-5 py-2 border-b border-white/[0.06]">
        <button @click="prevWeek()" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/65">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button @click="goToday()" class="px-3 py-1 rounded-lg text-[12px] text-white/40 border border-white/[0.08] hover:bg-white/[0.04]">Today</button>
        <button @click="nextWeek()" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/65">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        <span class="text-[13px] text-white/50" x-text="rangeLabel"></span>
        <div class="flex-1"></div>
        <div class="flex items-center gap-1 bg-white/[0.04] rounded-lg p-0.5">
            <button @click="zoom = 'weeks'" :class="zoom==='weeks'?'bg-white/[0.08] text-white/70':'text-white/35'" class="px-2.5 py-1 rounded-md text-[11px] font-medium transition-colors">Weeks</button>
            <button @click="zoom = 'months'" :class="zoom==='months'?'bg-white/[0.08] text-white/70':'text-white/35'" class="px-2.5 py-1 rounded-md text-[11px] font-medium transition-colors">Months</button>
        </div>
    </div>

    {{-- Timeline content --}}
    <div class="flex-1 flex overflow-hidden">
        {{-- Left: Task list --}}
        <div class="w-64 shrink-0 border-r border-white/[0.06] overflow-y-auto">
            <div class="py-2">
                <template x-for="section in sections" :key="section.id">
                    <div>
                        <div class="px-3 py-2 text-[12px] font-semibold text-white/50" x-text="section.name"></div>
                        <template x-for="task in section.tasks" :key="task.id">
                            <div class="flex items-center gap-2 px-3 py-1.5 hover:bg-white/[0.02] cursor-pointer text-[12px]"
                                 :class="task.status==='complete'?'text-white/25 line-through':'text-white/65'" @click="openTask(task)">
                                <div class="w-[14px] h-[14px] rounded-full border shrink-0"
                                    :class="task.status==='complete'?'bg-teal-500 border-teal-500':'border-white/20'"></div>
                                <span class="truncate" x-text="task.title"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Right: Gantt area --}}
        <div class="flex-1 overflow-x-auto overflow-y-auto" x-ref="ganttScroll">
            {{-- Date headers --}}
            <div class="sticky top-0 z-10 flex bg-[#1A1A2E] border-b border-white/[0.06]" :style="'width:' + totalWidth + 'px'">
                <template x-for="day in visibleDays" :key="day.str">
                    <div class="text-center border-r border-white/[0.04] shrink-0" :style="'width:' + dayWidth + 'px'">
                        <div class="text-[9px] text-white/20 py-0.5" x-text="day.month"></div>
                        <div class="text-[11px] py-0.5" :class="day.isToday ? 'text-teal-400 font-bold' : (day.isWeekend ? 'text-white/15' : 'text-white/35')" x-text="day.d"></div>
                    </div>
                </template>
            </div>

            {{-- Task bars --}}
            <div class="relative" :style="'width:' + totalWidth + 'px'">
                {{-- Today line --}}
                <div class="absolute top-0 bottom-0 w-px bg-teal-500/40 z-10" :style="'left:' + todayOffset + 'px'"></div>

                <template x-for="section in sections" :key="section.id">
                    <div>
                        <div class="h-8"></div>
                        <template x-for="task in section.tasks" :key="task.id">
                            <div class="h-8 relative flex items-center">
                                <template x-if="task.start_date || task.due_date">
                                    <div class="absolute h-5 rounded-full cursor-pointer hover:opacity-80 transition-opacity"
                                         :style="taskBarStyle(task)"
                                         :class="task.status==='complete'?'bg-teal-500/30':'bg-teal-500/60'"
                                         @click="openTask(task)">
                                        <span class="text-[9px] text-white/80 px-2 leading-5 truncate block" x-text="task.title"></span>
                                    </div>
                                </template>
                                <template x-if="!task.start_date && !task.due_date">
                                    <div class="absolute h-5 w-2 rounded-full bg-white/[0.06]" :style="'left:' + todayOffset + 'px'"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Detail panel --}}
    <div x-show="sel" x-cloak class="fixed inset-y-0 right-0 w-[480px] bg-[#1A1A2E] border-l border-white/[0.06] z-50 overflow-y-auto shadow-2xl"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">
        <template x-if="sel">
            <div>
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-[#1A1A2E] border-b border-white/[0.06]">
                    <button @click="toggleComp(sel)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium border"
                        :class="sel.status==='complete'?'border-teal-500/30 bg-teal-500/10 text-teal-400':'border-white/[0.1] text-white/50'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="sel.status==='complete'?'Completed':'Mark complete'"></span></button>
                    <div class="flex-1"></div>
                    <button @click="sel=null" class="p-1.5 text-white/25 hover:text-white/50"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="px-5 pt-4 pb-2"><input type="text" x-model="sel.title" @blur="upd('title',sel.title)" class="w-full bg-transparent text-[18px] font-semibold text-white/90 focus:outline-none"/></div>
                <div class="px-5 space-y-3 pb-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-4"><span class="text-[12px] text-white/35 w-20">Start date</span><input type="date" :value="sel.start_date" @change="upd('start_date',$event.target.value)" class="px-2 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[12px] text-white/60 focus:outline-none"/></div>
                    <div class="flex items-center gap-4"><span class="text-[12px] text-white/35 w-20">Due date</span><input type="date" :value="sel.due_date" @change="upd('due_date',$event.target.value)" class="px-2 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[12px] text-white/60 focus:outline-none"/></div>
                    <div class="flex items-center gap-4"><span class="text-[12px] text-white/35 w-20">Assignee</span><span class="text-[13px] text-white/65" x-text="sel.assignee?.name||'No assignee'"></span></div>
                </div>
                <div class="px-5 py-4"><span class="text-[12px] text-white/35 block mb-2">Description</span>
                    <textarea x-model="sel.description" @blur="upd('description',sel.description)" rows="3" placeholder="Add description..." class="w-full bg-white/[0.03] border border-white/[0.06] rounded-lg px-3 py-2 text-[13px] text-white/65 placeholder-white/20 focus:outline-none resize-none"></textarea></div>
            </div>
        </template>
    </div>
    <div x-show="sel" x-cloak @click="sel=null" class="fixed inset-0 bg-black/20 z-40"></div>
</div>

<script>
function oppTimeline(data) {
    return {
        sections: data.sections, sel: null, zoom: 'weeks',
        startDate: new Date(), dayWidth: 36,

        init() {
            const d = new Date(); d.setDate(d.getDate() - 14);
            d.setHours(0,0,0,0); this.startDate = d;
        },

        get totalDays() { return this.zoom === 'weeks' ? 42 : 90; },
        get totalWidth() { return this.totalDays * this.dayWidth; },
        get rangeLabel() {
            const e = new Date(this.startDate); e.setDate(e.getDate() + this.totalDays - 1);
            return this.startDate.toLocaleDateString('en-US',{month:'short',day:'numeric'}) + ' — ' + e.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
        },

        get visibleDays() {
            const days = [];
            for (let i = 0; i < this.totalDays; i++) {
                const d = new Date(this.startDate); d.setDate(d.getDate() + i);
                days.push({
                    str: d.toISOString().slice(0,10), d: d.getDate(),
                    month: d.getDate() === 1 ? d.toLocaleDateString('en-US',{month:'short'}) : '',
                    isToday: d.toISOString().slice(0,10) === new Date().toISOString().slice(0,10),
                    isWeekend: d.getDay() === 0 || d.getDay() === 6,
                });
            }
            return days;
        },

        get todayOffset() {
            const today = new Date(); today.setHours(0,0,0,0);
            const diff = Math.round((today - this.startDate) / 86400000);
            return diff * this.dayWidth;
        },

        taskBarStyle(task) {
            const sd = task.start_date ? new Date(task.start_date+'T00:00:00') : (task.due_date ? new Date(task.due_date+'T00:00:00') : new Date());
            const ed = task.due_date ? new Date(task.due_date+'T00:00:00') : sd;
            const startDiff = Math.round((sd - this.startDate) / 86400000);
            const dur = Math.max(Math.round((ed - sd) / 86400000) + 1, 1);
            return `left:${startDiff * this.dayWidth}px; width:${dur * this.dayWidth}px`;
        },

        prevWeek() { const d = new Date(this.startDate); d.setDate(d.getDate() - 7); this.startDate = d; },
        nextWeek() { const d = new Date(this.startDate); d.setDate(d.getDate() + 7); this.startDate = d; },
        goToday() { const d = new Date(); d.setDate(d.getDate() - 14); d.setHours(0,0,0,0); this.startDate = d; },

        async openTask(task) { this.sel = { ...task }; const r = await this.api('GET','/api/opp/tasks/'+task.id); if(r?.task) this.sel = r.task; },
        async toggleComp(task) {
            const r = await this.api('POST','/api/opp/tasks/'+task.id+'/complete');
            if(r?.task){Object.assign(task,r.task);for(const s of this.sections){const i=s.tasks.findIndex(t=>t.id===r.task.id);if(i!==-1){Object.assign(s.tasks[i],r.task);break;}}if(this.sel?.id===task.id)Object.assign(this.sel,r.task);}
        },
        async upd(f,v) {
            if(!this.sel) return;
            const r = await this.api('PUT','/api/opp/tasks/'+this.sel.id,{[f]:v});
            if(r?.task){Object.assign(this.sel,r.task);for(const s of this.sections){const i=s.tasks.findIndex(t=>t.id===r.task.id);if(i!==-1){Object.assign(s.tasks[i],r.task);break;}}}
        },
        async api(m,u,b=null){try{const o={method:m,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}};if(b)o.body=JSON.stringify(b);const r=await fetch(u,o);if(!r.ok)return null;return await r.json();}catch(e){return null;}},
    };
}
</script>

</x-layouts.opportunity>
