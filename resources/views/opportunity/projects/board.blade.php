<x-layouts.opportunity title="Board" :project="$project" currentView="board">

@php
$boardData = [
    'projectId' => $project->id,
    'sections' => $project->sections->map(fn($s) => [
        'id' => $s->id, 'name' => $s->name,
        'tasks' => $s->tasks->map(fn($t) => [
            'id' => $t->id, 'title' => $t->title, 'status' => $t->status,
            'assignee' => $t->assignee ? ['id' => $t->assignee->id, 'name' => $t->assignee->name] : null,
            'due_date' => $t->due_date?->format('Y-m-d'),
            'subtasks_count' => $t->subtasks_count ?? 0,
            'comments_count' => $t->comments_count ?? 0,
        ])->values(),
    ])->values(),
];
@endphp

<div class="flex-1 flex flex-col overflow-hidden" x-data="oppBoard({{ Js::from($boardData) }})">

    {{-- Toolbar --}}
    <div class="shrink-0 flex items-center gap-2 px-5 py-2 border-b border-white/[0.06]">
        <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-500 text-white text-[12px] font-semibold hover:bg-teal-400"
            @click="addingToSection = sections[0]?.id">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Add task
        </button>
        <div class="flex-1"></div>
        <button class="px-2.5 py-1.5 rounded-md text-[12px] text-white/35 hover:text-white/55 hover:bg-white/[0.04]">Filter</button>
        <button class="px-2.5 py-1.5 rounded-md text-[12px] text-white/35 hover:text-white/55 hover:bg-white/[0.04]">Sort</button>
    </div>

    {{-- Board columns --}}
    <div class="flex-1 overflow-x-auto">
        <div class="flex gap-4 p-5 h-full" style="min-width: max-content;">
            <template x-for="section in sections" :key="section.id">
                <div class="w-72 flex flex-col shrink-0">
                    {{-- Column header --}}
                    <div class="flex items-center gap-2 mb-3 px-1">
                        <span class="text-[13px] font-semibold text-white/60" x-text="section.name"></span>
                        <span class="text-[11px] text-white/25 bg-white/[0.05] px-1.5 py-0.5 rounded-full" x-text="section.tasks.length"></span>
                        <div class="flex-1"></div>
                        <button @click="addingToSection = section.id" class="text-white/20 hover:text-white/50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>

                    {{-- Drop zone --}}
                    <div class="flex-1 rounded-xl p-2 space-y-2 bg-white/[0.02] transition-all"
                         :class="dragOverSection === section.id ? 'ring-1 ring-teal-500/25 bg-white/[0.04]' : ''"
                         @dragover.prevent="dragOverSection = section.id"
                         @dragleave.self="dragOverSection = null"
                         @drop.prevent="dropTask(section)">

                        <template x-for="task in section.tasks" :key="task.id">
                            <div class="bg-[#181830] border border-white/[0.07] rounded-xl p-3 cursor-pointer hover:border-teal-500/20 transition-all group"
                                 draggable="true"
                                 @dragstart="dragTaskId = task.id; dragFromSection = section.id"
                                 @dragend="dragTaskId = null; dragFromSection = null; dragOverSection = null"
                                 @click="openDetail(task)">

                                {{-- Task title + complete --}}
                                <div class="flex items-start gap-2 mb-2">
                                    <button @click.stop="toggleComplete(task, section)"
                                        class="w-[16px] h-[16px] rounded-full border-2 shrink-0 flex items-center justify-center mt-0.5 transition-all"
                                        :class="task.status === 'complete' ? 'bg-teal-500 border-teal-500' : 'border-white/20 hover:border-teal-400'">
                                        <svg x-show="task.status === 'complete'" class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <span class="text-[13px] leading-tight" :class="task.status === 'complete' ? 'line-through text-white/25' : 'text-white/75'" x-text="task.title"></span>
                                </div>

                                {{-- Meta row --}}
                                <div class="flex items-center gap-2">
                                    <template x-if="task.assignee">
                                        <div class="w-5 h-5 rounded-full text-[7px] font-bold flex items-center justify-center"
                                            :style="'background:'+strColor(task.assignee.name)+'33;color:'+strColor(task.assignee.name)"
                                            :title="task.assignee.name" x-text="task.assignee.name.slice(0,2).toUpperCase()"></div>
                                    </template>
                                    <template x-if="task.due_date">
                                        <span class="text-[10px]" :class="isOverdue(task)?'text-red-400':'text-white/30'" x-text="fmtDate(task.due_date)"></span>
                                    </template>
                                    <div class="flex-1"></div>
                                    <span x-show="task.subtasks_count > 0" class="text-[10px] text-white/20" x-text="task.subtasks_count + ' ↳'"></span>
                                    <span x-show="task.comments_count > 0" class="flex items-center gap-0.5 text-[10px] text-white/20">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <span x-text="task.comments_count"></span>
                                    </span>
                                </div>
                            </div>
                        </template>

                        {{-- Add task inline --}}
                        <div x-show="addingToSection === section.id" x-cloak class="bg-[#181830] border border-teal-500/20 rounded-xl p-3">
                            <input type="text" x-model="newTaskTitle" placeholder="Task name" autofocus
                                @keydown.enter="createTask(section)" @keydown.escape="addingToSection = null; newTaskTitle = ''"
                                x-effect="if(addingToSection === section.id) $nextTick(() => $el.focus())"
                                class="w-full bg-transparent text-[13px] text-white/80 placeholder-white/25 focus:outline-none mb-2"/>
                            <div class="flex gap-2">
                                <button @click="createTask(section)" class="px-3 py-1 rounded-md bg-teal-500 text-white text-[11px] font-medium">Add</button>
                                <button @click="addingToSection = null; newTaskTitle = ''" class="px-3 py-1 rounded-md text-[11px] text-white/35 hover:text-white/55">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Add section column --}}
            <div class="w-72 shrink-0">
                <template x-if="!addingSectionName">
                    <button @click="addingSectionName = ''; $nextTick(() => $refs.newSecInput?.focus())"
                        class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-[13px] text-white/25 hover:text-white/50 hover:bg-white/[0.03] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add section
                    </button>
                </template>
                <template x-if="addingSectionName !== null && addingSectionName !== false">
                    <div>
                        <input x-ref="newSecInput" type="text" x-model="addingSectionName" placeholder="Section name"
                            @keydown.enter="createSection()" @keydown.escape="addingSectionName = null"
                            class="w-full bg-transparent text-[14px] font-semibold text-white/70 placeholder-white/25 focus:outline-none border-b border-teal-500/50 py-1 mb-2"/>
                        <button @click="createSection()" class="px-3 py-1 rounded-md bg-teal-500 text-white text-[11px] font-medium">Add</button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Task detail slide-over --}}
    <div x-show="detailTask" x-cloak
         class="fixed inset-y-0 right-0 w-[480px] bg-[#1A1A2E] border-l border-white/[0.06] z-50 overflow-y-auto shadow-2xl"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
        <template x-if="detailTask">
            <div>
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-[#1A1A2E] border-b border-white/[0.06]">
                    <button @click="toggleCompleteDetail()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium border transition-colors"
                        :class="detailTask.status==='complete'?'border-teal-500/30 bg-teal-500/10 text-teal-400':'border-white/[0.1] text-white/50 hover:bg-white/[0.04]'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="detailTask.status==='complete'?'Completed':'Mark complete'"></span>
                    </button>
                    <div class="flex-1"></div>
                    <button @click="detailTask = null" class="p-1.5 text-white/25 hover:text-white/50"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="px-5 pt-4 pb-2">
                    <input type="text" x-model="detailTask.title" @blur="updateDetail('title', detailTask.title)"
                        class="w-full bg-transparent text-[18px] font-semibold text-white/90 focus:outline-none"/>
                </div>
                <div class="px-5 space-y-3 pb-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-4"><span class="text-[12px] text-white/35 w-20">Assignee</span><span class="text-[13px] text-white/65" x-text="detailTask.assignee?.name||'No assignee'"></span></div>
                    <div class="flex items-center gap-4"><span class="text-[12px] text-white/35 w-20">Due date</span>
                        <input type="date" :value="detailTask.due_date" @change="updateDetail('due_date',$event.target.value||null)"
                            class="px-2 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[12px] text-white/60 focus:outline-none focus:ring-1 focus:ring-teal-500/40"/></div>
                </div>
                <div class="px-5 py-4 border-b border-white/[0.06]">
                    <span class="text-[12px] text-white/35 block mb-2">Description</span>
                    <textarea x-model="detailTask.description" @blur="updateDetail('description',detailTask.description)" rows="3" placeholder="Add description..."
                        class="w-full bg-white/[0.03] border border-white/[0.06] rounded-lg px-3 py-2 text-[13px] text-white/65 placeholder-white/20 focus:outline-none resize-none"></textarea>
                </div>
                <div class="px-5 py-4">
                    <h3 class="text-[13px] font-medium text-white/60 mb-3">Comments</h3>
                    <template x-for="c in (detailTask.comments||[])" :key="c.id">
                        <div class="flex gap-2.5 mb-3">
                            <div class="w-7 h-7 rounded-full text-[9px] font-bold flex items-center justify-center shrink-0"
                                :style="'background:'+strColor(c.user.name)+'33;color:'+strColor(c.user.name)" x-text="c.user.name.slice(0,2).toUpperCase()"></div>
                            <div><span class="text-[13px] font-semibold text-white/70" x-text="c.user.name"></span>
                            <span class="text-[11px] text-white/25 ml-2" x-text="timeAgo(c.created_at)"></span>
                            <p class="text-[13px] text-white/55 mt-0.5 whitespace-pre-wrap" x-text="c.body"></p></div>
                        </div>
                    </template>
                    <div class="flex gap-2.5 mt-3">
                        <div class="w-7 h-7 rounded-full bg-teal-500/20 text-teal-400 text-[9px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                        <div class="flex-1">
                            <textarea x-model="newComment" rows="2" placeholder="Add a comment..." class="w-full bg-white/[0.03] border border-white/[0.06] rounded-lg px-3 py-2 text-[13px] text-white/65 placeholder-white/20 focus:outline-none resize-none"></textarea>
                            <div x-show="newComment.trim()" class="flex justify-end mt-1"><button @click="postComment()" class="px-3 py-1 rounded-md bg-teal-500 text-white text-[12px] font-semibold hover:bg-teal-400">Comment</button></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <div x-show="detailTask" x-cloak @click="detailTask = null" class="fixed inset-0 bg-black/20 z-40"></div>
</div>

<script>
function oppBoard(data) {
    return {
        sections: data.sections,
        dragTaskId: null, dragFromSection: null, dragOverSection: null,
        addingToSection: null, newTaskTitle: '',
        addingSectionName: null,
        detailTask: null, newComment: '',

        async createTask(section) {
            if (!this.newTaskTitle.trim()) return;
            const r = await this.api('POST', '/api/opp/tasks', { title: this.newTaskTitle.trim(), project_id: data.projectId, section_id: section.id, assignee_id: {{ auth()->id() }} });
            if (r?.task) { r.task.subtasks_count=0; r.task.comments_count=0; section.tasks.push(r.task); this.newTaskTitle = ''; this.addingToSection = null; }
        },

        async toggleComplete(task, section) {
            const r = await this.api('POST', '/api/opp/tasks/' + task.id + '/complete');
            if (r?.task) Object.assign(task, r.task);
        },

        async dropTask(toSection) {
            this.dragOverSection = null;
            if (!this.dragTaskId || !this.dragFromSection) return;
            if (this.dragFromSection === toSection.id) return;

            const fromSec = this.sections.find(s => s.id === this.dragFromSection);
            const taskIdx = fromSec?.tasks.findIndex(t => t.id === this.dragTaskId);
            if (taskIdx === undefined || taskIdx === -1) return;

            const task = fromSec.tasks.splice(taskIdx, 1)[0];
            toSection.tasks.push(task);

            await this.api('PUT', '/api/opp/tasks/' + task.id + '/move', { section_id: toSection.id, position: toSection.tasks.length * 1000 });
            this.dragTaskId = null; this.dragFromSection = null;
        },

        async createSection() {
            if (!this.addingSectionName?.trim()) { this.addingSectionName = null; return; }
            const r = await this.api('POST', '/api/opp/sections', { name: this.addingSectionName.trim(), project_id: data.projectId });
            if (r?.section) this.sections.push({ ...r.section, tasks: [] });
            this.addingSectionName = null;
        },

        async openDetail(task) {
            this.detailTask = { ...task }; this.newComment = '';
            const r = await this.api('GET', '/api/opp/tasks/' + task.id);
            if (r?.task) this.detailTask = r.task;
        },

        async toggleCompleteDetail() {
            if (!this.detailTask) return;
            const r = await this.api('POST', '/api/opp/tasks/' + this.detailTask.id + '/complete');
            if (r?.task) { this.detailTask = { ...this.detailTask, ...r.task }; for(const s of this.sections){const i=s.tasks.findIndex(t=>t.id===r.task.id);if(i!==-1){Object.assign(s.tasks[i],r.task);break;}} }
        },

        async updateDetail(field, value) {
            if (!this.detailTask) return;
            const r = await this.api('PUT', '/api/opp/tasks/' + this.detailTask.id, { [field]: value });
            if (r?.task) { Object.assign(this.detailTask, r.task); for(const s of this.sections){const i=s.tasks.findIndex(t=>t.id===r.task.id);if(i!==-1){Object.assign(s.tasks[i],r.task);break;}} }
        },

        async postComment() {
            if (!this.newComment.trim() || !this.detailTask) return;
            const r = await this.api('POST', '/api/opp/comments', { body: this.newComment.trim(), task_id: this.detailTask.id });
            if (r?.comment) { if(!this.detailTask.comments)this.detailTask.comments=[]; this.detailTask.comments.push(r.comment); this.newComment = ''; }
        },

        isOverdue(t) { return t.due_date && new Date(t.due_date+'T23:59:59') < new Date() && t.status !== 'complete'; },
        fmtDate(d) { if(!d)return''; const dt=new Date(d+'T00:00:00'),now=new Date();now.setHours(0,0,0,0); const diff=Math.round((dt-now)/86400000); if(diff===0)return'Today';if(diff===1)return'Tomorrow'; return dt.toLocaleDateString('en-US',{month:'short',day:'numeric'}); },
        timeAgo(s) { if(!s)return'';const d=(Date.now()-new Date(s).getTime())/1000;if(d<60)return'just now';if(d<3600)return Math.floor(d/60)+'m ago';if(d<86400)return Math.floor(d/3600)+'h ago';return Math.floor(d/86400)+'d ago'; },
        strColor(s) { if(!s)return'#6B7280';let h=0;for(let i=0;i<s.length;i++)h=s.charCodeAt(i)+((h<<5)-h);return['#F43F5E','#EC4899','#A855F7','#6366F1','#3B82F6','#14B8A6','#10B981','#F59E0B','#EF4444','#8B5CF6'][Math.abs(h)%10]; },
        async api(m,u,b=null){try{const o={method:m,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}};if(b)o.body=JSON.stringify(b);const r=await fetch(u,o);if(!r.ok)return null;return await r.json();}catch(e){return null;}},
    };
}
</script>

</x-layouts.opportunity>
