<x-layouts.smartprojects :project="$project" currentView="list" :canEdit="$canEdit">

@php
$initData = [
    'projectId'   => $project->slug,
    'canEdit'     => $canEdit,
    'statuses'     => $project->statuses->map(fn($s) => [
        'id'    => $s->id,
        'name'  => $s->name,
        'slug'  => $s->slug,
        'color' => $s->color,
        'is_completed_state' => $s->is_completed_state,
        'is_default' => $s->is_default,
    ])->values(),
    'customFields' => $project->customFields->map(fn($f) => [
        'id'       => $f->id,
        'name'     => $f->name,
        'type'     => $f->type,
        'options'  => $f->options,
        'is_required' => $f->is_required,
    ])->values(),
    'taskLists'   => $project->taskLists->map(fn($tl) => [
        'id'    => $tl->id,
        'name'  => $tl->name,
        'tasks' => $tl->tasks->map(fn($t) => [
            'id'                => $t->id,
            'title'             => $t->title,
            'status'            => $t->status,
            'project_status_id' => $t->project_status_id,
            'status_name'       => $t->status_name,
            'status_color'      => $t->status_color,
            'priority'          => $t->priority,
            'issue_type'        => $t->issue_type ?? 'task',
            'story_points'      => $t->story_points,
            'task_list_id'      => $t->task_list_id,
            'assignee_id'       => $t->assignee_id,
            'assignee'          => $t->assignee ? ['id' => $t->assignee->id, 'name' => $t->assignee->name] : null,
            'due_date'          => $t->due_date?->format('Y-m-d'),
            'due_date_fmt'      => $t->due_date?->format('M j'),
            'due_date_status'   => $t->due_date_status ?? 'upcoming',
            'is_completed'      => (bool) $t->is_completed,
            'position'          => $t->position,
            'subtask_count'     => $t->subtasks->count(),
            'milestone_id'      => $t->milestone_id ?? null,
            'labels'            => $t->labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values(),
        ])->values(),
    ])->values(),
    'members'    => $project->members->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values(),
    'milestones' => $project->milestones->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values(),
    'labels'     => $project->labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values(),
];
@endphp

<div
    class="flex flex-col"
    x-data="projectManager({{ Js::from($initData) }})"
    x-init="init()"
    @open-create-task.window="startAddTask(taskLists[0]?.id)"
>

