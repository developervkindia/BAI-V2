<x-layouts.smartprojects :project="$project" currentView="milestones" :canEdit="$canEdit">
<div class="max-w-4xl mx-auto px-4 py-4" x-data="milestonesManager()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-sm font-semibold text-white/70">Milestones</h2>
            <p class="text-xs text-white/30 mt-0.5">{{ $project->milestones->count() }} milestones</p>
        </div>
        @if($canEdit)
        <button
            @click="showCreate = true"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium transition-colors border border-orange-500/20"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Milestone
        </button>
        @endif
    </div>

    {{-- Create milestone modal --}}
    @if($canEdit)
    <div
        x-show="showCreate"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="showCreate = false"
    >
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative bg-neutral-900 border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-sm font-semibold text-white/80 mb-4">New Milestone</h3>
            <div class="space-y-3">
                <div>
                    <label class="text-[10px] text-white/40 block mb-1">Name <span class="text-red-400">*</span></label>
                    <input
                        x-model="form.name"
                        type="text"
                        placeholder="e.g. Beta Launch"
                        class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/80 text-sm focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/20"
                        @keydown.enter="createMilestone()"
                    />
                </div>
                <div>
                    <label class="text-[10px] text-white/40 block mb-1">Description</label>
                    <textarea
                        x-model="form.description"
                        rows="2"
                        placeholder="Optional description…"
                        class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/80 text-sm focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/20 resize-none"
                    ></textarea>
                </div>
                <div>
                    <label class="text-[10px] text-white/40 block mb-1">Due date</label>
                    <input
                        x-model="form.due_date"
                        type="date"
                        class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/70 text-sm focus:ring-1 focus:ring-orange-500/40 focus:outline-none"
                    />
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showCreate = false" class="flex-1 py-2 rounded-xl border border-white/10 text-white/40 text-sm hover:border-white/20 transition-colors">Cancel</button>
                    <button type="button" @click="createMilestone()" class="flex-1 py-2 rounded-xl bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 transition-colors">Create</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Edit milestone modal --}}
    <div
        x-show="editingMilestone !== null"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="editingMilestone = null"
    >
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative bg-neutral-900 border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl" @click.stop>
            <template x-if="editingMilestone">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-white/80 mb-4">Edit Milestone</h3>
                    <div>
                        <label class="text-[10px] text-white/40 block mb-1">Name</label>
                        <input
                            x-model="editingMilestone.name"
                            type="text"
                            class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/80 text-sm focus:ring-1 focus:ring-orange-500/40 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="text-[10px] text-white/40 block mb-1">Description</label>
                        <textarea
                            x-model="editingMilestone.description"
                            rows="2"
                            class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/80 text-sm focus:ring-1 focus:ring-orange-500/40 focus:outline-none resize-none"
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] text-white/40 block mb-1">Due date</label>
                            <input
                                x-model="editingMilestone.due_date"
                                type="date"
                                class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/70 text-sm focus:ring-1 focus:ring-orange-500/40 focus:outline-none"
                            />
                        </div>
                        <div>
                            <label class="text-[10px] text-white/40 block mb-1">Status</label>
                            <select
                                x-model="editingMilestone.status"
                                class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/70 text-sm focus:ring-1 focus:ring-orange-500/40 focus:outline-none"
                            >
                                <option value="open">Open</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="editingMilestone = null" class="flex-1 py-2 rounded-xl border border-white/10 text-white/40 text-sm hover:border-white/20 transition-colors">Cancel</button>
                        <button type="button" @click="updateMilestone()" class="flex-1 py-2 rounded-xl bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 transition-colors">Save</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Milestones list --}}
    @if($project->milestones->isEmpty())
        <div class="text-center py-16">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-orange-400/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H13.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
            </div>
            <p class="text-white/30 text-sm">No milestones yet</p>
            @if($canEdit)
            <button @click="showCreate = true" class="mt-3 text-xs text-orange-400/70 hover:text-orange-400 transition-colors">+ Create first milestone</button>
            @endif
        </div>
    @else
        <div class="space-y-3" x-data>
            @foreach($project->milestones->sortBy('due_date') as $milestone)
                @php
                    $total = $milestone->tasks->count();
                    $done  = $milestone->tasks->where('is_completed', true)->count();
                    $pct   = $total > 0 ? round(($done / $total) * 100) : 0;
                    $isOverdue = $milestone->due_date && $milestone->due_date->isPast() && $milestone->status !== 'completed';
                    $statusClass = $milestone->status === 'completed'
                        ? 'bg-green-500/20 text-green-400'
                        : ($isOverdue ? 'bg-red-500/20 text-red-400' : 'bg-white/10 text-white/40');
                    $statusLabel = $milestone->status === 'completed' ? 'Completed' : ($isOverdue ? 'Overdue' : 'Open');
                @endphp
                <div
                    class="bg-white/[0.03] border border-white/5 rounded-2xl p-5 hover:border-white/10 transition-colors"
                    x-data="{ expanded: true }"
                >
                    <div class="flex items-start justify-between gap-3">
                        {{-- Left: flag icon + info --}}
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <div class="w-8 h-8 rounded-xl {{ $milestone->status === 'completed' ? 'bg-green-500/20' : 'bg-orange-500/20' }} flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4 {{ $milestone->status === 'completed' ? 'text-green-400' : 'text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H13.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                    <h3 class="text-sm font-semibold text-white/80">{{ $milestone->name }}</h3>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-full font-medium {{ $statusClass }}">{{ $statusLabel }}</span>
                                </div>
                                @if($milestone->description)
                                    <p class="text-xs text-white/30 leading-relaxed mb-2">{{ $milestone->description }}</p>
                                @endif
                                <div class="flex items-center gap-4 text-[10px] text-white/25">
                                    @if($milestone->due_date)
                                        <span class="{{ $isOverdue ? 'text-red-400/70' : '' }}">
                                            Due {{ $milestone->due_date->format('M j, Y') }}
                                        </span>
                                    @endif
                                    <span>{{ $done }}/{{ $total }} tasks</span>
                                </div>
                            </div>
                        </div>

                        {{-- Right: progress + actions --}}
                        <div class="flex items-center gap-3 shrink-0">
                            <div class="text-right">
                                <div class="text-xs font-semibold text-white/50">{{ $pct }}%</div>
                                <div class="w-20 h-1 bg-white/5 rounded-full overflow-hidden mt-1">
                                    <div class="h-full rounded-full transition-all {{ $milestone->status === 'completed' ? 'bg-green-400/60' : 'bg-orange-400/60' }}" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            @if($canEdit)
                            <div class="flex items-center gap-1">
                                <button
                                    @click="$dispatch('edit-milestone', {{ json_encode(['id' => $milestone->id, 'name' => $milestone->name, 'description' => $milestone->description, 'due_date' => $milestone->due_date?->format('Y-m-d'), 'status' => $milestone->status]) }})"
                                    class="p-1.5 rounded-lg text-white/20 hover:text-white/50 hover:bg-white/5 transition-colors"
                                    title="Edit milestone"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button
                                    @click="deleteMilestone({{ $milestone->id }})"
                                    class="p-1.5 rounded-lg text-white/20 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                                    title="Delete milestone"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                            @endif
                            <button
                                @click="expanded = !expanded"
                                class="p-1.5 rounded-lg text-white/20 hover:text-white/50 hover:bg-white/5 transition-colors"
                            >
                                <svg class="w-3.5 h-3.5 transition-transform" :class="expanded ? '' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Tasks accordion --}}
                    <div x-show="expanded" class="mt-4 pl-11">
                        @if($milestone->tasks->isEmpty())
                            <p class="text-xs text-white/20 italic">No tasks assigned to this milestone.</p>
                        @else
                            <div class="space-y-1.5">
                                @foreach($milestone->tasks as $task)
                                    @php
                                        $priorityClasses = ['critical' => 'text-red-400', 'high' => 'text-orange-400', 'medium' => 'text-orange-400', 'low' => 'text-blue-400', 'none' => 'text-white/20'];
                                        $taskDuePast = $task->due_date && $task->due_date->isPast() && !$task->is_completed;
                                    @endphp
                                    <div class="flex items-center gap-3 py-1.5 px-3 rounded-xl hover:bg-white/[0.03] group">
                                        <div class="w-2 h-2 rounded-full shrink-0 {{ $task->is_completed ? 'bg-green-400/60' : 'bg-white/15' }}"></div>
                                        <span class="flex-1 text-xs {{ $task->is_completed ? 'text-white/25 line-through' : 'text-white/60' }}">{{ $task->title }}</span>
                                        @if($task->priority && $task->priority !== 'none')
                                            <span class="text-[9px] font-medium {{ $priorityClasses[$task->priority] ?? 'text-white/20' }}">{{ ucfirst($task->priority) }}</span>
                                        @endif
                                        @if($task->due_date)
                                            <span class="text-[10px] {{ $taskDuePast ? 'text-red-400/70' : 'text-white/20' }}">{{ $task->due_date->format('M j') }}</span>
                                        @endif
                                        @if($task->assignee)
                                            <div class="w-5 h-5 rounded-full bg-white/10 text-white/40 text-[8px] font-bold flex items-center justify-center" title="{{ $task->assignee->name }}">
                                                {{ strtoupper(substr($task->assignee->name, 0, 2)) }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
</x-layouts.smartprojects>

<script>
function milestonesManager() {
    const projectId = {{ $project->id }};

    return {
        showCreate: false,
        editingMilestone: null,
        form: { name: '', description: '', due_date: '' },

        init() {
            window.addEventListener('edit-milestone', (e) => {
                this.editingMilestone = { ...e.detail };
            });
        },

        async createMilestone() {
            if (!this.form.name.trim()) return;
            const res = await this.apiCall('POST', `/api/projects/${projectId}/milestones`, this.form);
            if (res) window.location.reload();
        },

        async updateMilestone() {
            if (!this.editingMilestone) return;
            const { id, ...data } = this.editingMilestone;
            const res = await this.apiCall('PUT', `/api/project-milestones/${id}`, data);
            if (res) window.location.reload();
        },

        async deleteMilestone(id) {
            if (!confirm('Delete this milestone? Tasks will not be deleted.')) return;
            const res = await this.apiCall('DELETE', `/api/project-milestones/${id}`);
            if (res !== null) window.location.reload();
        },

        async apiCall(method, url, data = null) {
            try {
                const opts = {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                };
                if (data) opts.body = JSON.stringify(data);
                const r = await fetch(url, opts);
                if (method === 'DELETE') return r.ok ? {} : null;
                if (!r.ok) return null;
                return await r.json();
            } catch { return null; }
        },
    };
}
</script>
