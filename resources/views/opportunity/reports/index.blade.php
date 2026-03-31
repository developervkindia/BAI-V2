<x-layouts.opportunity title="Reporting" currentView="reporting">

<div class="px-6 py-6 max-w-5xl mx-auto" x-data="oppReports()" x-init="loadAll()">

    <h1 class="text-[20px] font-bold text-white/90 mb-6">Reporting</h1>

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-[#111122] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] text-white/35 mb-1">Total Tasks</div>
            <div class="text-[24px] font-bold text-white/80" x-text="stats.totalTasks || 0"></div>
        </div>
        <div class="bg-[#111122] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] text-white/35 mb-1">Completed</div>
            <div class="text-[24px] font-bold text-teal-400" x-text="stats.completedTasks || 0"></div>
        </div>
        <div class="bg-[#111122] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] text-white/35 mb-1">Overdue</div>
            <div class="text-[24px] font-bold text-red-400" x-text="stats.overdueTasks || 0"></div>
        </div>
        <div class="bg-[#111122] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] text-white/35 mb-1">Projects</div>
            <div class="text-[24px] font-bold text-white/80" x-text="stats.totalProjects || 0"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Team Workload --}}
        <div class="bg-[#111122] border border-white/[0.07] rounded-2xl p-5">
            <h3 class="text-[14px] font-semibold text-white/70 mb-4">Team Workload</h3>
            <template x-for="member in workload" :key="member.name">
                <div class="flex items-center gap-3 py-2">
                    <div class="w-7 h-7 rounded-full text-[9px] font-bold flex items-center justify-center shrink-0"
                        :style="'background:'+strColor(member.name)+'33;color:'+strColor(member.name)"
                        x-text="member.name.slice(0,2).toUpperCase()"></div>
                    <span class="text-[13px] text-white/60 w-28 truncate" x-text="member.name"></span>
                    <div class="flex-1 h-2 bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full bg-teal-500 rounded-full" :style="'width:'+Math.min((member.incomplete/(member.incomplete+member.complete||1))*100,100)+'%'"></div>
                    </div>
                    <span class="text-[11px] text-white/30 w-16 text-right" x-text="member.incomplete + ' open'"></span>
                </div>
            </template>
            <p x-show="workload.length === 0" class="text-[12px] text-white/20 text-center py-4">No data yet</p>
        </div>

        {{-- Project Progress --}}
        <div class="bg-[#111122] border border-white/[0.07] rounded-2xl p-5">
            <h3 class="text-[14px] font-semibold text-white/70 mb-4">Project Progress</h3>
            <template x-for="proj in projects" :key="proj.id">
                <div class="flex items-center gap-3 py-2">
                    <span class="w-3 h-3 rounded-sm shrink-0" :style="'background:'+proj.color"></span>
                    <span class="text-[13px] text-white/60 flex-1 truncate" x-text="proj.name"></span>
                    <div class="w-24 h-2 bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full bg-teal-500 rounded-full" :style="'width:'+proj.progress+'%'"></div>
                    </div>
                    <span class="text-[11px] text-white/30 w-10 text-right" x-text="proj.progress+'%'"></span>
                </div>
            </template>
            <p x-show="projects.length === 0" class="text-[12px] text-white/20 text-center py-4">No projects yet</p>
        </div>
    </div>
</div>

<script>
function oppReports() {
    return {
        stats: {}, workload: [], projects: [],
        async loadAll() {
            const [w, p] = await Promise.all([
                fetch('/api/opp/reports/team-workload', {headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(r=>r.json()).catch(()=>({})),
                fetch('/api/opp/reports/project-progress', {headers:{'Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}}).then(r=>r.json()).catch(()=>({})),
            ]);
            this.workload = w.workload || [];
            this.projects = p.projects || [];
            // Compute stats from data
            let total = 0, done = 0, overdue = 0;
            this.workload.forEach(m => { total += (m.incomplete||0) + (m.complete||0); done += m.complete||0; });
            this.stats = { totalTasks: total, completedTasks: done, overdueTasks: overdue, totalProjects: this.projects.length };
        },
        strColor(s) { if(!s)return'#6B7280';let h=0;for(let i=0;i<s.length;i++)h=s.charCodeAt(i)+((h<<5)-h);return['#F43F5E','#EC4899','#A855F7','#6366F1','#3B82F6','#14B8A6','#10B981','#F59E0B','#EF4444','#8B5CF6'][Math.abs(h)%10]; },
    };
}
</script>

</x-layouts.opportunity>