{{-- ================================================================ --}}
{{-- TOOLBAR                                                           --}}
{{-- ================================================================ --}}
<div class="border-b border-white/[0.06] bg-[#0D0D18]">
    <div class="flex items-center gap-1.5 px-5 py-2.5 flex-wrap">

        {{-- Status filter tabs (dynamic from project statuses) --}}
        <button @click="filterStatus = ''"
            :class="filterStatus === ''
                ? 'bg-orange-500/15 text-orange-300 border-orange-500/20'
                : 'text-white/38 border-transparent hover:text-white/60 hover:bg-white/[0.04]'"
            class="px-3 py-1.5 rounded-lg text-[12px] font-medium border transition-all">All</button>
        <template x-for="st in statuses" :key="st.id">
            <button @click="filterStatus = st.slug"
                :class="filterStatus === st.slug
                    ? 'border-opacity-20 text-opacity-90'
                    : 'text-white/38 border-transparent hover:text-white/60 hover:bg-white/[0.04]'"
                :style="filterStatus === st.slug ? 'background:' + st.color + '15; color:' + st.color + '; border-color:' + st.color + '33' : ''"
                class="px-3 py-1.5 rounded-lg text-[12px] font-medium border transition-all"
                x-text="st.name">
            </button>
        </template>

        <div class="flex-1 min-w-4"></div>

        {{-- Saved Views dropdown --}}
        <div x-data="{ open: false, viewName: '' }" @click.outside="open = false" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] border transition-all"
                :class="activeView ? 'text-orange-400 border-orange-500/20 bg-orange-500/10' : 'text-white/38 border-transparent hover:border-white/[0.1] hover:text-white/60 hover:bg-white/[0.04]'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                <span x-text="activeView ? activeView.name : 'Views'"></span>
            </button>
            <div x-show="open" x-cloak
                 class="absolute right-0 top-full mt-1.5 z-30 w-56 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-2xl p-2 space-y-1">
                <template x-for="sv in savedViews" :key="sv.id">
                    <div class="flex items-center gap-1.5 group">
                        <button @click="loadView(sv); open = false"
                            class="flex-1 text-left px-2.5 py-1.5 rounded-lg text-[12px] transition-colors"
                            :class="activeView?.id === sv.id ? 'text-orange-400 bg-orange-500/10' : 'text-white/55 hover:text-white/85 hover:bg-white/[0.05]'">
                            <span x-text="sv.name"></span>
                            <span x-show="sv.is_shared" class="ml-1 text-[9px] text-white/25">(shared)</span>
                        </button>
                        <button x-show="sv.is_mine" @click="deleteView(sv.id)" class="opacity-0 group-hover:opacity-100 p-1 text-red-400/60 hover:text-red-400 transition-all">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <div class="border-t border-white/[0.06] pt-1.5 mt-1.5">
                    <button x-show="!viewName" @click="viewName = ' '" class="w-full text-left px-2.5 py-1.5 rounded-lg text-[11px] text-orange-400/70 hover:bg-orange-500/10 hover:text-orange-400 transition-colors">
                        + Save current view
                    </button>
                    <div x-show="viewName" class="flex gap-1.5">
                        <input type="text" x-model.trim="viewName" placeholder="View name…"
                            @keydown.enter="saveCurrentView(viewName); viewName = ''; open = false"
                            class="flex-1 px-2.5 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-[11px] text-white/70 focus:outline-none focus:ring-1 focus:ring-orange-500/40"/>
                        <button @click="saveCurrentView(viewName); viewName = ''; open = false"
                            class="px-2 py-1.5 rounded-lg text-[11px] bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 transition-colors">Save</button>
                    </div>
                </div>
                <button x-show="activeView" @click="clearView(); open = false"
                    class="w-full text-left px-2.5 py-1.5 rounded-lg text-[11px] text-white/35 hover:bg-white/[0.05] hover:text-white/55 transition-colors">
                    Clear view
                </button>
            </div>
        </div>

        {{-- Filter dropdown --}}
        <div x-data="{ open: false }" @click.outside="open = false" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] border transition-all"
                :class="(filterPriority||filterAssignee||filterIssueType||filterLabel)
                    ? 'text-orange-400 border-orange-500/20 bg-orange-500/10'
                    : 'text-white/38 border-transparent hover:border-white/[0.1] hover:text-white/60 hover:bg-white/[0.04]'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h18M6 8h12M9 12h6"/></svg>
                Filter
                <span x-show="filterPriority||filterAssignee||filterIssueType||filterLabel"
                    class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
            </button>
            <div x-show="open" x-cloak
                 class="absolute right-0 top-full mt-1.5 z-30 w-56 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-2xl p-4 space-y-3">
                <div>
                    <label class="text-[10px] font-semibold text-white/28 uppercase tracking-wider block mb-1.5">Priority</label>
                    <select x-model="filterPriority" class="w-full px-2.5 py-1.5 rounded-lg bg-white/[0.04] border border-white/[0.07] text-[12px] text-white/55 focus:outline-none focus:ring-1 focus:ring-orange-500/30 appearance-none">
                        <option value="">All priorities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-semibold text-white/28 uppercase tracking-wider block mb-1.5">Assignee</label>
                    <select x-model="filterAssignee" class="w-full px-2.5 py-1.5 rounded-lg bg-white/[0.04] border border-white/[0.07] text-[12px] text-white/55 focus:outline-none focus:ring-1 focus:ring-orange-500/30 appearance-none">
                        <option value="">Anyone</option>
                        <template x-for="m in members" :key="m.id">
                            <option :value="m.id" x-text="m.name"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-semibold text-white/28 uppercase tracking-wider block mb-1.5">Type</label>
                    <select x-model="filterIssueType" class="w-full px-2.5 py-1.5 rounded-lg bg-white/[0.04] border border-white/[0.07] text-[12px] text-white/55 focus:outline-none focus:ring-1 focus:ring-orange-500/30 appearance-none">
                        <option value="">All types</option>
                        <option value="task">Task</option>
                        <option value="bug">Bug</option>
                        <option value="story">Story</option>
                        <option value="epic">Epic</option>
                    </select>
                </div>
                <button x-show="filterPriority||filterAssignee||filterIssueType||filterLabel"
                    @click="clearFilters(); open = false"
                    class="w-full py-1.5 rounded-lg border border-red-500/20 text-[11px] text-red-400/70 hover:bg-red-500/10 hover:text-red-400 transition-colors">
                    Clear Filters
                </button>
            </div>
        </div>

        {{-- Group by --}}
        <div class="flex items-center gap-1.5">
            <span class="text-[11px] text-white/22 hidden sm:inline">Group:</span>
            <select x-model="groupBy"
                class="text-[11px] bg-white/[0.04] border border-white/[0.07] rounded-lg px-2.5 py-1.5 text-white/45 focus:outline-none cursor-pointer hover:bg-white/[0.07] transition-colors appearance-none">
                <option value="none">Section</option>
                <option value="status">Status</option>
                <option value="priority">Priority</option>
                <option value="assignee">Assignee</option>
                <option value="milestone">Milestone</option>
            </select>
        </div>

        {{-- Add section --}}
        <button x-show="canEdit && !addingSection"
            @click="addingSection = true; $nextTick(() => $refs.newSectionInput?.focus())"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] text-white/35 border border-transparent hover:border-white/[0.1] hover:text-white/60 hover:bg-white/[0.04] transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Section
        </button>
    </div>

    {{-- Inline section name input --}}
    <div x-show="addingSection" x-cloak class="px-5 pb-3">
        <form @submit.prevent="createTaskList()">
            <input x-ref="newSectionInput" type="text" x-model="newSectionName"
                placeholder="Section name…"
                @keydown.escape="addingSection = false; newSectionName = ''"
                class="px-3 py-1.5 rounded-lg bg-white/[0.05] border border-orange-500/30 text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-orange-500/40 w-56"/>
        </form>
    </div>
</div>

{{-- ================================================================ --}}
{{-- BULK ACTION BAR                                                   --}}
{{-- ================================================================ --}}
<div x-show="selectedTasks.length > 0" x-cloak
     class="flex items-center gap-3 px-5 py-2 bg-orange-500/[0.07] border-b border-orange-500/15">
    <span class="text-[12px] font-medium text-orange-400" x-text="selectedTasks.length + ' selected'"></span>
    <div class="h-3.5 w-px bg-orange-500/20"></div>
    <div class="flex items-center gap-2 flex-wrap">
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="px-2.5 py-1 rounded-lg text-[11px] text-white/55 hover:bg-white/[0.07] hover:text-white/80 border border-white/[0.08] transition-colors">Status</button>
            <div x-show="open" x-cloak @click.outside="open = false" class="absolute top-full left-0 mt-1 z-30 w-36 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-xl py-1">
                <template x-for="s in statuses" :key="s.id">
                    <button @click="bulkUpdate('project_status_id', s.id); open = false" class="w-full text-left px-3 py-1.5 text-[11px] text-white/60 hover:bg-white/[0.05] hover:text-white/85 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full shrink-0" :style="'background:' + s.color"></span>
                        <span x-text="s.name"></span>
                    </button>
                </template>
            </div>
        </div>
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="px-2.5 py-1 rounded-lg text-[11px] text-white/55 hover:bg-white/[0.07] hover:text-white/80 border border-white/[0.08] transition-colors">Priority</button>
            <div x-show="open" x-cloak @click.outside="open = false" class="absolute top-full left-0 mt-1 z-30 w-36 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-xl py-1">
                <template x-for="p in ['critical','high','medium','low','none']" :key="p">
                    <button @click="bulkUpdate('priority', p); open = false" class="w-full text-left px-3 py-1.5 text-[11px] text-white/60 hover:bg-white/[0.05] hover:text-white/85 capitalize" x-text="p"></button>
                </template>
            </div>
        </div>
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="px-2.5 py-1 rounded-lg text-[11px] text-white/55 hover:bg-white/[0.07] hover:text-white/80 border border-white/[0.08] transition-colors">Assign</button>
            <div x-show="open" x-cloak @click.outside="open = false" class="absolute top-full left-0 mt-1 z-30 w-44 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-xl py-1">
                <button @click="bulkUpdate('assignee_id', null); open = false" class="w-full text-left px-3 py-1.5 text-[11px] text-white/40 hover:bg-white/[0.05]">Unassign</button>
                <template x-for="m in members" :key="m.id">
                    <button @click="bulkUpdate('assignee_id', m.id); open = false" class="w-full text-left px-3 py-1.5 text-[11px] text-white/60 hover:bg-white/[0.05] hover:text-white/85" x-text="m.name"></button>
                </template>
            </div>
        </div>
        <button @click="bulkDelete()" class="px-2.5 py-1 rounded-lg text-[11px] text-red-400/70 hover:text-red-400 hover:bg-red-500/10 border border-red-500/15 transition-colors">Delete</button>
    </div>
    <div class="flex-1"></div>
    <button @click="selectedTasks = []" class="w-5 h-5 flex items-center justify-center rounded hover:bg-white/10 text-white/40 hover:text-white/70 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>

