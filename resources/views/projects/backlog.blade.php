<x-layouts.smartprojects :project="$project" currentView="backlog" :canEdit="$canEdit">

@php
    $totalSprintTasks  = collect($sprints)->sum(fn($s) => count($s['tasks']));
    $backlogCount      = count($backlogTasks);
@endphp

<div
    class="flex flex-col h-[calc(100vh-176px)]"
    x-data="backlogManager({{ Js::from([
        'projectId'  => $project->slug,
        'canEdit'    => $canEdit,
        'sprints'    => $sprints,
        'backlog'    => $backlogTasks,
        'members'    => $project->members->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values(),
        'labels'     => $project->labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values(),
        'taskLists'  => $project->taskLists->map(fn($tl) => ['id' => $tl->id, 'name' => $tl->name])->values(),
    ]) }})"
    x-init="init()"
>

    {{-- ================================================================ --}}
    {{-- TOOLBAR                                                           --}}
    {{-- ================================================================ --}}
    <div class="shrink-0 flex items-center gap-3 px-5 py-2.5 border-b border-white/5 bg-neutral-950/80 backdrop-blur-sm flex-wrap">

        {{-- Sprint / Backlog counts --}}
        <span class="text-xs text-white/30">
            <span x-text="totalSprintTasks()"></span> in sprints
        </span>
        <div class="h-4 w-px bg-white/10"></div>
        <span class="text-xs text-white/30">
            <span x-text="filteredBacklog().length"></span> in backlog
        </span>

        <div class="flex-1"></div>

        {{-- Issue type filter pills --}}
        <div class="flex items-center gap-0.5 bg-white/5 rounded-lg p-0.5">
            <template x-for="type in ['all','task','bug','story','epic']" :key="type">
                <button
                    @click="filters.issueType = type"
                    :class="filters.issueType === type ? 'bg-white/15 text-white/80' : 'text-white/35 hover:text-white/60'"
                    class="px-2.5 py-1 rounded text-[11px] font-medium capitalize transition-colors"
                    x-text="type === 'all' ? 'All' : type.charAt(0).toUpperCase() + type.slice(1)"
                ></button>
            </template>
        </div>

        {{-- Assignee filter --}}
        <select
            x-model="filters.assignee"
            class="px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/50 text-[11px] focus:outline-none focus:ring-1 focus:ring-white/20"
        >
            <option value="">All Assignees</option>
            <template x-for="m in members" :key="m.id">
                <option :value="m.id" x-text="m.name"></option>
            </template>
        </select>

        {{-- Priority filter --}}
        <select
            x-model="filters.priority"
            class="px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/50 text-[11px] focus:outline-none focus:ring-1 focus:ring-white/20"
        >
            <option value="all">All Priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
            <option value="none">None</option>
        </select>

        @if($canEdit)
        <button
            @click="showCreateSprint = true"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium transition-colors border border-orange-500/20"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Sprint
        </button>
        @endif
    </div>

    {{-- ================================================================ --}}
    {{-- SCROLLABLE BODY                                                   --}}
    {{-- ================================================================ --}}
    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

        {{-- ── SPRINT SECTIONS ─────────────────────────────────────────── --}}
        <template x-for="sprint in sprints" :key="sprint.id">
            <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden">

                {{-- Sprint header --}}
                <div class="flex items-center gap-3 px-4 py-3 border-b border-white/5">

                    {{-- Expand/collapse --}}
                    <button
                        @click="toggleSprint(sprint.id)"
                        class="text-white/20 hover:text-white/50 transition-colors shrink-0"
                    >
                        <svg class="w-4 h-4 transition-transform" :class="isExpanded(sprint.id) ? '' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Sprint name + dates --}}
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <span class="text-sm font-semibold text-white/75" x-text="sprint.name"></span>
                        <span class="text-[10px] text-white/25" x-text="formatDateRange(sprint.start_date, sprint.end_date)"></span>
                        <span
                            class="text-[9px] px-1.5 py-0.5 rounded-full font-medium"
                            :class="{
                                'bg-white/10 text-white/35': sprint.status === 'planning',
                                'bg-blue-500/20 text-blue-400': sprint.status === 'active',
                                'bg-green-500/20 text-green-400': sprint.status === 'completed',
                            }"
                            x-text="sprint.status.charAt(0).toUpperCase() + sprint.status.slice(1)"
                        ></span>
                    </div>

                    {{-- Progress bar center --}}
                    <div class="flex items-center gap-2 w-40 shrink-0">
                        <div class="flex-1 h-1.5 bg-white/5 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-green-400/60 rounded-full transition-all"
                                :style="'width:' + sprintProgress(sprint) + '%'"
                            ></div>
                        </div>
                        <span class="text-[10px] text-white/30 shrink-0 font-mono">
                            <span x-text="sprintDone(sprint)"></span>/<span x-text="sprint.tasks.length"></span>
                        </span>
                    </div>

                    {{-- Sprint actions --}}
                    <div class="flex items-center gap-1 shrink-0">
                        <template x-if="sprint.status === 'planning'">
                            <button
                                @click="startSprint(sprint)"
                                class="px-2.5 py-1 rounded-lg bg-blue-500/15 text-blue-400 text-[11px] font-medium hover:bg-blue-500/25 transition-colors border border-blue-500/20"
                            >Start Sprint</button>
                        </template>
                        <template x-if="sprint.status === 'active'">
                            <button
                                @click="completeSprint(sprint)"
                                class="px-2.5 py-1 rounded-lg bg-green-500/15 text-green-400 text-[11px] font-medium hover:bg-green-500/25 transition-colors border border-green-500/20"
                            >Complete Sprint</button>
                        </template>

                        {{-- Kebab menu --}}
                        <div class="relative" x-data="{ openKebab: false }">
                            <button
                                @click="openKebab = !openKebab"
                                @click.outside="openKebab = false"
                                class="p-1.5 rounded-lg text-white/20 hover:text-white/50 hover:bg-white/5 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/>
                                </svg>
                            </button>
                            <div
                                x-show="openKebab"
                                x-cloak
                                class="absolute right-0 top-full mt-1 w-36 bg-neutral-900 border border-white/10 rounded-xl shadow-xl z-20 py-1 overflow-hidden"
                            >
                                <button
                                    @click="openEditSprint(sprint); openKebab = false"
                                    class="w-full text-left px-3 py-2 text-xs text-white/60 hover:bg-white/5 hover:text-white/80 transition-colors flex items-center gap-2"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </button>
                                <button
                                    @click="deleteSprint(sprint.id); openKebab = false"
                                    class="w-full text-left px-3 py-2 text-xs text-red-400/70 hover:bg-red-500/10 hover:text-red-400 transition-colors flex items-center gap-2"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sprint task list --}}
                <div x-show="isExpanded(sprint.id)">
                    <template x-if="sprint.tasks.length === 0">
                        <div class="px-4 py-5 text-center text-xs text-white/25 italic">
                            No tasks. Drag tasks from backlog or add new ones.
                        </div>
                    </template>

                    <template x-for="task in filteredSprintTasks(sprint)" :key="task.id">
                        <div class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/[0.02] group border-b border-white/[0.03] last:border-0">
                            {{-- Issue type icon --}}
                            <span class="shrink-0 w-3.5 h-3.5" x-html="issueTypeIcon(task.issue_type)"></span>

                            {{-- Checkbox --}}
                            <input type="checkbox" :checked="task.is_completed" class="w-3.5 h-3.5 shrink-0 accent-orange-500 cursor-pointer rounded" @click.prevent>

                            {{-- Title --}}
                            <span class="flex-1 text-xs text-white/65 truncate" :class="task.is_completed ? 'line-through text-white/25' : ''" x-text="task.title"></span>

                            {{-- Status badge --}}
                            <span
                                class="text-[9px] px-1.5 py-0.5 rounded-full font-medium shrink-0"
                                :class="statusClass(task.status)"
                                x-text="task.status ? task.status.replace('_',' ') : ''"
                            ></span>

                            {{-- Assignee avatar --}}
                            <template x-if="task.assignee_id">
                                <div
                                    class="w-5 h-5 rounded-full bg-white/10 text-white/40 text-[8px] font-bold flex items-center justify-center shrink-0"
                                    :title="memberName(task.assignee_id)"
                                    x-text="memberInitials(task.assignee_id)"
                                ></div>
                            </template>

                            {{-- Priority badge --}}
                            <span
                                class="text-[9px] px-1.5 py-0.5 rounded-full font-medium shrink-0"
                                :class="priorityClass(task.priority)"
                                x-text="task.priority && task.priority !== 'none' ? task.priority : ''"
                                x-show="task.priority && task.priority !== 'none'"
                            ></span>

                            {{-- Story points --}}
                            <template x-if="task.story_points !== null && task.story_points !== undefined">
                                <span class="text-[10px] text-white/35 bg-white/8 px-1.5 py-0.5 rounded font-mono shrink-0" x-text="task.story_points"></span>
                            </template>

                            {{-- Due date --}}
                            <template x-if="task.due_date">
                                <span
                                    class="text-[10px] shrink-0"
                                    :class="isDuePast(task.due_date) && !task.is_completed ? 'text-red-400/70' : 'text-white/25'"
                                    x-text="formatShortDate(task.due_date)"
                                ></span>
                            </template>

                            {{-- Remove from sprint --}}
                            <button
                                @click="removeFromSprint(task.id, sprint.id)"
                                class="opacity-0 group-hover:opacity-100 text-white/15 hover:text-white/40 transition-all shrink-0 p-0.5"
                                title="Move to backlog"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>

                    {{-- Quick add task to sprint --}}
                    <div class="px-4 py-2 border-t border-white/[0.03]" x-data="{ adding: false, title: '' }">
                        <template x-if="!adding">
                            <button
                                @click="adding = true"
                                class="text-[11px] text-white/20 hover:text-white/50 transition-colors flex items-center gap-1.5"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add Task
                            </button>
                        </template>
                        <template x-if="adding">
                            <div class="flex items-center gap-2">
                                <input
                                    x-model="title"
                                    type="text"
                                    placeholder="Task title…"
                                    class="flex-1 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/20"
                                    @keydown.enter="$dispatch('quick-add-sprint', { sprintId: sprint.id, title: title }); adding = false; title = ''"
                                    @keydown.escape="adding = false; title = ''"
                                    x-init="$nextTick(() => $el.focus())"
                                />
                                <button
                                    @click="$dispatch('quick-add-sprint', { sprintId: sprint.id, title: title }); adding = false; title = ''"
                                    class="px-2.5 py-1.5 rounded-lg bg-orange-500/15 text-orange-400 text-xs hover:bg-orange-500/25 transition-colors"
                                >Add</button>
                                <button @click="adding = false; title = ''" class="text-white/20 hover:text-white/40 transition-colors p-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </template>

        {{-- ── BACKLOG SECTION ──────────────────────────────────────────── --}}
        <div class="bg-white/[0.02] border border-white/5 rounded-2xl overflow-hidden">

            {{-- Backlog header --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-white/5">
                <button
                    @click="collapsedBacklog = !collapsedBacklog"
                    class="text-white/20 hover:text-white/50 transition-colors shrink-0"
                >
                    <svg class="w-4 h-4 transition-transform" :class="collapsedBacklog ? '-rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <span class="text-sm font-semibold text-white/75">Backlog</span>
                <span class="text-[10px] text-white/25 bg-white/5 px-1.5 py-0.5 rounded-full font-mono" x-text="filteredBacklog().length"></span>
                <div class="flex-1"></div>
                <span class="text-[10px] text-white/20">Sorted by priority</span>
            </div>

            {{-- Backlog task rows --}}
            <div x-show="!collapsedBacklog">
                <template x-if="filteredBacklog().length === 0">
                    <div class="px-4 py-6 text-center text-xs text-white/20 italic">No tasks in backlog.</div>
                </template>

                <template x-for="task in filteredBacklog()" :key="task.id">
                    <div class="flex items-center gap-2.5 px-4 py-2 hover:bg-white/[0.02] group border-b border-white/[0.03] last:border-0">

                        {{-- Issue type icon --}}
                        <span class="shrink-0 w-3.5 h-3.5" x-html="issueTypeIcon(task.issue_type)"></span>

                        {{-- Checkbox --}}
                        <input type="checkbox" :checked="task.is_completed" class="w-3.5 h-3.5 shrink-0 accent-orange-500 cursor-pointer rounded" @click.prevent>

                        {{-- Title --}}
                        <span class="flex-1 text-xs text-white/65 truncate" :class="task.is_completed ? 'line-through text-white/25' : ''" x-text="task.title"></span>

                        {{-- Status badge --}}
                        <span
                            class="text-[9px] px-1.5 py-0.5 rounded-full font-medium shrink-0"
                            :class="statusClass(task.status)"
                            x-text="task.status ? task.status.replace(/_/g,' ') : ''"
                        ></span>

                        {{-- Assignee avatar --}}
                        <template x-if="task.assignee_id">
                            <div
                                class="w-5 h-5 rounded-full bg-white/10 text-white/40 text-[8px] font-bold flex items-center justify-center shrink-0"
                                :title="memberName(task.assignee_id)"
                                x-text="memberInitials(task.assignee_id)"
                            ></div>
                        </template>

                        {{-- Priority badge --}}
                        <span
                            class="text-[9px] px-1.5 py-0.5 rounded-full font-medium shrink-0"
                            :class="priorityClass(task.priority)"
                            x-text="task.priority && task.priority !== 'none' ? task.priority : ''"
                            x-show="task.priority && task.priority !== 'none'"
                        ></span>

                        {{-- Story points --}}
                        <template x-if="task.story_points !== null && task.story_points !== undefined">
                            <span class="text-[10px] text-white/35 bg-white/8 px-1.5 py-0.5 rounded font-mono shrink-0" x-text="task.story_points"></span>
                        </template>

                        {{-- Due date --}}
                        <template x-if="task.due_date">
                            <span
                                class="text-[10px] shrink-0"
                                :class="isDuePast(task.due_date) && !task.is_completed ? 'text-red-400/70' : 'text-white/25'"
                                x-text="formatShortDate(task.due_date)"
                            ></span>
                        </template>

                        {{-- Sprint assignment dropdown --}}
                        <select
                            @change="handleSprintAssign(task.id, $event.target.value)"
                            class="text-[10px] rounded-lg bg-white/5 border border-white/10 text-white/40 py-0.5 px-1.5 focus:outline-none focus:ring-1 focus:ring-white/20 max-w-28 shrink-0"
                        >
                            <option value="">Backlog</option>
                            <template x-for="s in sprints.filter(sp => sp.status !== 'completed')" :key="s.id">
                                <option :value="s.id" x-text="s.name"></option>
                            </template>
                        </select>

                    </div>
                </template>

                {{-- Quick add to backlog --}}
                <div class="px-4 py-2 border-t border-white/[0.03]" x-data="{ adding: false, title: '' }">
                    <template x-if="!adding">
                        <button
                            @click="adding = true"
                            class="text-[11px] text-white/20 hover:text-white/50 transition-colors flex items-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Task
                        </button>
                    </template>
                    <template x-if="adding">
                        <div class="flex items-center gap-2">
                            <input
                                x-model="title"
                                type="text"
                                placeholder="Task title…"
                                class="flex-1 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/20"
                                @keydown.enter="$dispatch('quick-add-backlog', { title: title }); adding = false; title = ''"
                                @keydown.escape="adding = false; title = ''"
                                x-init="$nextTick(() => $el.focus())"
                            />
                            <button
                                @click="$dispatch('quick-add-backlog', { title: title }); adding = false; title = ''"
                                class="px-2.5 py-1.5 rounded-lg bg-orange-500/15 text-orange-400 text-xs hover:bg-orange-500/25 transition-colors"
                            >Add</button>
                            <button @click="adding = false; title = ''" class="text-white/20 hover:text-white/40 transition-colors p-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>{{-- end scrollable body --}}

    {{-- ================================================================ --}}
    {{-- CREATE SPRINT MODAL                                              --}}
    {{-- ================================================================ --}}
    <div
        x-show="showCreateSprint"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="showCreateSprint = false"
        @keydown.escape.window="showCreateSprint = false"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="relative bg-neutral-900 border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-white/80">Create Sprint</h2>
                <button @click="showCreateSprint = false" class="text-white/20 hover:text-white/50 transition-colors p-1 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs text-white/40 mb-1.5">Sprint name <span class="text-red-400">*</span></label>
                    <input
                        x-model="sprintForm.name"
                        type="text"
                        placeholder="e.g. Sprint 1"
                        class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white/80 text-sm focus:ring-1 focus:ring-orange-500/50 focus:outline-none placeholder-white/20"
                        @keydown.enter="createSprint()"
                    />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-white/40 mb-1.5">Start date</label>
                        <input
                            x-model="sprintForm.start_date"
                            type="date"
                            class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white/70 text-sm focus:ring-1 focus:ring-orange-500/50 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label class="block text-xs text-white/40 mb-1.5">End date</label>
                        <input
                            x-model="sprintForm.end_date"
                            type="date"
                            class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white/70 text-sm focus:ring-1 focus:ring-orange-500/50 focus:outline-none"
                        />
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showCreateSprint = false" class="flex-1 py-2.5 rounded-xl border border-white/10 text-white/40 text-sm hover:border-white/20 transition-colors">Cancel</button>
                    <button type="button" @click="createSprint()" class="flex-1 py-2.5 rounded-xl bg-orange-500/20 text-orange-400 border border-orange-500/20 text-sm font-medium hover:bg-orange-500/30 transition-colors">Create Sprint</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- EDIT SPRINT MODAL                                                --}}
    {{-- ================================================================ --}}
    <div
        x-show="editingSprintId !== null"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="editingSprintId = null"
        @keydown.escape.window="editingSprintId = null"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <template x-if="editingSprintId !== null">
            <div class="relative bg-neutral-900 border border-white/10 rounded-2xl w-full max-w-md p-6 shadow-2xl" @click.stop>
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-sm font-semibold text-white/80">Edit Sprint</h2>
                    <button @click="editingSprintId = null" class="text-white/20 hover:text-white/50 transition-colors p-1 rounded">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-white/40 mb-1.5">Sprint name</label>
                        <input
                            x-model="editingSprintData.name"
                            type="text"
                            class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white/80 text-sm focus:ring-1 focus:ring-orange-500/50 focus:outline-none"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-white/40 mb-1.5">Start date</label>
                            <input x-model="editingSprintData.start_date" type="date" class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white/70 text-sm focus:ring-1 focus:ring-orange-500/50 focus:outline-none"/>
                        </div>
                        <div>
                            <label class="block text-xs text-white/40 mb-1.5">End date</label>
                            <input x-model="editingSprintData.end_date" type="date" class="w-full px-3 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white/70 text-sm focus:ring-1 focus:ring-orange-500/50 focus:outline-none"/>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="editingSprintId = null" class="flex-1 py-2.5 rounded-xl border border-white/10 text-white/40 text-sm hover:border-white/20 transition-colors">Cancel</button>
                        <button type="button" @click="saveEditSprint()" class="flex-1 py-2.5 rounded-xl bg-orange-500/20 text-orange-400 border border-orange-500/20 text-sm font-medium hover:bg-orange-500/30 transition-colors">Save</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

