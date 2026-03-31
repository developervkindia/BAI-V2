<x-layouts.opportunity title="Home" currentView="home">

@php
    $hour = (int) date('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', auth()->user()->name ?? 'there')[0];
    $today = now()->format('l, F j');
@endphp

<div class="max-w-4xl mx-auto px-6 py-8" x-data="oppHome()" x-init="loadData()">

    {{-- Date + Greeting --}}
    <div class="mb-6">
        <p class="text-[13px] text-white/35 mb-1">{{ $today }}</p>
        <div class="flex items-center justify-between">
            <h1 class="text-[28px] font-bold text-white/90">{{ $greeting }}, {{ $firstName }}</h1>
            <div class="flex items-center gap-4 text-[12px] text-white/35">
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span x-text="completedCount + ' tasks completed'"></span>
                </span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7"/></svg>
                    <span x-text="collaboratorCount + ' collaborators'"></span>
                </span>
            </div>
        </div>
    </div>

    {{-- My Tasks widget --}}
    <div class="bg-[#111122] border border-white/[0.07] rounded-2xl mb-6">
        <div class="flex items-center gap-3 px-5 pt-4 pb-2">
            <div class="w-9 h-9 rounded-full bg-teal-500/20 text-teal-400 text-[12px] font-bold flex items-center justify-center">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <h2 class="text-[16px] font-semibold text-white/85">My tasks</h2>
        </div>

        {{-- Tabs --}}
        <div class="flex items-center px-5 border-b border-white/[0.06]">
            <template x-for="t in ['upcoming','overdue','completed']" :key="t">
                <button @click="taskTab = t"
                    :class="taskTab === t ? 'border-white/60 text-white/75' : 'border-transparent text-white/35 hover:text-white/55'"
                    class="px-3 py-2.5 text-[13px] font-medium border-b-2 transition-colors capitalize" x-text="t"></button>
            </template>
        </div>

        <div class="divide-y divide-white/[0.04]">
            {{-- Create task --}}
            <div class="flex items-center gap-3 px-5 py-2.5 cursor-pointer" @click="addingTask = true; $nextTick(() => $refs.homeTaskInput?.focus())">
                <template x-if="!addingTask">
                    <div class="flex items-center gap-3 text-white/30 hover:text-white/50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="text-[13px]">Create task</span>
                    </div>
                </template>
                <template x-if="addingTask">
                    <div class="flex items-center gap-3 flex-1" @click.stop>
                        <div class="w-[18px] h-[18px] rounded-full border-2 border-white/15 shrink-0"></div>
                        <input x-ref="homeTaskInput" type="text" x-model="newTaskTitle" placeholder="Write a task name"
                            @keydown.enter="createTask()" @keydown.escape="addingTask = false; newTaskTitle = ''"
                            class="flex-1 bg-transparent text-[13px] text-white/80 placeholder-white/25 focus:outline-none"/>
                    </div>
                </template>
            </div>

            {{-- Task rows --}}
            <template x-for="task in filteredHomeTasks" :key="task.id">
                <div class="flex items-center gap-3 px-5 py-2.5 group hover:bg-white/[0.02] cursor-pointer transition-colors"
                     @click="openTaskDetail(task)">
                    <button @click.stop="toggleComplete(task)"
                        class="w-[18px] h-[18px] rounded-full border-2 shrink-0 flex items-center justify-center transition-all"
                        :class="task.status === 'complete' ? 'bg-teal-500 border-teal-500' : 'border-white/20 hover:border-teal-400'">
                        <svg x-show="task.status === 'complete'" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </button>
                    <span class="flex-1 text-[13px] truncate" :class="task.status === 'complete' ? 'line-through text-white/25' : 'text-white/70'" x-text="task.title"></span>
                    <template x-if="task.project">
                        <span class="hidden sm:flex items-center gap-1.5 px-2 py-0.5 rounded text-[11px] shrink-0"
                            :style="'background:' + (task.project.color||'#14B8A6') + '15; color:' + (task.project.color||'#14B8A6')">
                            <span class="w-1.5 h-1.5 rounded-sm" :style="'background:' + (task.project.color||'#14B8A6')"></span>
                            <span class="truncate max-w-[100px]" x-text="task.project.name"></span>
                        </span>
                    </template>
                    <template x-if="task.due_date">
                        <span class="text-[12px] shrink-0" :class="isOverdue(task) ? 'text-red-400' : (isToday(task) ? 'text-red-400' : 'text-white/35')"
                            x-text="fmtDate(task.due_date)"></span>
                    </template>
                </div>
            </template>

            <div x-show="filteredHomeTasks.length === 0 && !loading" class="px-5 py-8 text-center text-[13px] text-white/25">
                <span x-show="taskTab === 'upcoming'">No upcoming tasks</span>
                <span x-show="taskTab === 'overdue'">No overdue tasks</span>
                <span x-show="taskTab === 'completed'">No completed tasks this week</span>
            </div>
        </div>
    </div>

    {{-- Projects + People --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Projects --}}
        <div class="bg-[#111122] border border-white/[0.07] rounded-2xl">
            <div class="flex items-center justify-between px-5 pt-4 pb-2">
                <h2 class="text-[15px] font-semibold text-white/80">Projects</h2>
            </div>
            <div class="px-5 pb-4 space-y-0.5">
                <a href="{{ route('opportunity.projects.index') }}" class="flex items-center gap-3 py-2.5 text-white/30 hover:text-white/50 transition-colors">
                    <div class="w-11 h-11 rounded-lg border-2 border-dashed border-white/15 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <span class="text-[13px]">Create project</span>
                </a>
                <template x-for="proj in projects" :key="proj.id">
                    <a :href="'/opportunity/projects/' + proj.slug" class="flex items-center gap-3 py-2.5 hover:bg-white/[0.03] -mx-2 px-2 rounded-lg transition-colors">
                        <div class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0" :style="'background:' + (proj.color||'#14B8A6') + '18'">
                            <svg class="w-5 h-5" :style="'color:' + (proj.color||'#14B8A6')" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        </div>
                        <div>
                            <div class="text-[13px] text-white/70 font-medium" x-text="proj.name"></div>
                            <div class="text-[11px] text-white/30" x-text="(proj.tasks_count||0) + ' tasks'"></div>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        {{-- People --}}
        <div class="bg-[#111122] border border-white/[0.07] rounded-2xl">
            <div class="px-5 pt-4 pb-2">
                <h2 class="text-[15px] font-semibold text-white/80">People</h2>
            </div>
            <div class="px-5 pb-4">
                <p class="text-[13px] text-white/35 mb-3">See who's on track and who needs support at a glance.</p>
                <template x-for="m in members" :key="m.id">
                    <div class="flex items-center gap-3 py-2">
                        <div class="w-7 h-7 rounded-full text-[9px] font-bold flex items-center justify-center shrink-0"
                            :style="'background:'+strColor(m.name)+'33;color:'+strColor(m.name)" x-text="m.name.slice(0,2).toUpperCase()"></div>
                        <span class="flex-1 text-[13px] text-white/60 truncate" x-text="m.name"></span>
                        <span class="text-[11px] text-red-400/80 tabular-nums" x-text="(m.overdue||0) + ' overdue'"></span>
                        <span class="text-[11px] text-green-400/80 tabular-nums" x-text="(m.completed||0) + ' completed'"></span>
                        <span class="text-[11px] text-white/30 tabular-nums" x-text="(m.upcoming||0) + ' upcoming'"></span>
                    </div>
                </template>
                <p x-show="members.length === 0" class="text-[12px] text-white/20 text-center py-4">No team members</p>
            </div>
        </div>
    </div>

    {{-- Task Detail Slide-over (same as My Tasks) --}}
    <div x-show="detailTask" x-cloak
         class="fixed inset-y-0 right-0 w-[500px] bg-[#1A1A2E] border-l border-white/[0.06] z-50 overflow-y-auto shadow-2xl"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
        <template x-if="detailTask">
            <div>
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-[#1A1A2E] border-b border-white/[0.06]">
                    <button @click="toggleComplete(detailTask)"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium border transition-colors"
                        :class="detailTask.status === 'complete' ? 'border-teal-500/30 bg-teal-500/10 text-teal-400' : 'border-white/[0.1] text-white/50 hover:bg-white/[0.04]'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="detailTask.status === 'complete' ? 'Completed' : 'Mark complete'"></span>
                    </button>
                    <div class="flex-1"></div>
                    <button @click="detailTask = null" class="p-1.5 text-white/25 hover:text-white/50"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
                <div class="px-5 pt-4 pb-2">
                    <input type="text" x-model="detailTask.title" @blur="updateTask(detailTask, 'title', detailTask.title)"
                        class="w-full bg-transparent text-[18px] font-semibold text-white/90 focus:outline-none border-none"/>
                </div>
                <div class="px-5 space-y-3 pb-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-4">
                        <span class="text-[12px] text-white/35 w-20">Assignee</span>
                        <span class="text-[13px] text-white/65" x-text="detailTask.assignee?.name || 'No assignee'"></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-[12px] text-white/35 w-20">Due date</span>
                        <input type="date" :value="detailTask.due_date" @change="updateTask(detailTask, 'due_date', $event.target.value || null)"
                            class="px-2 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[12px] text-white/60 focus:outline-none focus:ring-1 focus:ring-teal-500/40"/>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-[12px] text-white/35 w-20">Projects</span>
                        <template x-if="detailTask.project">
                            <a :href="'/opportunity/projects/' + detailTask.project.slug" class="text-[12px] px-2 py-0.5 rounded"
                                :style="'background:'+(detailTask.project.color||'#14B8A6')+'15;color:'+(detailTask.project.color||'#14B8A6')" x-text="detailTask.project.name"></a>
                        </template>
                    </div>
                </div>
                <div class="px-5 py-4 border-b border-white/[0.06]">
                    <span class="text-[12px] text-white/35 block mb-2">Description</span>
                    <textarea x-model="detailTask.description" @blur="updateTask(detailTask, 'description', detailTask.description)"
                        rows="3" placeholder="Add description..." class="w-full bg-white/[0.03] border border-white/[0.06] rounded-lg px-3 py-2 text-[13px] text-white/65 placeholder-white/20 focus:outline-none resize-none"></textarea>
                </div>
                {{-- Subtasks --}}
                <div class="px-5 py-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[13px] font-medium text-white/60">Subtasks</span>
                        <button @click="showSubInput = true; $nextTick(() => $refs.subInput?.focus())" class="text-white/20 hover:text-teal-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    <template x-for="sub in (detailTask.subtasks || [])" :key="sub.id">
                        <div class="flex items-center gap-2.5 py-1.5">
                            <button @click="toggleSubComplete(sub)" class="w-4 h-4 rounded-full border-2 shrink-0 flex items-center justify-center"
                                :class="sub.status==='complete'?'bg-teal-500 border-teal-500':'border-white/20 hover:border-teal-400'">
                                <svg x-show="sub.status==='complete'" class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <span class="text-[13px]" :class="sub.status==='complete'?'line-through text-white/25':'text-white/65'" x-text="sub.title"></span>
                        </div>
                    </template>
                    <div x-show="showSubInput" x-cloak class="flex items-center gap-2.5 py-1.5">
                        <div class="w-4 h-4 rounded-full border-2 border-white/15 shrink-0"></div>
                        <input x-ref="subInput" type="text" x-model="newSubTitle" placeholder="Add subtask..."
                            @keydown.enter="createSubtask()" @keydown.escape="showSubInput=false;newSubTitle=''"
                            class="flex-1 bg-transparent text-[13px] text-white/65 placeholder-white/20 focus:outline-none"/>
                    </div>
                </div>
                {{-- Comments --}}
                <div class="px-5 py-4">
                    <h3 class="text-[13px] font-medium text-white/60 mb-3">Comments</h3>
                    <template x-for="c in (detailTask.comments || [])" :key="c.id">
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
                            <textarea x-model="newComment" rows="2" placeholder="Add a comment..."
                                class="w-full bg-white/[0.03] border border-white/[0.06] rounded-lg px-3 py-2 text-[13px] text-white/65 placeholder-white/20 focus:outline-none resize-none"></textarea>
                            <div x-show="newComment.trim()" class="flex justify-end mt-1">
                                <button @click="postComment()" class="px-3 py-1 rounded-md bg-teal-500 text-white text-[12px] font-semibold hover:bg-teal-400">Comment</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
    <div x-show="detailTask" x-cloak @click="detailTask = null" class="fixed inset-0 bg-black/30 z-40"></div>
</div>

<script>
function oppHome() {
    return {
        tasks: [], projects: [], members: [], loading: true,
        taskTab: 'upcoming', addingTask: false, newTaskTitle: '',
        detailTask: null, showSubInput: false, newSubTitle: '', newComment: '',
        completedCount: 0, collaboratorCount: 0,

        get filteredHomeTasks() {
            const now = new Date(); now.setHours(0,0,0,0);
            if (this.taskTab === 'upcoming') return this.tasks.filter(t => t.status !== 'complete');
            if (this.taskTab === 'overdue') return this.tasks.filter(t => t.status !== 'complete' && t.due_date && new Date(t.due_date+'T23:59:59') < now);
            if (this.taskTab === 'completed') return this.tasks.filter(t => t.status === 'complete');
            return this.tasks;
        },

        async loadData() {
            this.loading = true;
            const [taskRes, projRes] = await Promise.all([
                this.api('GET', '/api/opp/my-tasks'),
                this.api('GET', '/api/opp/reports/project-progress'),
            ]);
            if (taskRes?.tasks) {
                this.tasks = taskRes.tasks;
                this.completedCount = this.tasks.filter(t => t.status === 'complete').length;
            }
            if (projRes?.projects) this.projects = projRes.projects;

            // Load members
            const wRes = await this.api('GET', '/api/opp/reports/team-workload');
            if (wRes?.workload) {
                this.members = wRes.workload.map(w => ({ id: w.name, name: w.name, overdue: 0, completed: w.complete || 0, upcoming: w.incomplete || 0 }));
                this.collaboratorCount = this.members.length;
            }
            this.loading = false;
        },

        async createTask() {
            if (!this.newTaskTitle.trim()) return;
            const pid = this.tasks[0]?.project_id || this.projects[0]?.id;
            if (!pid) { alert('Create a project first'); return; }
            const r = await this.api('POST', '/api/opp/tasks', { title: this.newTaskTitle.trim(), project_id: pid, assignee_id: {{ auth()->id() }} });
            if (r?.task) { this.tasks.unshift(r.task); this.newTaskTitle = ''; this.addingTask = false; }
        },

        async toggleComplete(task) {
            const r = await this.api('POST', '/api/opp/tasks/' + task.id + '/complete');
            if (r?.task) {
                Object.assign(task, r.task);
                const i = this.tasks.findIndex(t => t.id === task.id);
                if (i !== -1) Object.assign(this.tasks[i], r.task);
                if (this.detailTask?.id === task.id) Object.assign(this.detailTask, r.task);
                this.completedCount = this.tasks.filter(t => t.status === 'complete').length;
            }
        },

        async openTaskDetail(task) {
            this.detailTask = { ...task }; this.showSubInput = false; this.newSubTitle = ''; this.newComment = '';
            const r = await this.api('GET', '/api/opp/tasks/' + task.id);
            if (r?.task) this.detailTask = r.task;
        },

        async updateTask(task, field, value) {
            const r = await this.api('PUT', '/api/opp/tasks/' + task.id, { [field]: value });
            if (r?.task) {
                Object.assign(task, r.task);
                const i = this.tasks.findIndex(t => t.id === task.id);
                if (i !== -1) Object.assign(this.tasks[i], r.task);
            }
        },

        async toggleSubComplete(sub) {
            const r = await this.api('POST', '/api/opp/tasks/' + sub.id + '/complete');
            if (r?.task && this.detailTask?.subtasks) {
                const idx = this.detailTask.subtasks.findIndex(s => s.id === sub.id);
                if (idx !== -1) { this.detailTask.subtasks[idx] = { ...this.detailTask.subtasks[idx], ...r.task }; this.detailTask.subtasks = [...this.detailTask.subtasks]; }
            }
        },

        async createSubtask() {
            if (!this.newSubTitle.trim() || !this.detailTask) return;
            const r = await this.api('POST', '/api/opp/tasks', { title: this.newSubTitle.trim(), project_id: this.detailTask.project_id, parent_task_id: this.detailTask.id });
            if (r?.task) { if (!this.detailTask.subtasks) this.detailTask.subtasks = []; this.detailTask.subtasks.push(r.task); this.newSubTitle = ''; }
        },

        async postComment() {
            if (!this.newComment.trim() || !this.detailTask) return;
            const r = await this.api('POST', '/api/opp/comments', { body: this.newComment.trim(), task_id: this.detailTask.id });
            if (r?.comment) { if (!this.detailTask.comments) this.detailTask.comments = []; this.detailTask.comments.push(r.comment); this.newComment = ''; }
        },

        isOverdue(t) { return t.due_date && new Date(t.due_date+'T23:59:59') < new Date() && t.status !== 'complete'; },
        isToday(t) { return t.due_date === new Date().toISOString().slice(0,10); },
        fmtDate(d) { if(!d)return''; const dt=new Date(d+'T00:00:00'),now=new Date();now.setHours(0,0,0,0); const diff=Math.round((dt-now)/86400000); if(diff===0)return'Today';if(diff===1)return'Tomorrow';if(diff===-1)return'Yesterday'; return dt.toLocaleDateString('en-US',{month:'short',day:'numeric'}); },
        timeAgo(s) { if(!s)return'';const d=(Date.now()-new Date(s).getTime())/1000;if(d<60)return'just now';if(d<3600)return Math.floor(d/60)+'m ago';if(d<86400)return Math.floor(d/3600)+'h ago';return Math.floor(d/86400)+'d ago'; },
        strColor(s) { if(!s)return'#6B7280';let h=0;for(let i=0;i<s.length;i++)h=s.charCodeAt(i)+((h<<5)-h);return['#F43F5E','#EC4899','#A855F7','#6366F1','#3B82F6','#14B8A6','#10B981','#F59E0B','#EF4444','#8B5CF6'][Math.abs(h)%10]; },
        async api(m,u,b=null){try{const o={method:m,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}};if(b)o.body=JSON.stringify(b);const r=await fetch(u,o);if(!r.ok)return null;return await r.json();}catch(e){console.error(e);return null;}},
    };
}
</script>

</x-layouts.opportunity>