{{-- ================================================================ --}}
{{-- COLUMN HEADERS                                                    --}}
{{-- ================================================================ --}}
<div class="sticky top-36 z-10 bg-[#0B0B14] border-b border-white/[0.05]">
    <div class="grid items-center px-5 py-2" style="grid-template-columns: 20px 18px 1fr 115px 80px 80px 32px">
        <div class="flex items-center justify-center">
            <input type="checkbox"
                @change="selectAll($event.target.checked)"
                :checked="selectedTasks.length > 0 && selectedTasks.length === allVisibleTaskIds().length"
                :indeterminate="selectedTasks.length > 0 && selectedTasks.length < allVisibleTaskIds().length"
                class="w-3.5 h-3.5 rounded accent-orange-500 cursor-pointer"/>
        </div>
        <div></div>
        <span class="text-[10px] font-semibold uppercase tracking-wider text-white/22">Task</span>
        <span class="text-[10px] font-semibold uppercase tracking-wider text-white/22 text-center">Status</span>
        <span class="text-[10px] font-semibold uppercase tracking-wider text-white/22 text-center">Owner</span>
        <span class="text-[10px] font-semibold uppercase tracking-wider text-white/22 text-center">Due</span>
        <div></div>
    </div>
</div>

{{-- ================================================================ --}}
{{-- TASK TABLE                                                        --}}
{{-- ================================================================ --}}
<div class="pb-8">

    {{-- ── Group by section ── --}}
    <template x-if="groupBy === 'none'">
        <div>
            <template x-for="tl in taskLists" :key="tl.id">
                <div :id="'tl-' + tl.id">

                    {{-- Section header --}}
                    <div class="flex items-center gap-2 px-5 py-2.5 cursor-pointer group/hdr hover:bg-white/[0.015] transition-colors border-b border-white/[0.03]"
                         @click="toggleSection(tl.id)">
                        <svg class="w-3.5 h-3.5 text-white/28 transition-transform duration-150 shrink-0"
                             :class="collapsedSections.includes(tl.id) ? '-rotate-90' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>

                        <template x-if="editingListId === tl.id">
                            <input type="text" x-model="editingListName"
                                @keydown.enter.prevent="saveListRename(tl)"
                                @keydown.escape="editingListId = null; editingListName = ''"
                                @blur="saveListRename(tl)"
                                @click.stop
                                class="max-w-[220px] bg-white/[0.07] border border-orange-500/40 rounded-lg px-2 py-0.5 text-[12px] text-white/90 focus:outline-none"
                                x-effect="if (editingListId === tl.id) $nextTick(() => $el.focus())"/>
                        </template>
                        <template x-if="editingListId !== tl.id">
                            <span class="text-[12px] font-semibold text-white/55 group-hover/hdr:text-white/78 transition-colors" x-text="tl.name"></span>
                        </template>

                        <span class="text-[10px] text-white/20 bg-white/[0.04] px-1.5 py-0.5 rounded-full shrink-0"
                              x-text="tl.tasks.filter(t => isTaskVisible(t)).length"></span>
                        <div class="flex-1 h-px bg-white/[0.04]"></div>

                        <div class="flex items-center gap-1 opacity-0 group-hover/hdr:opacity-100 transition-opacity shrink-0" x-show="canEdit" @click.stop>
                            <button @click.stop="startAddTask(tl.id)"
                                class="flex items-center gap-1 text-[11px] text-white/28 hover:text-orange-400 hover:bg-orange-500/10 px-2 py-0.5 rounded-lg transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add
                            </button>
                            <div class="relative" x-data="{ menuOpen: false }" @click.outside="menuOpen = false">
                                <button @click.stop="menuOpen = !menuOpen"
                                    class="w-5 h-5 flex items-center justify-center rounded hover:bg-white/10 text-white/28 hover:text-white/65 transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </button>
                                <div x-show="menuOpen" x-cloak class="absolute right-0 top-full mt-0.5 z-30 w-32 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-xl overflow-hidden py-1">
                                    <button @click.stop="editingListId = tl.id; editingListName = tl.name; menuOpen = false"
                                        class="w-full text-left px-3 py-1.5 text-[11px] text-white/55 hover:bg-white/[0.05] hover:text-white/85">Rename</button>
                                    <button @click.stop="deleteTaskList(tl); menuOpen = false"
                                        class="w-full text-left px-3 py-1.5 text-[11px] text-red-400/70 hover:bg-red-500/10 hover:text-red-400">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Task rows --}}
                    <div x-show="!collapsedSections.includes(tl.id)">
                        <template x-for="task in tl.tasks.filter(t => isTaskVisible(t))" :key="task.id">
                            <div class="grid items-center px-5 py-0 hover:bg-white/[0.02] group/row cursor-pointer border-b border-white/[0.03] transition-colors"
                                 style="grid-template-columns: 20px 18px 1fr 115px 80px 80px 32px"
                                 :class="task.is_completed ? 'opacity-45' : ''"
                                 @click="openTask(task)">

                                {{-- Select --}}
                                <div class="flex items-center justify-center py-2.5" @click.stop>
                                    <input type="checkbox" :value="task.id" :checked="selectedTasks.includes(task.id)"
                                        @change="toggleSelectTask(task.id)"
                                        class="w-3.5 h-3.5 rounded accent-orange-500 cursor-pointer"/>
                                </div>

                                {{-- Complete toggle --}}
                                <div class="flex items-center justify-center py-2.5" @click.stop>
                                    <button @click="toggleComplete(task)"
                                        class="w-3.5 h-3.5 rounded-sm border flex items-center justify-center transition-all shrink-0"
                                        :class="task.is_completed
                                            ? 'bg-orange-500/30 border-orange-500/50'
                                            : 'border-white/15 hover:border-orange-400/50 opacity-0 group-hover/row:opacity-100'">
                                        <svg x-show="task.is_completed" class="w-2.5 h-2.5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Title + priority dot --}}
                                <div class="flex items-center gap-1.5 min-w-0 py-2.5" @click.stop>
                                    <span x-show="task.priority && task.priority !== 'none'"
                                        class="w-1.5 h-1.5 rounded-full shrink-0"
                                        :class="{
                                            'bg-red-400': task.priority === 'critical',
                                            'bg-orange-400': task.priority === 'high',
                                            'bg-yellow-400': task.priority === 'medium',
                                            'bg-blue-400': task.priority === 'low',
                                        }"></span>
                                    <template x-if="editingTaskId === task.id && editingField === 'title'">
                                        <input type="text" x-model="editingValue"
                                            @keydown.enter.prevent="saveInlineEdit(task)"
                                            @keydown.escape="cancelInlineEdit()"
                                            @blur="saveInlineEdit(task)"
                                            class="flex-1 min-w-0 bg-white/[0.07] border border-orange-500/40 rounded-lg px-2 py-0.5 text-[13px] text-white/90 focus:outline-none"
                                            x-effect="if (editingTaskId === task.id && editingField === 'title') $nextTick(() => $el.focus())"/>
                                    </template>
                                    <template x-if="!(editingTaskId === task.id && editingField === 'title')">
                                        <span class="text-[13px] truncate transition-colors cursor-pointer"
                                            :class="task.is_completed ? 'text-white/28 line-through' : 'text-white/75 group-hover/row:text-white/90'"
                                            x-text="task.title"
                                            @click="openTask(task)"
                                            @dblclick.stop="startInlineEdit(task, 'title')"></span>
                                    </template>
                                    <span x-show="task.subtask_count > 0"
                                        class="shrink-0 text-[9px] text-white/22 bg-white/[0.05] px-1 py-0.5 rounded"
                                        x-text="task.subtask_count"></span>
                                </div>

                                {{-- Status --}}
                                <div class="flex items-center justify-center py-2" @click.stop>
                                    <template x-if="editingTaskId === task.id && editingField === 'status'">
                                        <select x-model="editingValue"
                                            @change="saveInlineEdit(task, 'project_status_id', parseInt($el.value))" @blur="cancelInlineEdit()"
                                            class="text-[11px] bg-[#17172A] border border-orange-500/40 rounded-lg px-2 py-1 text-white/70 focus:outline-none w-full appearance-none"
                                            x-effect="if (editingTaskId === task.id && editingField === 'status') $nextTick(() => $el.focus())">
                                            <template x-for="st in statuses" :key="st.id">
                                                <option :value="st.id" x-text="st.name" :selected="st.id == task.project_status_id"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="!(editingTaskId === task.id && editingField === 'status')">
                                        <button @click.stop="startInlineEdit(task, 'status')"
                                            class="px-2.5 py-0.5 rounded-full text-[10px] font-medium whitespace-nowrap transition-all cursor-pointer"
                                            :style="'background:' + (task.status_color || '#94A3B8') + '15; color:' + (task.status_color || '#94A3B8')"
                                            x-text="task.status_name || 'Open'">
                                        </button>
                                    </template>
                                </div>

                                {{-- Assignee --}}
                                <div class="flex items-center justify-center py-2" @click.stop>
                                    <template x-if="editingTaskId === task.id && editingField === 'assignee'">
                                        <select x-model="editingValue"
                                            @change="saveInlineEdit(task)" @blur="cancelInlineEdit()"
                                            class="text-[11px] bg-[#17172A] border border-orange-500/40 rounded-lg px-2 py-1 text-white/70 focus:outline-none w-full appearance-none"
                                            x-effect="if (editingTaskId === task.id && editingField === 'assignee') $nextTick(() => $el.focus())">
                                            <option value="">Unassigned</option>
                                            <template x-for="m in members" :key="m.id">
                                                <option :value="m.id" x-text="m.name"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="!(editingTaskId === task.id && editingField === 'assignee')">
                                        <div @click.stop="startInlineEdit(task, 'assignee')">
                                            <template x-if="task.assignee">
                                                <div class="w-6 h-6 rounded-full bg-orange-500/15 text-orange-300 text-[9px] font-bold flex items-center justify-center ring-1 ring-orange-500/20 hover:ring-orange-400/50 cursor-pointer transition-all"
                                                    :title="task.assignee.name"
                                                    x-text="task.assignee.name.slice(0,2).toUpperCase()"></div>
                                            </template>
                                            <template x-if="!task.assignee">
                                                <div class="w-6 h-6 rounded-full border border-dashed border-white/10 hover:border-white/25 cursor-pointer opacity-0 group-hover/row:opacity-100 flex items-center justify-center transition-all">
                                                    <svg class="w-3 h-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                {{-- Due Date --}}
                                <div class="flex items-center justify-center py-2" @click.stop>
                                    <template x-if="editingTaskId === task.id && editingField === 'due_date'">
                                        <input type="date" x-model="editingValue"
                                            @change="saveInlineEdit(task)" @blur="cancelInlineEdit()"
                                            class="text-[11px] bg-[#17172A] border border-orange-500/40 rounded-lg px-2 py-1 text-white/70 focus:outline-none w-28"
                                            x-effect="if (editingTaskId === task.id && editingField === 'due_date') $nextTick(() => $el.focus())"/>
                                    </template>
                                    <template x-if="!(editingTaskId === task.id && editingField === 'due_date')">
                                        <div @click.stop="startInlineEdit(task, 'due_date')">
                                            <template x-if="task.due_date">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-lg font-medium cursor-pointer"
                                                    :class="{
                                                        'bg-red-500/15 text-red-400': task.due_date_status === 'overdue',
                                                        'bg-orange-500/15 text-orange-400': task.due_date_status === 'today' || task.due_date_status === 'soon',
                                                        'text-white/30 hover:text-white/55': task.due_date_status === 'upcoming'
                                                    }"
                                                    x-text="task.due_date_fmt"></span>
                                            </template>
                                            <template x-if="!task.due_date">
                                                <span class="text-white/15 opacity-0 group-hover/row:opacity-100 text-[11px] cursor-pointer transition-opacity">+ Date</span>
                                            </template>
                                        </div>
                                    </template>
                                </div>

                                {{-- Actions --}}
                                <div class="flex items-center justify-center py-2" @click.stop>
                                    <div class="relative opacity-0 group-hover/row:opacity-100 transition-opacity" x-data="{ open: false }">
                                        <button @click.stop="open = !open"
                                            class="w-6 h-6 flex items-center justify-center rounded hover:bg-white/[0.08] text-white/28 hover:text-white/65 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </button>
                                        <div x-show="open" x-cloak @click.outside="open = false"
                                             class="absolute right-0 top-full mt-0.5 z-30 w-36 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-xl overflow-hidden py-1">
                                            <button @click.stop="openTask(task); open = false"
                                                class="w-full text-left px-3 py-1.5 text-[11px] text-white/55 hover:bg-white/[0.05] hover:text-white/85 flex items-center gap-2">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Open
                                            </button>
                                            <button @click.stop="deleteTask(task); open = false"
                                                class="w-full text-left px-3 py-1.5 text-[11px] text-red-400/70 hover:bg-red-500/10 hover:text-red-400 flex items-center gap-2">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Inline add task --}}
                        <div x-show="addingToList === tl.id" x-cloak class="border-b border-white/[0.03] bg-orange-500/[0.02]">
                            <form @submit.prevent="createTask(tl.id)"
                                class="grid items-center px-5 py-2"
                                style="grid-template-columns: 20px 18px 1fr auto auto">
                                <div></div><div></div>
                                <input x-ref="newTaskInput" type="text" x-model="newTaskTitle"
                                    placeholder="Task title…"
                                    @keydown.escape="addingToList = null; newTaskTitle = ''"
                                    class="bg-transparent text-[13px] text-white/80 placeholder-white/25 focus:outline-none py-1.5"/>
                                <button type="submit" class="ml-3 text-[11px] px-3 py-1 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 border border-orange-500/20 whitespace-nowrap transition-colors">Add</button>
                                <button type="button" @click="addingToList = null; newTaskTitle = ''" class="ml-2 text-[11px] text-white/30 hover:text-white/60 whitespace-nowrap transition-colors">Cancel</button>
                            </form>
                        </div>

                        {{-- Add task row — always visible --}}
                        <button x-show="canEdit && addingToList !== tl.id"
                            @click="startAddTask(tl.id)"
                            class="flex items-center gap-2 w-full px-9 py-2.5 text-[12px] text-white/28 hover:text-orange-400 hover:bg-orange-500/[0.04] transition-colors border-b border-white/[0.02]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add task
                        </button>
                    </div>

                </div>
            </template>

            {{-- Add section footer --}}
            <div x-show="canEdit" class="px-5 pt-4 pb-2">
                <button x-show="!addingSection"
                    @click="addingSection = true; $nextTick(() => $refs.newSectionInput?.focus())"
                    class="flex items-center gap-1.5 text-[11px] text-white/22 hover:text-white/50 transition-colors px-2 py-1.5 rounded-lg hover:bg-white/[0.03]">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Section
                </button>
            </div>
        </div>
    </template>

    {{-- ── Group by non-section ── --}}
    <template x-if="groupBy !== 'none'">
        <div>
            <template x-for="group in groupedSections()" :key="group.key">
                <div>
                    <div class="flex items-center gap-2 px-5 py-2.5 cursor-pointer group/hdr hover:bg-white/[0.015] transition-colors border-b border-white/[0.03]"
                         @click="toggleSection('group-' + group.key)">
                        <svg class="w-3.5 h-3.5 text-white/28 transition-transform duration-150 shrink-0"
                             :class="collapsedSections.includes('group-' + group.key) ? '-rotate-90' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <span class="text-[12px] font-semibold text-white/55 group-hover/hdr:text-white/78 transition-colors" x-text="group.label"></span>
                        <span class="text-[10px] text-white/20 bg-white/[0.04] px-1.5 py-0.5 rounded-full shrink-0" x-text="group.tasks.length"></span>
                        <div class="flex-1 h-px bg-white/[0.04]"></div>
                    </div>

                    <div x-show="!collapsedSections.includes('group-' + group.key)">
                        <template x-for="task in group.tasks" :key="task.id">
                            <div class="grid items-center px-5 py-0 hover:bg-white/[0.02] group/row cursor-pointer border-b border-white/[0.03] transition-colors"
                                 style="grid-template-columns: 20px 18px 1fr 115px 80px 80px 32px"
                                 :class="task.is_completed ? 'opacity-45' : ''"
                                 @click="openTask(task)">
                                <div class="flex items-center justify-center py-2.5" @click.stop>
                                    <input type="checkbox" :value="task.id" :checked="selectedTasks.includes(task.id)" @change="toggleSelectTask(task.id)" class="w-3.5 h-3.5 rounded accent-orange-500 cursor-pointer"/>
                                </div>
                                <div class="flex items-center justify-center py-2.5" @click.stop>
                                    <button @click="toggleComplete(task)" class="w-3.5 h-3.5 rounded-sm border flex items-center justify-center transition-all shrink-0"
                                        :class="task.is_completed ? 'bg-orange-500/30 border-orange-500/50' : 'border-white/15 hover:border-orange-400/50 opacity-0 group-hover/row:opacity-100'">
                                        <svg x-show="task.is_completed" class="w-2.5 h-2.5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                                <div class="flex items-center gap-1.5 min-w-0 py-2.5" @click.stop>
                                    <span x-show="task.priority && task.priority !== 'none'"
                                        class="w-1.5 h-1.5 rounded-full shrink-0"
                                        :class="{
                                            'bg-red-400': task.priority === 'critical',
                                            'bg-orange-400': task.priority === 'high',
                                            'bg-yellow-400': task.priority === 'medium',
                                            'bg-blue-400': task.priority === 'low',
                                        }"></span>
                                    <span class="text-[13px] truncate transition-colors cursor-pointer"
                                        :class="task.is_completed ? 'text-white/28 line-through' : 'text-white/75 group-hover/row:text-white/90'"
                                        x-text="task.title" @click="openTask(task)"></span>
                                    <span x-show="task.subtask_count > 0" class="shrink-0 text-[9px] text-white/22 bg-white/[0.05] px-1 py-0.5 rounded" x-text="task.subtask_count"></span>
                                </div>
                                <div class="flex items-center justify-center py-2" @click.stop>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-medium whitespace-nowrap"
                                        :style="'background:' + (task.status_color || '#94A3B8') + '15; color:' + (task.status_color || '#94A3B8')"
                                        x-text="task.status_name || 'Open'"></span>
                                </div>
                                <div class="flex items-center justify-center py-2">
                                    <template x-if="task.assignee">
                                        <div class="w-6 h-6 rounded-full bg-orange-500/15 text-orange-300 text-[9px] font-bold flex items-center justify-center ring-1 ring-orange-500/20"
                                            :title="task.assignee.name" x-text="task.assignee.name.slice(0,2).toUpperCase()"></div>
                                    </template>
                                </div>
                                <div class="flex items-center justify-center py-2">
                                    <template x-if="task.due_date">
                                        <span class="text-[10px] px-1.5 py-0.5 rounded-lg font-medium"
                                            :class="{'bg-red-500/15 text-red-400': task.due_date_status === 'overdue','bg-orange-500/15 text-orange-400': task.due_date_status === 'today' || task.due_date_status === 'soon','text-white/30': task.due_date_status === 'upcoming'}"
                                            x-text="task.due_date_fmt"></span>
                                    </template>
                                </div>
                                <div class="flex items-center justify-center py-2" @click.stop>
                                    <div class="relative opacity-0 group-hover/row:opacity-100 transition-opacity" x-data="{ open: false }">
                                        <button @click.stop="open = !open" class="w-6 h-6 flex items-center justify-center rounded hover:bg-white/[0.08] text-white/28 hover:text-white/65 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        </button>
                                        <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 top-full mt-0.5 z-30 w-36 bg-[#17172A] border border-white/[0.1] rounded-xl shadow-xl overflow-hidden py-1">
                                            <button @click.stop="openTask(task); open = false" class="w-full text-left px-3 py-1.5 text-[11px] text-white/55 hover:bg-white/[0.05] hover:text-white/85">Open</button>
                                            <button @click.stop="deleteTask(task); open = false" class="w-full text-left px-3 py-1.5 text-[11px] text-red-400/70 hover:bg-red-500/10 hover:text-red-400">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </template>

</div>

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

</div>

</x-layouts.smartprojects>

{{-- ====================================================================== --}}
{{-- Alpine.js: projectManager()                                            --}}
{{-- ====================================================================== --}}
<script>
function projectManager(data) {
    return {
        // ── Core data ─────────────────────────────────────────────────
        projectId:    data.projectId,
        canEdit:      data.canEdit,
        statuses:     data.statuses || [],
        customFields: data.customFields || [],
        savedViews:   [],
        activeView:   null,
        taskLists:    data.taskLists,
        members:      data.members,
        milestones:   data.milestones,
        labels:       data.labels,

        // ── Task detail (consumed by x-projects.task-detail) ──────────
        selectedTask: null,

        // ── Sidebar ───────────────────────────────────────────────────
        activeListId: null,
        collapsedSections: [],

        // ── Grouping & Filtering ──────────────────────────────────────
        groupBy:         'none',
        filterStatus:    '',
        filterPriority:  '',
        filterAssignee:  '',
        filterIssueType: '',
        filterLabel:     '',

        // ── Bulk ops ──────────────────────────────────────────────────
        selectedTasks: [],

        // ── Inline add ────────────────────────────────────────────────
        addingToList:    null,
        newTaskTitle:    '',
        addingSection:   false,
        newSectionName:  '',

        // ── Section rename ────────────────────────────────────────────
        editingListId:   null,
        editingListName: '',

        // ── Inline cell editing ───────────────────────────────────────
        editingTaskId:  null,
        editingField:   null,
        editingValue:   '',

        // ── Lifecycle ─────────────────────────────────────────────────
        init() {
            window.addEventListener('task-labels-updated', (e) => {
                const { taskId, labels } = e.detail;
                for (const tl of this.taskLists) {
                    const task = tl.tasks.find(t => t.id === taskId);
                    if (task) { task.labels = labels; break; }
                }
            });

            window.addEventListener('delete-task', (e) => {
                const taskId = e.detail?.taskId ?? e.detail;
                this._removeTask(taskId);
                this.selectedTask = null;
            });

            if (this.taskLists.length > 0) {
                this.activeListId = this.taskLists[0].id;
            }

            // Load saved views
            this.apiCall('GET', `/api/projects/${this.projectId}/saved-views`).then(res => {
                if (res?.views) this.savedViews = res.views;
            });
        },

        csrf() {
            return document.querySelector('meta[name=csrf-token]').content;
        },

        async apiCall(method, url, body = null) {
            try {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf(),
                    },
                    body: body ? JSON.stringify(body) : null,
                });
                if (!res.ok) return null;
                return await res.json();
            } catch (err) {
                console.error('API error:', err);
                return null;
            }
        },

        isTaskVisible(task) {
            if (this.filterStatus    && task.status      !== this.filterStatus)    return false;
            if (this.filterPriority  && task.priority    !== this.filterPriority)  return false;
            if (this.filterAssignee  && String(task.assignee_id) !== String(this.filterAssignee)) return false;
            if (this.filterIssueType && task.issue_type  !== this.filterIssueType) return false;
            if (this.filterLabel     && !(task.labels || []).some(l => l.id == this.filterLabel)) return false;
            return true;
        },

        clearFilters() {
            this.filterStatus = '';
            this.filterPriority = '';
            this.filterAssignee = '';
            this.filterIssueType = '';
            this.filterLabel = '';
        },

        loadView(view) {
            this.activeView = view;
            const f = view.filters || {};
            this.filterStatus    = f.status    || '';
            this.filterPriority  = f.priority  || '';
            this.filterAssignee  = f.assignee  || '';
            this.filterIssueType = f.issueType || '';
            this.filterLabel     = f.label     || '';
            if (view.group_by) this.groupBy = view.group_by;
        },

        clearView() {
            this.activeView = null;
            this.clearFilters();
            this.groupBy = 'none';
        },

        async saveCurrentView(name) {
            if (!name.trim()) return;
            const res = await this.apiCall('POST', `/api/projects/${this.projectId}/saved-views`, {
                name: name.trim(),
                filters: {
                    status:    this.filterStatus    || null,
                    priority:  this.filterPriority  || null,
                    assignee:  this.filterAssignee  || null,
                    issueType: this.filterIssueType || null,
                    label:     this.filterLabel     || null,
                },
                group_by:  this.groupBy !== 'none' ? this.groupBy : null,
                view_type: 'list',
            });
            if (res?.success && res.view) {
                this.savedViews.push(res.view);
                this.activeView = res.view;
            }
        },

        async deleteView(viewId) {
            await this.apiCall('DELETE', `/api/project-saved-views/${viewId}`);
            this.savedViews = this.savedViews.filter(v => v.id !== viewId);
            if (this.activeView?.id === viewId) this.activeView = null;
        },

        visibleTaskCount() {
            let count = 0;
            for (const tl of this.taskLists) {
                count += tl.tasks.filter(t => this.isTaskVisible(t)).length;
            }
            return count;
        },

        allVisibleTaskIds() {
            const ids = [];
            for (const tl of this.taskLists) {
                for (const t of tl.tasks) {
                    if (this.isTaskVisible(t)) ids.push(t.id);
                }
            }
            return ids;
        },

        groupedSections() {
            const allTasks = [];
            for (const tl of this.taskLists) {
                for (const t of tl.tasks) {
                    if (this.isTaskVisible(t)) allTasks.push(t);
                }
            }

            if (this.groupBy === 'none') return [];

            const groups = {};
            const orderedKeys = [];

            for (const task of allTasks) {
                let key, label;

                if (this.groupBy === 'status') {
                    key = task.project_status_id ? String(task.project_status_id) : (task.status || 'none');
                    label = task.status_name || task.status || 'Unknown';
                } else if (this.groupBy === 'priority') {
                    key = task.priority || 'none';
                    label = key.charAt(0).toUpperCase() + key.slice(1);
                } else if (this.groupBy === 'assignee') {
                    key = task.assignee ? String(task.assignee.id) : 'unassigned';
                    label = task.assignee ? task.assignee.name : 'Unassigned';
                } else if (this.groupBy === 'milestone') {
                    key = task.milestone_id ? String(task.milestone_id) : 'none';
                    const ms = this.milestones.find(m => m.id == task.milestone_id);
                    label = ms ? ms.name : 'No Milestone';
                }

                if (!groups[key]) {
                    groups[key] = { key, label, tasks: [] };
                    orderedKeys.push(key);
                }
                groups[key].tasks.push(task);
            }

            return orderedKeys.map(k => groups[k]);
        },

        toggleSection(id) {
            const idx = this.collapsedSections.indexOf(id);
            if (idx === -1) this.collapsedSections.push(id);
            else this.collapsedSections.splice(idx, 1);
        },

        async openTask(task) {
            const res = await this.apiCall('GET', `/api/project-tasks/${task.id}`);
            if (res && res.task) {
                this.selectedTask = res.task;
            } else if (res) {
                this.selectedTask = res;
            }
        },

        async updateTask(task, payload) {
            const res = await this.apiCall('PUT', `/api/project-tasks/${task.id}`, payload);
            if (res && res.success && res.task) {
                for (const tl of this.taskLists) {
                    const idx = tl.tasks.findIndex(t => t.id === task.id);
                    if (idx !== -1) {
                        Object.assign(tl.tasks[idx], res.task);
                        break;
                    }
                }
                if (this.selectedTask && this.selectedTask.id === task.id) {
                    Object.assign(this.selectedTask, res.task);
                }
            }
        },

        deleteTask(task) {
            this.$dispatch('confirm-modal', {
                title: 'Delete Task',
                message: `Delete "${task.title}"? This cannot be undone.`,
                confirmLabel: 'Delete',
                variant: 'danger',
                onConfirm: async () => {
                    const res = await this.apiCall('DELETE', `/api/project-tasks/${task.id}`);
                    if (res && res.success) {
                        this._removeTask(task.id);
                        if (this.selectedTask?.id === task.id) this.selectedTask = null;
                        this._toast('Task deleted');
                    }
                }
            });
        },

        _removeTask(taskId) {
            for (const tl of this.taskLists) {
                const idx = tl.tasks.findIndex(t => t.id === taskId);
                if (idx !== -1) { tl.tasks.splice(idx, 1); break; }
            }
            this.selectedTasks = this.selectedTasks.filter(id => id !== taskId);
        },

        async toggleComplete(task) {
            const newVal = !task.is_completed;
            task.is_completed = newVal;
            const res = await this.apiCall('PUT', `/api/project-tasks/${task.id}`, {
                is_completed: newVal,
            });
            if (res && res.success && res.task) {
                for (const tl of this.taskLists) {
                    const idx = tl.tasks.findIndex(t => t.id === task.id);
                    if (idx !== -1) { Object.assign(tl.tasks[idx], res.task); break; }
                }
                if (this.selectedTask?.id === task.id) Object.assign(this.selectedTask, res.task);
            }
        },

        async createTask(listId) {
            if (!this.newTaskTitle.trim()) return;
            const res = await this.apiCall('POST', `/api/projects/${this.projectId}/tasks`, {
                title:        this.newTaskTitle.trim(),
                task_list_id: listId,
            });
            if (res && res.success && res.task) {
                const tl = this.taskLists.find(t => t.id === listId);
                if (tl) tl.tasks.push({
                    ...res.task,
                    labels:          res.task.labels        ?? [],
                    subtask_count:   res.task.subtask_count  ?? 0,
                    due_date_fmt:    res.task.due_date_fmt   ?? null,
                    due_date_status: res.task.due_date_status ?? 'upcoming',
                });
                this.newTaskTitle = '';
                this.addingToList = null;
                this._toast('Task added');
            }
        },

        async createTaskList() {
            if (!this.newSectionName.trim()) return;
            const res = await this.apiCall('POST', `/api/projects/${this.projectId}/task-lists`, {
                name: this.newSectionName.trim(),
            });
            if (res && res.success && res.task_list) {
                this.taskLists.push({ id: res.task_list.id, name: res.task_list.name, tasks: [] });
                this.newSectionName = '';
                this.addingSection  = false;
                this._toast('Section added');
            }
        },

        deleteTaskList(tl) {
            this.$dispatch('confirm-modal', {
                title: 'Delete Section',
                message: `Delete section "${tl.name}" and all its tasks? This cannot be undone.`,
                confirmLabel: 'Delete',
                variant: 'danger',
                onConfirm: async () => {
                    const res = await this.apiCall('DELETE', `/api/projects/${this.projectId}/task-lists/${tl.id}`);
                    if (res && res.success) {
                        const idx = this.taskLists.findIndex(t => t.id === tl.id);
                        if (idx !== -1) this.taskLists.splice(idx, 1);
                        this._toast('Section deleted');
                    }
                }
            });
        },

        async saveListRename(tl) {
            if (!this.editingListName.trim() || this.editingListName === tl.name) {
                this.editingListId = null;
                return;
            }
            const res = await this.apiCall('PUT', `/api/projects/${this.projectId}/task-lists/${tl.id}`, {
                name: this.editingListName.trim(),
            });
            if (res && res.success) {
                tl.name = this.editingListName.trim();
            }
            this.editingListId   = null;
            this.editingListName = '';
        },

        startAddTask(taskListId) {
            if (!taskListId) return;
            this.addingToList = taskListId;
            this.$nextTick(() => this.$refs.newTaskInput?.focus());
        },

        startInlineEdit(task, field) {
            if (!this.canEdit) return;
            this.editingTaskId = task.id;
            this.editingField  = field;
            if (field === 'status') {
                this.editingValue = task.project_status_id ?? '';
            } else {
                this.editingValue = task[field] ?? '';
            }
        },

        async saveInlineEdit(task, overrideField = null, overrideValue = null) {
            const field = overrideField || this.editingField;
            const value = overrideValue !== null ? overrideValue : this.editingValue;
            this.cancelInlineEdit();
            if (String(task[field] ?? '') !== String(value ?? '')) {
                await this.updateTask(task, { [field]: value || null });
            }
        },

        cancelInlineEdit() {
            this.editingTaskId = null;
            this.editingField  = null;
            this.editingValue  = '';
        },

        selectAll(checked) {
            this.selectedTasks = checked ? this.allVisibleTaskIds() : [];
        },

        toggleSelectTask(id) {
            const idx = this.selectedTasks.indexOf(id);
            if (idx === -1) this.selectedTasks.push(id);
            else this.selectedTasks.splice(idx, 1);
        },

        async bulkUpdate(field, value) {
            const ids = [...this.selectedTasks];
            for (const id of ids) {
                let task = null;
                for (const tl of this.taskLists) {
                    task = tl.tasks.find(t => t.id === id);
                    if (task) break;
                }
                if (task) await this.updateTask(task, { [field]: value });
            }
            this.selectedTasks = [];
            this._toast(`Updated ${ids.length} task${ids.length !== 1 ? 's' : ''}`);
        },

        bulkDelete() {
            const count = this.selectedTasks.length;
            this.$dispatch('confirm-modal', {
                title: 'Delete Tasks',
                message: `Delete ${count} selected task${count !== 1 ? 's' : ''}? This cannot be undone.`,
                confirmLabel: 'Delete',
                variant: 'danger',
                onConfirm: async () => {
                    const ids = [...this.selectedTasks];
                    for (const id of ids) {
                        const res = await this.apiCall('DELETE', `/api/project-tasks/${id}`);
                        if (res && res.success) this._removeTask(id);
                    }
                    this.selectedTasks = [];
                    this._toast(`Deleted ${count} task${count !== 1 ? 's' : ''}`);
                }
            });
        },

        issueTypeIcon(type) {
            const icons = {
                task:  'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                bug:   'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0',
                story: 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z',
                epic:  'M13 10V3L4 14h7v7l9-11h-7z',
            };
            return icons[type] ?? icons.task;
        },

        issueTypeClass(type) {
            return { task: 'text-sky-400', bug: 'text-red-400', story: 'text-blue-400', epic: 'text-amber-400' }[type] ?? 'text-sky-400';
        },

        priorityClass(p) {
            return {
                critical: 'bg-red-500/20 text-red-400',
                high:     'bg-orange-500/20 text-orange-400',
                medium:   'bg-amber-500/20 text-amber-400',
                low:      'bg-blue-500/20 text-blue-400',
                none:     'bg-white/[0.06] text-white/30',
            }[p] ?? 'bg-white/[0.06] text-white/30';
        },

        _toast(msg) {
            try { Alpine.store('toast').success(msg); } catch (_) {}
        },
    };
}
</script>
