<x-layouts.opportunity title="Calendar" :project="$project" currentView="calendar">

@php
$calTasks = $project->tasks->map(fn($t) => [
    'id' => $t->id, 'title' => $t->title, 'status' => $t->status,
    'due_date' => $t->due_date?->format('Y-m-d'),
    'assignee' => $t->assignee ? ['id' => $t->assignee->id, 'name' => $t->assignee->name] : null,
    'project_id' => $t->project_id,
])->values();
@endphp

<div class="flex-1 flex flex-col overflow-hidden" x-data="oppCal({{ Js::from(['tasks' => $calTasks, 'projectId' => $project->id]) }})">

    <div class="shrink-0 flex items-center gap-3 px-5 py-3 border-b border-white/[0.06]">
        <button @click="prevMonth()" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/65">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <h2 class="text-[15px] font-semibold text-white/80 min-w-[140px] text-center" x-text="monthLabel"></h2>
        <button @click="nextMonth()" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/65">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        <button @click="goToday()" class="px-3 py-1 rounded-lg text-[12px] text-white/40 border border-white/[0.08] hover:bg-white/[0.04]">Today</button>
    </div>

    <div class="shrink-0 grid grid-cols-7 border-b border-white/[0.06]">
        <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']"><div class="text-center py-2 text-[11px] font-semibold text-white/30 uppercase" x-text="d"></div></template>
    </div>

    <div class="flex-1 grid grid-cols-7 overflow-y-auto">
        <template x-for="(cell, i) in cells" :key="i">
            <div class="border-b border-r border-white/[0.04] min-h-[90px] p-1.5" :class="cell.cur ? '' : 'opacity-30'"
                 @click="cell.cur && addTask(cell.ds)">
                <div class="text-[12px] mb-1 px-1" :class="cell.today ? 'text-teal-400 font-bold' : 'text-white/40'" x-text="cell.d"></div>
                <template x-for="task in tasksFor(cell.ds)" :key="task.id">
                    <div class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] mb-0.5 cursor-pointer hover:bg-white/[0.06]"
                         :class="task.status==='complete' ? 'text-white/25 line-through' : 'text-white/65'" @click.stop="openTask(task)">
                        <div class="w-3 h-3 rounded-full border shrink-0" :class="task.status==='complete'?'bg-teal-500 border-teal-500':'border-white/20'"></div>
                        <span class="truncate" x-text="task.title"></span>
                    </div>
                </template>
            </div>
        </template>
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
function oppCal(data) {
    return {
        tasks: data.tasks, cur: new Date(), sel: null,
        get monthLabel() { return this.cur.toLocaleDateString('en-US',{month:'long',year:'numeric'}); },
        get cells() {
            const y=this.cur.getFullYear(),m=this.cur.getMonth(),fd=new Date(y,m,1).getDay(),dim=new Date(y,m+1,0).getDate(),pd=new Date(y,m,0).getDate(),td=new Date().toISOString().slice(0,10),cs=[];
            for(let i=fd-1;i>=0;i--){const d=pd-i,dt=new Date(y,m-1,d);cs.push({d,ds:dt.toISOString().slice(0,10),cur:false,today:false});}
            for(let d=1;d<=dim;d++){const dt=new Date(y,m,d),ds=dt.toISOString().slice(0,10);cs.push({d,ds,cur:true,today:ds===td});}
            const rem=42-cs.length;for(let d=1;d<=rem;d++){const dt=new Date(y,m+1,d);cs.push({d,ds:dt.toISOString().slice(0,10),cur:false,today:false});}
            return cs;
        },
        tasksFor(ds) { return this.tasks.filter(t=>t.due_date===ds); },
        prevMonth() { this.cur=new Date(this.cur.getFullYear(),this.cur.getMonth()-1,1); },
        nextMonth() { this.cur=new Date(this.cur.getFullYear(),this.cur.getMonth()+1,1); },
        goToday() { this.cur=new Date(); },
        async addTask(ds) {
            const t=prompt('Task name:'); if(!t?.trim()) return;
            const r=await this.api('POST','/api/opp/tasks',{title:t.trim(),project_id:data.projectId,due_date:ds,assignee_id:{{ auth()->id() }}});
            if(r?.task) this.tasks.push(r.task);
        },
        async openTask(task) { this.sel={...task}; const r=await this.api('GET','/api/opp/tasks/'+task.id); if(r?.task) this.sel=r.task; },
        async toggleComp(task) {
            const r=await this.api('POST','/api/opp/tasks/'+task.id+'/complete');
            if(r?.task){Object.assign(task,r.task);const i=this.tasks.findIndex(t=>t.id===task.id);if(i!==-1)Object.assign(this.tasks[i],r.task);if(this.sel?.id===task.id)Object.assign(this.sel,r.task);}
        },
        async upd(f,v) { if(!this.sel) return; const r=await this.api('PUT','/api/opp/tasks/'+this.sel.id,{[f]:v}); if(r?.task){Object.assign(this.sel,r.task);const i=this.tasks.findIndex(t=>t.id===r.task.id);if(i!==-1)Object.assign(this.tasks[i],r.task);} },
        async api(m,u,b=null){try{const o={method:m,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}};if(b)o.body=JSON.stringify(b);const r=await fetch(u,o);if(!r.ok)return null;return await r.json();}catch(e){return null;}},
    };
}
</script>

</x-layouts.opportunity>
