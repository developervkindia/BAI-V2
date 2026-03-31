<x-layouts.opportunity title="Inbox" currentView="inbox">

<div class="max-w-3xl mx-auto px-6 py-6" x-data="oppInbox()" x-init="loadInbox()">

    <div class="flex items-center justify-between mb-5">
        <h1 class="text-[20px] font-bold text-white/90">Inbox</h1>
        <div class="flex items-center gap-2">
            <button class="px-3 py-1.5 rounded-lg text-[12px] text-white/35 hover:text-white/55 hover:bg-white/[0.04] border border-white/[0.08]">Manage notifications</button>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center gap-0 mb-4 border-b border-white/[0.06]">
        <button @click="tab = 'activity'" :class="tab==='activity'?'border-teal-500 text-white/75':'border-transparent text-white/35'"
            class="px-3 py-2.5 text-[13px] font-medium border-b-2 transition-colors">Activity</button>
        <button @click="tab = 'mentions'" :class="tab==='mentions'?'border-teal-500 text-white/75':'border-transparent text-white/35'"
            class="px-3 py-2.5 text-[13px] font-medium border-b-2 transition-colors">@Mentioned</button>
    </div>

    {{-- Filter bar --}}
    <div class="flex items-center gap-3 mb-4">
        <button class="px-2.5 py-1.5 rounded-md text-[12px] text-white/35 hover:text-white/55 hover:bg-white/[0.04]">Filter</button>
        <button class="px-2.5 py-1.5 rounded-md text-[12px] text-white/35 hover:text-white/55 hover:bg-white/[0.04]">Density: Detailed</button>
    </div>

    {{-- Activity groups --}}
    <template x-for="group in groupedActivities" :key="group.label">
        <div class="mb-6">
            <h3 class="text-[13px] font-semibold text-white/50 mb-3" x-text="group.label"></h3>
            <div class="space-y-1">
                <template x-for="item in group.items" :key="item.id">
                    <div class="bg-[#111122] border border-white/[0.07] rounded-xl p-4 hover:border-teal-500/20 cursor-pointer transition-all"
                         @click="navigateToTask(item)">
                        {{-- Project badge + task name --}}
                        <div class="flex items-center gap-2 mb-2">
                            <template x-if="item.task?.project">
                                <span class="flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px]"
                                    :style="'background:'+(item.task.project.color||'#14B8A6')+'15;color:'+(item.task.project.color||'#14B8A6')">
                                    <span class="w-1.5 h-1.5 rounded-sm" :style="'background:'+(item.task.project.color||'#14B8A6')"></span>
                                    <span x-text="item.task.project.name"></span>
                                </span>
                            </template>
                            <template x-if="item.task?.due_date">
                                <span class="text-[11px] text-white/25 ml-auto" x-text="'Due ' + fmtDate(item.task.due_date)"></span>
                            </template>
                        </div>

                        {{-- Task title --}}
                        <template x-if="item.task">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-[16px] h-[16px] rounded-full border-2 shrink-0 flex items-center justify-center"
                                    :class="item.task.status==='complete'?'bg-teal-500 border-teal-500':'border-white/20'">
                                    <svg x-show="item.task.status==='complete'" class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="text-[13px] text-white/70" :class="item.task.status==='complete'?'line-through text-white/30':''" x-text="item.task.title"></span>
                            </div>
                        </template>

                        {{-- User action --}}
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full text-[9px] font-bold flex items-center justify-center shrink-0"
                                :style="'background:'+strColor(item.user?.name||'?')+'33;color:'+strColor(item.user?.name||'?')"
                                x-text="(item.user?.name||'?').slice(0,2).toUpperCase()"></div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[13px] font-medium text-white/65" x-text="item.user?.name||'Someone'"></span>
                                <span class="text-[13px] text-white/35" x-text="' ' + formatAction(item)"></span>
                                <span class="text-[11px] text-white/20 ml-1" x-text="'· ' + timeAgo(item.created_at)"></span>
                            </div>
                        </div>

                        {{-- Comment preview (if comment action) --}}
                        <template x-if="item.action === 'comment.added' && item.new_value">
                            <div class="mt-2 ml-9 pl-3 border-l-2 border-white/[0.08]">
                                <p class="text-[12px] text-white/40 line-clamp-2" x-text="item.new_value"></p>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <div x-show="activities.length === 0 && !loading" class="text-center py-16">
        <div class="w-16 h-16 rounded-full bg-teal-500/10 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-teal-400/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
        </div>
        <h3 class="text-[16px] font-semibold text-white/50 mb-1">You're all caught up!</h3>
        <p class="text-[13px] text-white/25">Activity on tasks you follow will appear here</p>
    </div>

    <div x-show="loading" class="text-center py-16"><div class="w-6 h-6 border-2 border-teal-500/30 border-t-teal-500 rounded-full animate-spin mx-auto"></div></div>
