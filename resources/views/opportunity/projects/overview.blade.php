<x-layouts.opportunity title="Overview" :project="$project" currentView="overview">

@php
    $total = $project->tasks_count ?? 0;
    $done = $project->completed_tasks_count ?? 0;
    $overdue = $project->overdue_tasks_count ?? 0;
    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
@endphp

<div class="flex gap-0 h-[calc(100vh-140px)]">
    {{-- Main content --}}
    <div class="flex-1 overflow-y-auto px-8 py-6">

        {{-- Project description --}}
        <div class="mb-8" x-data="{ editing: false, desc: '{{ addslashes($project->description ?? '') }}' }">
            <h2 class="text-[16px] font-semibold text-white/70 mb-3">Project description</h2>
            <template x-if="!editing">
                <div @click="editing = true" class="text-[13px] text-white/45 cursor-pointer hover:bg-white/[0.02] rounded-lg p-2 -m-2 min-h-[60px]">
                    <span x-show="desc" x-text="desc"></span>
                    <span x-show="!desc" class="text-white/25 italic">Click to add a project description...</span>
                </div>
            </template>
            <template x-if="editing">
                <div>
                    <textarea x-model="desc" rows="4" class="w-full bg-white/[0.03] border border-white/[0.08] rounded-lg px-3 py-2 text-[13px] text-white/65 focus:outline-none focus:ring-1 focus:ring-teal-500/30 resize-none"></textarea>
                    <div class="flex gap-2 mt-2">
                        <button @click="fetch('/opportunity/projects/{{ $project->slug }}', { method:'PUT', headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}, body:JSON.stringify({description:desc}) }); editing=false"
                            class="px-3 py-1.5 rounded-lg bg-teal-500 text-white text-[12px] font-medium">Save</button>
                        <button @click="editing = false" class="px-3 py-1.5 rounded-lg text-[12px] text-white/40">Cancel</button>
                    </div>
                </div>
            </template>
        </div>

        {{-- Project roles --}}
        <div class="mb-8" x-data="memberManager()">
            <h2 class="text-[16px] font-semibold text-white/70 mb-3">Project roles</h2>
            <div class="flex flex-wrap gap-4">
                {{-- Add member button + dropdown --}}
                <div class="relative">
                    <button @click="showDropdown = !showDropdown; if(showDropdown) loadOrgMembers()"
                        class="flex items-center gap-2 text-white/30 hover:text-white/50 cursor-pointer transition-colors">
                        <div class="w-9 h-9 rounded-full border-2 border-dashed border-white/15 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span class="text-[13px]">Add member</span>
                    </button>

                    {{-- Dropdown --}}
                    <div x-show="showDropdown" x-cloak @click.outside="showDropdown = false"
                         class="absolute top-full left-0 mt-2 w-64 bg-[#1E1E32] border border-white/[0.1] rounded-xl shadow-2xl z-50 overflow-hidden">
                        <div class="p-2">
                            <input type="text" x-model="searchQuery" placeholder="Search by name..."
                                class="w-full px-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[12px] text-white/70 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-teal-500/40"/>
                        </div>
                        <div class="max-h-48 overflow-y-auto">
                            <template x-for="user in filteredOrgMembers" :key="user.id">
                                <button @click="addMember(user.id)"
                                    class="flex items-center gap-2.5 w-full px-3 py-2 text-left hover:bg-white/[0.05] transition-colors">
                                    <div class="w-7 h-7 rounded-full text-[9px] font-bold flex items-center justify-center shrink-0"
                                        :style="'background:'+strColor(user.name)+'33;color:'+strColor(user.name)"
                                        x-text="user.name.slice(0,2).toUpperCase()"></div>
                                    <div class="min-w-0">
                                        <div class="text-[12px] text-white/70 truncate" x-text="user.name"></div>
                                        <div class="text-[10px] text-white/30 truncate" x-text="user.email"></div>
                                    </div>
                                </button>
                            </template>
                            <div x-show="filteredOrgMembers.length === 0" class="px-3 py-3 text-center text-[11px] text-white/25">
                                No members to add
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Current members --}}
                <template x-for="(member, idx) in members" :key="member.id">
                    <div class="flex items-center gap-2 group relative">
                        <div class="w-9 h-9 rounded-full text-[11px] font-bold flex items-center justify-center"
                             :style="'background:'+strColor(member.name)+'33;color:'+strColor(member.name)"
                             x-text="member.name.slice(0,2).toUpperCase()"></div>
                        <div>
                            <div class="text-[13px] text-white/70" x-text="member.name"></div>
                            <div class="text-[10px] text-white/30" x-text="member.role === 'owner' ? 'Project owner' : member.role.charAt(0).toUpperCase() + member.role.slice(1)"></div>
                        </div>
                        {{-- Remove button (not for owner) --}}
                        <button x-show="member.role !== 'owner'" @click="removeMember(member)"
                            class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500/80 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"
                            title="Remove">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        @php
            $memberJson = $project->members->map(function($m) { return ['id' => $m->id, 'name' => $m->name, 'email' => $m->email, 'role' => $m->pivot->role]; });
            $orgMemberJson = $project->organization->members->map(function($m) { return ['id' => $m->id, 'name' => $m->name, 'email' => $m->email]; });
        @endphp
        <script>
        function memberManager() {
            return {
                members: @json($memberJson),
                orgMembers: @json($orgMemberJson),
                showDropdown: false,
                searchQuery: '',

                get filteredOrgMembers() {
                    const memberIds = this.members.map(m => m.id);
                    let available = this.orgMembers.filter(u => !memberIds.includes(u.id));
                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase();
                        available = available.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q));
                    }
                    return available;
                },

                async loadOrgMembers() {
                    // orgMembers already loaded from server-side data
                },

                async addMember(userId) {
                    const r = await fetch('/api/opp/projects/{{ $project->slug }}/members', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: JSON.stringify({ user_id: userId, role: 'editor' })
                    });
                    if (r.ok) {
                        const data = await r.json();
                        if (data.member) {
                            this.members.push(data.member);
                        }
                        this.showDropdown = false;
                        this.searchQuery = '';
                    }
                },

                async removeMember(member) {
                    if (!confirm('Remove ' + member.name + ' from this project?')) return;
                    const r = await fetch('/api/opp/projects/{{ $project->slug }}/members/' + member.id, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                    });
                    if (r.ok) {
                        this.members = this.members.filter(m => m.id !== member.id);
                    }
                },

                strColor(s) {
                    if(!s) return '#6B7280';
                    let h = 0;
                    for(let i = 0; i < s.length; i++) h = s.charCodeAt(i) + ((h << 5) - h);
                    return ['#F43F5E','#3B82F6','#10B981','#F59E0B','#8B5CF6','#EC4899','#14B8A6','#EF4444','#6366F1','#A855F7'][Math.abs(h) % 10];
                },
            };
        }
        </script>

        {{-- Key resources --}}
        <div>
            <h2 class="text-[16px] font-semibold text-white/70 mb-3">Key resources</h2>
            <div class="bg-white/[0.02] border border-white/[0.06] rounded-xl p-6 text-center">
                <p class="text-[13px] text-white/40 mb-3">Align your team around a shared vision with a project brief and supporting resources.</p>
                <div class="flex items-center justify-center gap-4">
                    <button class="flex items-center gap-1.5 text-[12px] text-teal-400 hover:text-teal-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Create project brief
                    </button>
                    <button class="flex items-center gap-1.5 text-[12px] text-white/35 hover:text-white/55">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/></svg>
                        Add links & files
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="w-80 shrink-0 border-l border-white/[0.06] overflow-y-auto px-5 py-6">

        {{-- Status --}}
        <div class="mb-6">
            <h3 class="text-[13px] text-white/50 mb-3">What's the status?</h3>
            <div class="flex gap-2" x-data="{ status: '{{ $project->status }}' }">
                @foreach(['on_track' => ['On track', 'green'], 'at_risk' => ['At risk', 'amber'], 'off_track' => ['Off track', 'red']] as $key => [$label, $color])
                    <button @click="status = '{{ $key }}'; fetch('/opportunity/projects/{{ $project->slug }}', { method:'PUT', headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}, body:JSON.stringify({status:'{{ $key }}'}) })"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium border transition-colors"
                        :class="status === '{{ $key }}' ? 'border-{{ $color }}-500/30 bg-{{ $color }}-500/15 text-{{ $color }}-400' : 'border-white/[0.08] text-white/35 hover:bg-white/[0.04]'">
                        <span class="w-2 h-2 rounded-full bg-{{ $color }}-500"></span>
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Stats --}}
        <div class="mb-6 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[12px] text-white/35">Tasks</span>
                <span class="text-[13px] text-white/60">{{ $total }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[12px] text-white/35">Completed</span>
                <span class="text-[13px] text-teal-400">{{ $done }} ({{ $pct }}%)</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[12px] text-white/35">Overdue</span>
                <span class="text-[13px] {{ $overdue > 0 ? 'text-red-400' : 'text-white/40' }}">{{ $overdue }}</span>
            </div>
            @if($project->due_date)
            <div class="flex items-center justify-between">
                <span class="text-[12px] text-white/35">Due date</span>
                <span class="text-[13px] text-white/60">{{ $project->due_date->format('M j, Y') }}</span>
            </div>
            @endif
        </div>

        {{-- Progress --}}
        @if($total > 0)
        <div class="mb-6">
            <div class="h-2 bg-white/[0.06] rounded-full overflow-hidden">
                <div class="h-full bg-teal-500 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <div class="text-[11px] text-white/25 mt-1.5 text-right">{{ $pct }}% complete</div>
        </div>
        @endif

        {{-- Member activity --}}
        <div>
            <h3 class="text-[13px] text-white/50 mb-3">Recent activity</h3>
            @foreach($project->members->take(5) as $member)
            <div class="flex items-center gap-2 py-2">
                <div class="w-6 h-6 rounded-full text-[8px] font-bold flex items-center justify-center"
                     style="background: {{ ['#F43F5E','#3B82F6','#10B981','#F59E0B','#8B5CF6'][($loop->index) % 5] }}33; color: {{ ['#F43F5E','#3B82F6','#10B981','#F59E0B','#8B5CF6'][($loop->index) % 5] }}">
                    {{ strtoupper(substr($member->name, 0, 2)) }}
                </div>
                <span class="text-[12px] text-white/45">{{ $member->name }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

</x-layouts.opportunity>