</div>{{-- end x-data wrapper --}}

</x-layouts.smartprojects>

<script>
function backlogManager(config) {
    return {
        projectId:        config.projectId,
        canEdit:          config.canEdit,
        sprints:          config.sprints,
        backlogTasks:     config.backlog,
        members:          config.members,
        labels:           config.labels,
        taskLists:        config.taskLists,

        filters: { issueType: 'all', assignee: '', priority: 'all' },
        expandedSprints:   new Set(),
        collapsedBacklog:  false,

        showCreateSprint:  false,
        sprintForm:        { name: '', start_date: '', end_date: '' },

        editingSprintId:   null,
        editingSprintData: { name: '', start_date: '', end_date: '' },

        init() {
            // Expand all sprints that are not completed by default
            this.sprints.forEach(s => {
                if (s.status !== 'completed') this.expandedSprints.add(s.id);
            });
            // Re-wrap as reactive array (Alpine needs reactivity)
            this.expandedSprints = new Set(this.expandedSprints);

            window.addEventListener('quick-add-sprint', (e) => {
                const { sprintId, title } = e.detail;
                if (title && title.trim()) this.quickAddTask(null, 'open', title.trim(), sprintId);
            });
            window.addEventListener('quick-add-backlog', (e) => {
                const { title } = e.detail;
                if (title && title.trim()) this.quickAddTask(this.taskLists[0]?.id ?? null, 'open', title.trim(), null);
            });
        },

        // ── Helpers ────────────────────────────────────────────────────

        isExpanded(sprintId) {
            return this.expandedSprints.has(sprintId);
        },

        toggleSprint(sprintId) {
            if (this.expandedSprints.has(sprintId)) {
                this.expandedSprints.delete(sprintId);
            } else {
                this.expandedSprints.add(sprintId);
            }
            // Force reactivity
            this.expandedSprints = new Set(this.expandedSprints);
        },

        totalSprintTasks() {
            return this.sprints.reduce((sum, s) => sum + s.tasks.length, 0);
        },

        sprintDone(sprint) {
            return sprint.tasks.filter(t => t.is_completed).length;
        },

        sprintProgress(sprint) {
            if (!sprint.tasks.length) return 0;
            return Math.round((this.sprintDone(sprint) / sprint.tasks.length) * 100);
        },

        filteredSprintTasks(sprint) {
            return sprint.tasks.filter(t => this.isTaskVisible(t));
        },

        filteredBacklog() {
            const priorityOrder = { critical: 0, high: 1, medium: 2, low: 3, none: 4 };
            return this.backlogTasks
                .filter(t => this.isTaskVisible(t))
                .sort((a, b) => (priorityOrder[a.priority] ?? 4) - (priorityOrder[b.priority] ?? 4));
        },

        isTaskVisible(task) {
            if (this.filters.issueType !== 'all' && task.issue_type !== this.filters.issueType) return false;
            if (this.filters.assignee && String(task.assignee_id) !== String(this.filters.assignee)) return false;
            if (this.filters.priority !== 'all' && task.priority !== this.filters.priority) return false;
            return true;
        },

        memberName(id) {
            const m = this.members.find(m => String(m.id) === String(id));
            return m ? m.name : '';
        },

        memberInitials(id) {
            const name = this.memberName(id);
            return name ? name.slice(0, 2).toUpperCase() : '??';
        },

        formatDateRange(start, end) {
            const fmt = (d) => {
                if (!d) return null;
                const dt = new Date(d);
                return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            };
            const s = fmt(start), e = fmt(end);
            if (s && e) return `${s} – ${e}`;
            if (s) return `From ${s}`;
            if (e) return `Until ${e}`;
            return '';
        },

        formatShortDate(d) {
            if (!d) return '';
            return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        isDuePast(d) {
            if (!d) return false;
            return new Date(d) < new Date();
        },

        issueTypeIcon(type) {
            const icons = {
                task:  `<svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>`,
                bug:   `<svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
                story: `<svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>`,
                epic:  `<svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>`,
            };
            return icons[type] || icons.task;
        },

        priorityClass(p) {
            const map = {
                critical: 'bg-red-500/20 text-red-400',
                high:     'bg-orange-500/20 text-orange-400',
                medium:   'bg-orange-500/20 text-orange-400',
                low:      'bg-blue-500/20 text-blue-400',
                none:     'bg-white/5 text-white/25',
            };
            return map[p] || 'bg-white/5 text-white/25';
        },

        statusClass(s) {
            const map = {
                open:        'bg-white/10 text-white/40',
                in_progress: 'bg-blue-500/20 text-blue-400',
                completed:   'bg-green-500/20 text-green-400',
                deferred:    'bg-white/5 text-white/25',
                on_hold:     'bg-orange-500/20 text-orange-400',
            };
            return map[s] || 'bg-white/10 text-white/35';
        },

        // ── Sprint assignment dropdown handler ─────────────────────────

        handleSprintAssign(taskId, sprintId) {
            if (!sprintId) return; // "Backlog" option keeps task in backlog
            this.moveToSprint(taskId, sprintId);
        },

        // ── API Methods ────────────────────────────────────────────────

        async moveToSprint(taskId, sprintId) {
            const task = this.backlogTasks.find(t => t.id === taskId);
            if (!task) return;
            const result = await this.apiCall('POST', `/api/project-sprints/${sprintId}/tasks`, { task_id: taskId });
            if (result !== null) {
                this.backlogTasks = this.backlogTasks.filter(t => t.id !== taskId);
                const sprint = this.sprints.find(s => s.id == sprintId);
                if (sprint) sprint.tasks.push({ ...task });
            }
        },

        async removeFromSprint(taskId, sprintId) {
            const sprint = this.sprints.find(s => s.id == sprintId);
            if (!sprint) return;
            const task = sprint.tasks.find(t => t.id === taskId);
            if (!task) return;
            const result = await this.apiCall('DELETE', `/api/project-sprints/${sprintId}/tasks/${taskId}`);
            if (result !== null) {
                sprint.tasks = sprint.tasks.filter(t => t.id !== taskId);
                this.backlogTasks.push({ ...task });
            }
        },

        async createSprint() {
            if (!this.sprintForm.name.trim()) return;
            const result = await this.apiCall('POST', `/api/projects/${this.projectId}/sprints`, this.sprintForm);
            if (result) {
                this.sprints.push({ ...result, tasks: [] });
                this.expandedSprints.add(result.id);
                this.expandedSprints = new Set(this.expandedSprints);
                this.sprintForm = { name: '', start_date: '', end_date: '' };
                this.showCreateSprint = false;
            }
        },

        async startSprint(sprint) {
            const result = await this.apiCall('PUT', `/api/project-sprints/${sprint.id}`, { status: 'active' });
            if (result) {
                const idx = this.sprints.findIndex(s => s.id === sprint.id);
                if (idx !== -1) this.sprints[idx].status = 'active';
            }
        },

        completeSprint(sprint) {
            this.$dispatch('confirm-modal', {
                title: 'Complete Sprint',
                message: `Complete "${sprint.name}"? Incomplete tasks will be moved to backlog.`,
                confirmLabel: 'Complete',
                variant: 'warning',
                onConfirm: async () => {
                    const result = await this.apiCall('POST', `/api/project-sprints/${sprint.id}/complete`);
                    if (result) {
                        const idx = this.sprints.findIndex(s => s.id === sprint.id);
                        if (idx !== -1) {
                            const incomplete = this.sprints[idx].tasks.filter(t => !t.is_completed);
                            incomplete.forEach(t => this.backlogTasks.push(t));
                            this.sprints[idx].status = 'completed';
                            this.sprints[idx].tasks  = this.sprints[idx].tasks.filter(t => t.is_completed);
                        }
                    }
                }
            });
        },

        deleteSprint(sprintId) {
            this.$dispatch('confirm-modal', {
                title: 'Delete Sprint',
                message: 'Delete this sprint? All tasks will be moved to backlog.',
                confirmLabel: 'Delete',
                variant: 'danger',
                onConfirm: async () => {
                    const sprint = this.sprints.find(s => s.id === sprintId);
                    const result = await this.apiCall('DELETE', `/api/project-sprints/${sprintId}`);
                    if (result !== null) {
                        if (sprint) sprint.tasks.forEach(t => this.backlogTasks.push(t));
                        this.sprints = this.sprints.filter(s => s.id !== sprintId);
                    }
                }
            });
        },

        openEditSprint(sprint) {
            this.editingSprintId   = sprint.id;
            this.editingSprintData = { name: sprint.name, start_date: sprint.start_date ?? '', end_date: sprint.end_date ?? '' };
        },

        async saveEditSprint() {
            if (!this.editingSprintId) return;
            const result = await this.apiCall('PUT', `/api/project-sprints/${this.editingSprintId}`, this.editingSprintData);
            if (result) {
                const idx = this.sprints.findIndex(s => s.id === this.editingSprintId);
                if (idx !== -1) {
                    this.sprints[idx].name       = this.editingSprintData.name;
                    this.sprints[idx].start_date = this.editingSprintData.start_date;
                    this.sprints[idx].end_date   = this.editingSprintData.end_date;
                }
                this.editingSprintId = null;
            }
        },

        async quickAddTask(taskListId, status, title, sprintId) {
            if (!title) return;
            const result = await this.apiCall('POST', `/api/projects/${this.projectId}/tasks`, {
                title,
                status,
                task_list_id: taskListId,
            });
            if (result) {
                const newTask = {
                    id:            result.id,
                    title:         result.title,
                    status:        result.status ?? status,
                    priority:      result.priority ?? 'none',
                    issue_type:    result.issue_type ?? 'task',
                    story_points:  result.story_points ?? null,
                    due_date:      result.due_date ?? null,
                    assignee_id:   result.assignee_id ?? null,
                    is_completed:  result.is_completed ?? false,
                    task_list_id:  taskListId,
                };
                if (sprintId) {
                    // Add to sprint
                    const sprint = this.sprints.find(s => s.id == sprintId);
                    if (sprint) {
                        // Also associate task with sprint via API
                        await this.apiCall('POST', `/api/project-sprints/${sprintId}/tasks`, { task_id: result.id });
                        sprint.tasks.push(newTask);
                    }
                } else {
                    this.backlogTasks.push(newTask);
                }
            }
        },

        async apiCall(method, url, data = null) {
            try {
                const opts = {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept':       'application/json',
                    },
                };
                if (data && method !== 'GET') opts.body = JSON.stringify(data);
                const r = await fetch(url, opts);
                if (method === 'DELETE') return r.ok ? {} : null;
                if (!r.ok) return null;
                return await r.json();
            } catch (e) {
                console.error('API error:', e);
                return null;
            }
        },
    };
}
</script>