</div>

<script>
function oppInbox() {
    return {
        activities: [], loading: true, tab: 'activity',

        get groupedActivities() {
            const now = new Date(); now.setHours(0,0,0,0);
            const yesterday = new Date(now); yesterday.setDate(yesterday.getDate() - 1);
            const weekAgo = new Date(now); weekAgo.setDate(weekAgo.getDate() - 7);

            const groups = { today: [], yesterday: [], thisWeek: [], older: [] };
            this.activities.forEach(a => {
                const d = new Date(a.created_at); d.setHours(0,0,0,0);
                if (d >= now) groups.today.push(a);
                else if (d >= yesterday) groups.yesterday.push(a);
                else if (d >= weekAgo) groups.thisWeek.push(a);
                else groups.older.push(a);
            });

            const result = [];
            if (groups.today.length) result.push({ label: 'Today', items: groups.today });
            if (groups.yesterday.length) result.push({ label: 'Yesterday', items: groups.yesterday });
            if (groups.thisWeek.length) result.push({ label: 'Past 7 Days', items: groups.thisWeek });
            if (groups.older.length) result.push({ label: 'Earlier', items: groups.older });
            return result;
        },

        async loadInbox() {
            this.loading = true;
            // Load activity for tasks assigned to or followed by user
            const r = await this.api('GET', '/api/opp/my-tasks');
            if (r?.tasks) {
                const taskIds = r.tasks.map(t => t.id);
                // Load recent activity for these tasks
                const acts = [];
                for (const task of r.tasks.slice(0, 20)) {
                    const detail = await this.api('GET', '/api/opp/tasks/' + task.id);
                    if (detail?.task?.activity) {
                        detail.task.activity.forEach(a => {
                            a.task = { id: task.id, title: task.title, status: task.status, project: task.project, due_date: task.due_date };
                            acts.push(a);
                        });
                    }
                }
                this.activities = acts.sort((a, b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 50);
            }
            this.loading = false;
        },

        navigateToTask(item) {
            if (item.task?.project?.slug) {
                window.location.href = '/opportunity/projects/' + item.task.project.slug;
            }
        },

        formatAction(a) {
            const m = { 'task.created':'created this task', 'task.completed':'completed this task', 'task.reopened':'marked incomplete',
                'comment.added':'commented', 'task.moved':'moved this task' };
            if (m[a.action]) return m[a.action];
            if (a.field_name === 'due_date') return 'changed the due date';
            if (a.field_name === 'assignee_id') return 'assigned this task';
            if (a.field_name) return 'changed ' + a.field_name.replace('_', ' ');
            return a.action;
        },

        fmtDate(d) { if(!d)return''; const dt=new Date(d+'T00:00:00'),now=new Date();now.setHours(0,0,0,0); const diff=Math.round((dt-now)/86400000); if(diff===0)return'Today';if(diff===1)return'Tomorrow'; return dt.toLocaleDateString('en-US',{month:'short',day:'numeric'}); },
        timeAgo(s) { if(!s)return'';const d=(Date.now()-new Date(s).getTime())/1000;if(d<60)return'just now';if(d<3600)return Math.floor(d/60)+'m ago';if(d<86400)return Math.floor(d/3600)+'h ago';return Math.floor(d/86400)+'d ago'; },
        strColor(s) { if(!s)return'#6B7280';let h=0;for(let i=0;i<s.length;i++)h=s.charCodeAt(i)+((h<<5)-h);return['#F43F5E','#EC4899','#A855F7','#6366F1','#3B82F6','#14B8A6','#10B981','#F59E0B','#EF4444','#8B5CF6'][Math.abs(h)%10]; },
        async api(m,u,b=null){try{const o={method:m,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}};if(b)o.body=JSON.stringify(b);const r=await fetch(u,o);if(!r.ok)return null;return await r.json();}catch(e){return null;}},
    };
}
</script>

</x-layouts.opportunity>
