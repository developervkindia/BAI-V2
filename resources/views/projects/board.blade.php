<x-layouts.smartprojects :project="$project" currentView="board" :canEdit="$canEdit">

<div
    class="flex flex-col h-[calc(100vh-176px)]"
    x-data="projectBoard({{ Js::from([
        'projectId' => $project->slug,
        'canEdit'   => $canEdit,
        'tasks'     => $tasks,
        'statuses'  => $project->statuses->map(fn($s) => [
            'id' => $s->id, 'name' => $s->name, 'slug' => $s->slug, 'color' => $s->color,
            'is_completed_state' => $s->is_completed_state, 'is_default' => $s->is_default,
        ])->values(),
        'customFields' => $project->customFields->map(fn($f) => [
            'id' => $f->id, 'name' => $f->name, 'type' => $f->type,
            'options' => $f->options, 'is_required' => $f->is_required,
        ])->values(),
        'taskLists' => $project->taskLists->map(fn($tl) => ['id' => $tl->id, 'name' => $tl->name])->values(),
        'members'   => $project->members->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values(),
        'labels'    => $project->labels->map(fn($l) => ['id' => $l->id, 'name' => $l->name, 'color' => $l->color])->values(),
    ]) }})"
    x-init="init()"
>

    {{-- ================================================================ --}}
    {{-- TOOLBAR                                                           --}}
    {{-- ================================================================ --}}
    <div class="shrink-0 flex items-center gap-3 px-5 py-2.5 border-b border-white/5 bg-neutral-950/80 backdrop-blur-sm flex-wrap">

        {{-- Task count --}}
        <span class="text-xs text-white/30">
            <span x-text="tasks.filter(t => !t.parent_task_id).length"></span> tasks
        </span>

        <div class="h-4 w-px bg-white/10"></div>

        {{-- Swimlane toggle --}}
        <div class="flex items-center gap-1.5">
            <span class="text-[11px] text-white/35">Swimlanes:</span>
            <div class="flex rounded-lg overflow-hidden border border-white/10 bg-white/5">
                <button
                    @click="swimlaneMode = 'off'"
                    class="px-2.5 py-1 text-[11px] font-medium transition-colors"
                    :class="swimlaneMode === 'off' ? 'bg-white/15 text-white/80' : 'text-white/35 hover:text-white/60'"
                >Off</button>
                <button
                    @click="swimlaneMode = 'assignee'"
                    class="px-2.5 py-1 text-[11px] font-medium transition-colors"
                    :class="swimlaneMode === 'assignee' ? 'bg-white/15 text-white/80' : 'text-white/35 hover:text-white/60'"
                >By Assignee</button>
            </div>
        </div>

        <div class="flex-1"></div>

        {{-- New issue button --}}
        <button
            x-show="canEdit"
            @click="showCreateModal = true"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 text-xs font-medium transition-colors border border-orange-500/20"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Issue
        </button>
    </div>

    {{-- ================================================================ --}}
    {{-- BOARD (scrollable)                                                --}}
    {{-- ================================================================ --}}
    <div class="flex-1 overflow-auto">

        {{-- ── Standard mode (swimlaneMode = 'off') ─────────────────── --}}
        <template x-if="swimlaneMode === 'off'">
            <div class="flex gap-3 p-5" style="min-height: calc(100vh - 160px);">
                <template x-for="col in columns" :key="col.status">
                    <div class="flex-shrink-0 w-72 flex flex-col">

                        {{-- Column header --}}
                        <div class="flex items-center gap-2 mb-3 px-1">
                            <span class="w-2 h-2 rounded-full shrink-0" :style="'background:' + (col.color || '#94A3B8')"></span>
                            <span class="text-xs font-semibold text-white/60" x-text="col.label"></span>
                            <span class="ml-auto text-[10px] text-white/25 bg-white/5 px-1.5 py-0.5 rounded-full font-mono" x-text="tasksInCol(col.status).length"></span>
                        </div>

                        {{-- Drop zone --}}
                        <div
                            class="flex-1 rounded-xl p-2 space-y-2 transition-all duration-150"
                            :class="dragOverCol === col.status && dragOverSwimlane === null
                                ? 'bg-white/[0.05] ring-1 ring-orange-500/25'
                                : 'bg-white/[0.02]'"
                            @dragover.prevent="dragOverCol = col.status; dragOverSwimlane = null"
                            @dragleave.self="dragOverCol = null; dragOverSwimlane = null"
                            @drop.prevent="dropTask(col.status, null)"
                        >
                            <template x-for="task in tasksInCol(col.status)" :key="task.id">
                                <div
                                    class="group/card relative bg-neutral-900 hover:bg-neutral-800/80 border border-white/[0.07] hover:border-white/[0.14] rounded-xl p-3 cursor-pointer transition-all duration-100 select-none"
                                    :class="dragTaskId === task.id ? 'opacity-40 scale-95' : 'hover:shadow-lg hover:shadow-black/30'"
                                    draggable="true"
                                    @dragstart="startDrag(task)"
                                    @dragend="dragTaskId = null; dragOverCol = null; dragOverSwimlane = null"
                                    @click="openTask(task)"
                                >
                                    {{-- Card top row: issue type + priority --}}
                                    <div class="flex items-start justify-between gap-1 mb-2">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="w-3 h-3 shrink-0" :class="issueTypeClass(task.issue_type)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="issueTypeIcon(task.issue_type)"/>
                                            </svg>
                                        </div>
                                        <span
                                            x-show="task.priority && task.priority !== 'none'"
                                            class="shrink-0 text-[9px] px-1.5 py-0.5 rounded-full font-medium capitalize"
                                            :class="priorityClass(task.priority)"
                                            x-text="task.priority"
                                        ></span>
                                    </div>

                                    {{-- Title --}}
                                    <p
                                        class="text-xs text-white/75 group-hover/card:text-white/90 leading-snug mb-2 line-clamp-2 transition-colors"
                                        :class="task.is_completed ? 'line-through text-white/30' : ''"
                                        x-text="task.title"
                                    ></p>

                                    {{-- Task list name --}}
                                    <div x-show="task.task_list" class="text-[10px] text-white/22 mb-2 truncate" x-text="task.task_list?.name ?? ''"></div>

                                    {{-- Labels --}}
                                    <div x-show="(task.labels || []).length > 0" class="flex items-center gap-1 mb-2.5 flex-wrap">
                                        <template x-for="(lbl, i) in (task.labels || []).slice(0, 3)" :key="lbl.id">
                                            <span class="inline-flex items-center gap-1 text-[9px] px-1.5 py-0.5 rounded-full border border-white/8 text-white/45">
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0" :style="'background:' + lbl.color"></span>
                                                <span x-text="lbl.name"></span>
                                            </span>
                                        </template>
                                        <span
                                            x-show="(task.labels || []).length > 3"
                                            class="text-[9px] text-white/30"
                                            x-text="'+' + ((task.labels || []).length - 3)"
                                        ></span>
                                    </div>

                                    {{-- Card footer --}}
                                    <div class="flex items-center gap-1.5 mt-1">
                                        {{-- Assignee avatar --}}
                                        <template x-if="task.assignee">
                                            <div
                                                class="w-5 h-5 rounded-full bg-white/10 text-white/40 text-[8px] font-bold flex items-center justify-center shrink-0 ring-1 ring-white/10"
                                                :title="task.assignee.name"
                                                x-text="task.assignee.name.slice(0,2).toUpperCase()"
                                            ></div>
                                        </template>
                                        <template x-if="!task.assignee">
                                            <div class="w-5 h-5 rounded-full border border-dashed border-white/10 shrink-0"></div>
                                        </template>

                                        <div class="flex-1"></div>

                                        {{-- Story points --}}
                                        <template x-if="task.story_points">
                                            <span class="text-[9px] text-white/30 bg-white/5 border border-white/8 px-1.5 py-0.5 rounded font-mono" x-text="task.story_points"></span>
                                        </template>

                                        {{-- Due date --}}
                                        <template x-if="task.due_date">
                                            <span
                                                class="text-[9px] font-medium"
                                                :class="dueDateClass(task)"
                                                x-text="formatDate(task.due_date)"
                                            ></span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Quick add inline --}}
                            <div x-show="addingToCol === col.status && addingToSwimlane === null" x-cloak class="mt-1">
                                <div class="bg-neutral-900 border border-orange-500/25 rounded-xl p-2">
                                    <input
                                        :x-ref="'colInput_' + col.status"
                                        type="text"
                                        placeholder="Issue title…"
                                        class="w-full bg-transparent text-xs text-white/80 placeholder-white/25 focus:outline-none py-1 px-1"
                                        @keydown.enter="quickAddTask(col.status, null, $event)"
                                        @keydown.escape="addingToCol = null; addingToSwimlane = null"
                                        @blur="addingToCol = null; addingToSwimlane = null"
                                        x-effect="if(addingToCol === col.status && addingToSwimlane === null) $nextTick(() => $el.focus())"
                                    />
                                </div>
                            </div>

                            {{-- Empty state --}}
                            <div
                                x-show="tasksInCol(col.status).length === 0 && addingToCol !== col.status"
                                class="flex flex-col items-center justify-center h-24 text-[10px] text-white/15 border border-dashed border-white/[0.06] rounded-xl gap-1.5"
                            >
                                <svg class="w-5 h-5 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                No issues
                            </div>
                        </div>

                        {{-- Add to column button --}}
                        <button
                            x-show="canEdit"
                            @click="addingToCol = col.status; addingToSwimlane = null"
                            class="mt-2 w-full py-1.5 rounded-lg text-[10px] text-white/25 hover:text-white/55 hover:bg-white/[0.04] transition-colors flex items-center justify-center gap-1"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add issue
                        </button>
                    </div>
                </template>
            </div>
        </template>

        {{-- ── Swimlane mode (swimlaneMode = 'assignee') ───────────── --}}
        <template x-if="swimlaneMode === 'assignee'">
            <div class="p-5 space-y-3">

                {{-- Column headers (fixed sticky row) --}}
                <div class="flex gap-3 sticky top-0 z-10 bg-neutral-950/95 backdrop-blur-sm pb-2 border-b border-white/5">
                    {{-- Swimlane label gutter --}}
                    <div class="w-32 shrink-0"></div>
                    <template x-for="col in columns" :key="col.status">
                        <div class="flex-1 min-w-0 flex items-center gap-2 px-1">
                            <span class="w-2 h-2 rounded-full shrink-0" :style="'background:' + (col.color || '#94A3B8')"></span>
                            <span class="text-xs font-semibold text-white/55" x-text="col.label"></span>
                        </div>
                    </template>
                </div>

                {{-- Swimlane rows --}}
                <template x-for="lane in getSwimlanes()" :key="lane.key">
                    <div class="flex gap-3 group/lane">

                        {{-- Lane label --}}
                        <div class="w-32 shrink-0 flex flex-col items-start pt-2 gap-2">
                            <template x-if="lane.id">
                                <div class="w-8 h-8 rounded-full bg-white/10 text-white/50 text-[10px] font-bold flex items-center justify-center ring-1 ring-white/10" x-text="lane.label.slice(0,2).toUpperCase()"></div>
                            </template>
                            <template x-if="!lane.id">
                                <div class="w-8 h-8 rounded-full border border-dashed border-white/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            </template>
                            <span class="text-[11px] text-white/45 leading-tight max-w-full truncate" x-text="lane.label"></span>
                        </div>

                        {{-- Columns in this swimlane --}}
                        <template x-for="col in columns" :key="col.status">
                            <div class="flex-1 min-w-0">
                                <div
                                    class="rounded-xl p-2 space-y-2 min-h-24 transition-all duration-150"
                                    :class="dragOverCol === col.status && dragOverSwimlane === lane.key
                                        ? 'bg-white/[0.05] ring-1 ring-orange-500/25'
                                        : 'bg-white/[0.02]'"
                                    @dragover.prevent="dragOverCol = col.status; dragOverSwimlane = lane.key"
                                    @dragleave.self="dragOverCol = null; dragOverSwimlane = null"
                                    @drop.prevent="dropTask(col.status, lane.key)"
                                >
                                    <template x-for="task in tasksInCol(col.status, lane.key)" :key="task.id">
                                        <div
                                            class="group/card bg-neutral-900 hover:bg-neutral-800/80 border border-white/[0.07] hover:border-white/[0.14] rounded-xl p-2.5 cursor-pointer transition-all duration-100 select-none"
                                            :class="dragTaskId === task.id ? 'opacity-40 scale-95' : 'hover:shadow-md hover:shadow-black/20'"
                                            draggable="true"
                                            @dragstart="startDrag(task)"
                                            @dragend="dragTaskId = null; dragOverCol = null; dragOverSwimlane = null"
                                            @click="openTask(task)"
                                        >
                                            <div class="flex items-start gap-1.5 mb-1.5">
                                                <svg class="w-3 h-3 shrink-0 mt-px" :class="issueTypeClass(task.issue_type)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="issueTypeIcon(task.issue_type)"/>
                                                </svg>
                                                <p class="text-[11px] text-white/72 group-hover/card:text-white/88 leading-snug line-clamp-2 flex-1 transition-colors" :class="task.is_completed ? 'line-through text-white/28' : ''" x-text="task.title"></p>
                                                <span x-show="task.priority && task.priority !== 'none'" class="shrink-0 text-[8px] px-1 py-0.5 rounded-full font-medium" :class="priorityClass(task.priority)" x-text="task.priority?.charAt(0).toUpperCase()"></span>
                                            </div>

                                            {{-- Labels dots --}}
                                            <div x-show="(task.labels || []).length > 0" class="flex items-center gap-0.5 mb-1.5">
                                                <template x-for="(lbl, i) in (task.labels || []).slice(0,3)" :key="lbl.id">
                                                    <span class="w-1.5 h-1.5 rounded-full" :style="'background:' + lbl.color" :title="lbl.name"></span>
                                                </template>
                                                <span x-show="(task.labels || []).length > 3" class="text-[9px] text-white/28 ml-0.5" x-text="'+' + ((task.labels || []).length - 3)"></span>
                                            </div>

                                            <div class="flex items-center gap-1">
                                                <template x-if="task.story_points">
                                                    <span class="text-[9px] text-white/28 bg-white/5 border border-white/8 px-1 py-0.5 rounded font-mono" x-text="task.story_points"></span>
                                                </template>
                                                <div class="flex-1"></div>
                                                <template x-if="task.due_date">
                                                    <span class="text-[9px]" :class="dueDateClass(task)" x-text="formatDate(task.due_date)"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Quick add inline (swimlane) --}}
                                    <div x-show="addingToCol === col.status && addingToSwimlane === lane.key" x-cloak>
                                        <div class="bg-neutral-900 border border-orange-500/25 rounded-xl p-2">
                                            <input
                                                type="text"
                                                placeholder="Issue title…"
                                                class="w-full bg-transparent text-xs text-white/80 placeholder-white/25 focus:outline-none py-0.5 px-1"
                                                @keydown.enter="quickAddTask(col.status, lane.key, $event)"
                                                @keydown.escape="addingToCol = null; addingToSwimlane = null"
                                                @blur="addingToCol = null; addingToSwimlane = null"
                                                x-effect="if(addingToCol === col.status && addingToSwimlane === lane.key) $nextTick(() => $el.focus())"
                                            />
                                        </div>
                                    </div>

                                    {{-- Empty state --}}
                                    <div
                                        x-show="tasksInCol(col.status, lane.key).length === 0 && !(addingToCol === col.status && addingToSwimlane === lane.key)"
                                        class="h-16 flex items-center justify-center text-[9px] text-white/10 border border-dashed border-white/[0.05] rounded-xl"
                                    >
                                        Empty
                                    </div>
                                </div>

                                <button
                                    x-show="canEdit"
                                    @click="addingToCol = col.status; addingToSwimlane = lane.key"
                                    class="mt-1.5 w-full py-1 rounded text-[9px] text-white/18 hover:text-white/45 hover:bg-white/[0.03] transition-colors flex items-center justify-center gap-0.5"
                                >
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </template>

    </div>{{-- end board scroll --}}

    {{-- ================================================================ --}}
    {{-- CREATE ISSUE MODAL                                                --}}
    {{-- ================================================================ --}}
    <div
        x-show="showCreateModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.escape.window="showCreateModal = false"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showCreateModal = false"></div>
        <div class="relative w-full max-w-md bg-neutral-900 border border-white/10 rounded-2xl shadow-2xl overflow-hidden" @click.stop>

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-5 pt-5 pb-4 border-b border-white/5">
                <h3 class="text-sm font-semibold text-white/80">Create Issue</h3>
                <button @click="showCreateModal = false" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-white/8 text-white/35 hover:text-white/70 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal form --}}
            <form @submit.prevent="createIssue()" class="p-5 space-y-4">

                {{-- Issue type --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-white/35 mb-1.5">Issue Type</label>
                    <div class="flex gap-2">
                        <template x-for="type in [{v:'task',l:'Task'},{v:'bug',l:'Bug'},{v:'story',l:'Story'},{v:'epic',l:'Epic'}]" :key="type.v">
                            <button
                                type="button"
                                @click="newIssue.issue_type = type.v"
                                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors border"
                                :class="newIssue.issue_type === type.v
                                    ? 'bg-white/10 border-white/20 text-white/90'
                                    : 'bg-transparent border-white/8 text-white/40 hover:border-white/15 hover:text-white/65'"
                            >
                                <svg class="w-3 h-3" :class="issueTypeClass(type.v)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="issueTypeIcon(type.v)"/>
                                </svg>
                                <span x-text="type.l"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-white/35 mb-1.5">Title <span class="text-red-400">*</span></label>
                    <input
                        type="text"
                        x-model="newIssue.title"
                        placeholder="Issue title…"
                        required
                        class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-sm text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-orange-500/40 focus:border-transparent transition-colors"
                        x-effect="if(showCreateModal) $nextTick(() => $el.focus())"
                    />
                </div>

                {{-- Section + Status --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-white/35 mb-1.5">Section</label>
                        <select
                            x-model="newIssue.task_list_id"
                            class="w-full px-2.5 py-2 rounded-lg bg-white/5 border border-white/10 text-xs text-white/65 focus:outline-none focus:ring-1 focus:ring-orange-500/40 cursor-pointer"
                        >
                            <template x-for="tl in taskLists" :key="tl.id">
                                <option :value="tl.id" x-text="tl.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-white/35 mb-1.5">Status</label>
                        <select
                            x-model="newIssue.project_status_id"
                            class="w-full px-2.5 py-2 rounded-lg bg-white/5 border border-white/10 text-xs text-white/65 focus:outline-none focus:ring-1 focus:ring-orange-500/40 cursor-pointer"
                        >
                            <template x-for="st in columns" :key="st.statusId">
                                <option :value="st.statusId" x-text="st.label"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- Priority + Story points --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-white/35 mb-1.5">Priority</label>
                        <select
                            x-model="newIssue.priority"
                            class="w-full px-2.5 py-2 rounded-lg bg-white/5 border border-white/10 text-xs text-white/65 focus:outline-none focus:ring-1 focus:ring-orange-500/40 cursor-pointer"
                        >
                            <option value="none">None</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-white/35 mb-1.5">Story Points</label>
                        <input
                            type="number"
                            x-model="newIssue.story_points"
                            placeholder="0"
                            min="0"
                            class="w-full px-2.5 py-2 rounded-lg bg-white/5 border border-white/10 text-xs text-white/65 placeholder-white/20 focus:outline-none focus:ring-1 focus:ring-orange-500/40"
                        />
                    </div>
                </div>

                {{-- Labels --}}
                <div x-show="labels.length > 0">
                    <label class="block text-[10px] font-semibold uppercase tracking-wider text-white/35 mb-1.5">Labels</label>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="lbl in labels" :key="lbl.id">
                            <button
                                type="button"
                                @click="toggleNewIssueLabel(lbl.id)"
                                class="flex items-center gap-1 px-2 py-1 rounded-full text-[10px] border transition-colors"
                                :class="newIssue.label_ids.includes(lbl.id)
                                    ? 'bg-white/10 border-white/20 text-white/80'
                                    : 'bg-transparent border-white/8 text-white/40 hover:border-white/15'"
                            >
                                <span class="w-1.5 h-1.5 rounded-full" :style="'background:' + lbl.color"></span>
                                <span x-text="lbl.name"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        @click="showCreateModal = false"
                        class="px-3.5 py-2 rounded-lg text-xs text-white/50 hover:text-white/80 hover:bg-white/5 transition-colors"
                    >Cancel</button>
                    <button
                        type="submit"
                        :disabled="!newIssue.title.trim() || creatingIssue"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-lg bg-orange-500/25 text-orange-400 hover:bg-orange-500/35 text-xs font-semibold transition-colors border border-orange-500/25 disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <svg x-show="creatingIssue" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Create Issue
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- TASK DETAIL SLIDE-OVER (shared component)                        --}}
    {{-- ================================================================ --}}
    <x-projects.task-detail
        :project="$project"
        :members="$project->members"
        :milestones="$project->milestones"
        :labels="$project->labels"
        :canEdit="$canEdit"
    />

</div>{{-- end main flex container --}}

</x-layouts.smartprojects>

{{-- ====================================================================== --}}
{{-- Alpine.js: projectBoard()                                              --}}
{{-- ====================================================================== --}}
<script>
function projectBoard(data) {
    return {
        // ── Core data ─────────────────────────────────────────────────
        projectId:    data.projectId,
        canEdit:      data.canEdit,
        tasks:        data.tasks,
        customFields: data.customFields || [],
        statuses:     data.statuses || [],
        taskLists:    data.taskLists,
        members:      data.members,
        labels:       data.labels,

        // ── Board config (dynamic from project statuses) ─────────────
        columns: (data.statuses || []).map(s => ({
            status: s.slug,
            statusId: s.id,
            label: s.name,
            color: s.color,
            dotClass: '',
            is_completed_state: s.is_completed_state,
        })),

        // ── Swimlane ──────────────────────────────────────────────────
        swimlaneMode: 'off',

        // ── Drag & drop ───────────────────────────────────────────────
        dragTaskId:       null,
        dragOverCol:      null,
        dragOverSwimlane: null,

        // ── Task detail (consumed by x-projects.task-detail) ──────────
        selectedTask: null,

        // ── Quick add ─────────────────────────────────────────────────
        addingToCol:       null,
        addingToSwimlane:  null,

        // ── Create issue modal ────────────────────────────────────────
        showCreateModal: false,
        creatingIssue:   false,
        newIssue: {
            title:             '',
            issue_type:        'task',
            project_status_id: (data.statuses && data.statuses[0]) ? data.statuses[0].id : null,
            priority:          'none',
            story_points:      '',
            task_list_id:      null,
            label_ids:         [],
        },

        // ── Lifecycle ─────────────────────────────────────────────────
        init() {
            // Set default task list
            if (this.taskLists.length > 0) {
                this.newIssue.task_list_id = this.taskLists[0].id;
            }

            // Listen for label updates from task-detail component
            window.addEventListener('task-labels-updated', (e) => {
                const { taskId, labels } = e.detail;
                const idx = this.tasks.findIndex(t => t.id === taskId);
                if (idx !== -1) this.tasks[idx] = { ...this.tasks[idx], labels };
                if (this.selectedTask?.id === taskId) {
                    this.selectedTask = { ...this.selectedTask, labels };
                }
            });

            // Listen for delete-task event
            window.addEventListener('delete-task', (e) => {
                const taskId = e.detail?.taskId ?? e.detail;
                this.tasks = this.tasks.filter(t => t.id !== taskId);
                if (this.selectedTask?.id === taskId) this.selectedTask = null;
            });
        },

        // ── CSRF helper ───────────────────────────────────────────────
        csrf() {
            return document.querySelector('meta[name=csrf-token]').content;
        },

        // ── Generic fetch wrapper ─────────────────────────────────────
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

        // ── Tasks per column (+ optional swimlane filter) ─────────────
        tasksInCol(status, swimlaneKey = null) {
            return this.tasks.filter(t => {
                if (t.parent_task_id) return false;
                if (t.status !== status) return false;
                if (swimlaneKey !== null) {
                    if (swimlaneKey === 'unassigned') {
                        return !t.assignee;
                    }
                    return t.assignee && String(t.assignee.id) === String(swimlaneKey);
                }
                return true;
            });
        },

        // ── Swimlane rows (by assignee) ───────────────────────────────
        getSwimlanes() {
            if (this.swimlaneMode !== 'assignee') return [];

            const seen   = new Set();
            const lanes  = [];

            for (const task of this.tasks) {
                if (task.parent_task_id) continue;
                if (task.assignee && !seen.has(task.assignee.id)) {
                    seen.add(task.assignee.id);
                    lanes.push({
                        key:   String(task.assignee.id),
                        label: task.assignee.name,
                        id:    task.assignee.id,
                    });
                }
            }

            // Sort lanes alphabetically
            lanes.sort((a, b) => a.label.localeCompare(b.label));

            // Always add Unassigned at the end
            lanes.push({ key: 'unassigned', label: 'Unassigned', id: null });

            return lanes;
        },

        // ── Drag & drop ───────────────────────────────────────────────
        startDrag(task) {
            this.dragTaskId = task.id;
        },

        async dropTask(newStatus, swimlaneKey) {
            this.dragOverCol      = null;
            this.dragOverSwimlane = null;
            if (!this.dragTaskId) return;

            const task = this.tasks.find(t => t.id === this.dragTaskId);
            this.dragTaskId = null;

            if (!task) return;

            // Find the target column to get its statusId
            const col = this.columns.find(c => c.status === newStatus);
            const payload = col?.statusId ? { project_status_id: col.statusId } : { project_status_id: null };

            // If swimlane mode and dropping into a different swimlane, also reassign
            if (swimlaneKey && swimlaneKey !== 'unassigned') {
                const lane = this.getSwimlanes().find(l => l.key === swimlaneKey);
                if (lane && lane.id && task.assignee_id !== lane.id) {
                    payload.assignee_id = lane.id;
                    const member = this.members.find(m => m.id === lane.id);
                    task.assignee    = member ? { id: member.id, name: member.name } : null;
                    task.assignee_id = lane.id;
                }
            } else if (swimlaneKey === 'unassigned') {
                if (task.assignee_id) {
                    payload.assignee_id = null;
                    task.assignee    = null;
                    task.assignee_id = null;
                }
            }

            if (task.status === newStatus && !payload.assignee_id) return;

            // Optimistic update
            task.status       = newStatus;
            task.is_completed = col?.is_completed_state ?? false;
            if (col) {
                task.project_status_id = col.statusId;
                task.status_name = col.label;
                task.status_color = col.color;
            }

            const res = await this.apiCall('PUT', `/api/project-tasks/${task.id}`, payload);
            if (res?.success && res.task) {
                const idx = this.tasks.findIndex(t => t.id === task.id);
                if (idx !== -1) Object.assign(this.tasks[idx], res.task);
            }
        },

        // ── Open task detail ──────────────────────────────────────────
        async openTask(task) {
            const res = await this.apiCall('GET', `/api/project-tasks/${task.id}`);
            if (res && res.task) {
                this.selectedTask = res.task;
            } else if (res) {
                this.selectedTask = res;
            }
        },

        // ── Update task ───────────────────────────────────────────────
        async updateTask(task, payload) {
            const res = await this.apiCall('PUT', `/api/project-tasks/${task.id}`, payload);
            if (res) {
                const updated = res.task ?? res;
                const idx = this.tasks.findIndex(t => t.id === task.id);
                if (idx !== -1) {
                    this.tasks[idx] = { ...this.tasks[idx], ...updated };
                }
                if (this.selectedTask?.id === task.id) {
                    this.selectedTask = { ...this.selectedTask, ...updated };
                }
            }
        },

        // ── Quick add task in column ──────────────────────────────────
        async quickAddTask(status, swimlaneKey, event) {
            const title = event.target.value.trim();
            if (!title) return;
            event.target.value = '';
            this.addingToCol      = null;
            this.addingToSwimlane = null;

            const listId = this.newIssue.task_list_id ?? this.taskLists[0]?.id;
            if (!listId) return;

            const col = this.columns.find(c => c.status === status);
            const body = { title, task_list_id: listId, project_status_id: col?.statusId };

            // Pre-assign to swimlane member
            if (swimlaneKey && swimlaneKey !== 'unassigned') {
                const lane = this.getSwimlanes().find(l => l.key === swimlaneKey);
                if (lane?.id) body.assignee_id = lane.id;
            }

            const res = await this.apiCall('POST', `/api/projects/${this.projectId}/tasks`, body);
            if (res && res.success && res.task) {
                this.tasks.push({
                    ...res.task,
                    labels:      res.task.labels      ?? [],
                    issue_type:  res.task.issue_type  ?? 'task',
                    story_points: res.task.story_points ?? null,
                });
            } else if (res && res.id) {
                this.tasks.push({ ...res, labels: res.labels ?? [], issue_type: res.issue_type ?? 'task' });
            }
        },

        // ── Create issue (modal) ──────────────────────────────────────
        async createIssue() {
            if (!this.newIssue.title.trim() || this.creatingIssue) return;
            this.creatingIssue = true;

            const body = {
                title:             this.newIssue.title.trim(),
                issue_type:        this.newIssue.issue_type,
                project_status_id: this.newIssue.project_status_id,
                priority:          this.newIssue.priority,
                task_list_id:      this.newIssue.task_list_id ?? this.taskLists[0]?.id,
            };
            if (this.newIssue.story_points) body.story_points = parseInt(this.newIssue.story_points, 10);
            if (this.newIssue.label_ids.length > 0) body.label_ids = this.newIssue.label_ids;

            const res = await this.apiCall('POST', `/api/projects/${this.projectId}/tasks`, body);

            this.creatingIssue = false;

            if (res && (res.success || res.id)) {
                const task = res.task ?? res;
                this.tasks.push({
                    ...task,
                    labels:       task.labels       ?? [],
                    issue_type:   task.issue_type   ?? 'task',
                    story_points: task.story_points ?? null,
                });
                this.showCreateModal = false;
                this._resetNewIssue();
                this._toast('Issue created');
            }
        },

        _resetNewIssue() {
            this.newIssue = {
                title:             '',
                issue_type:        'task',
                project_status_id: this.columns[0]?.statusId ?? null,
                priority:          'none',
                story_points:      '',
                task_list_id:      this.taskLists[0]?.id ?? null,
                label_ids:    [],
            };
        },

        toggleNewIssueLabel(labelId) {
            const idx = this.newIssue.label_ids.indexOf(labelId);
            if (idx === -1) this.newIssue.label_ids.push(labelId);
            else this.newIssue.label_ids.splice(idx, 1);
        },

        // ── Issue type helpers ────────────────────────────────────────
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
            const classes = {
                task:  'text-sky-400',
                bug:   'text-red-400',
                story: 'text-blue-400',
                epic:  'text-orange-400',
            };
            return classes[type] ?? 'text-sky-400';
        },

        // ── Priority badge class ──────────────────────────────────────
        priorityClass(p) {
            const map = {
                critical: 'bg-red-500/20 text-red-400',
                high:     'bg-orange-500/20 text-orange-400',
                medium:   'bg-amber-500/20 text-amber-400',
                low:      'bg-blue-500/20 text-blue-400',
                none:     'bg-white/8 text-white/30',
            };
            return map[p] ?? 'bg-white/8 text-white/30';
        },

        // ── Due date colour ───────────────────────────────────────────
        dueDateClass(task) {
            if (!task.due_date) return 'text-white/25';
            if (task.is_completed) return 'text-white/25';
            const today = new Date(); today.setHours(0, 0, 0, 0);
            const due   = new Date(task.due_date + 'T00:00:00');
            if (due < today) return 'text-red-400';
            const diff = (due - today) / 86400000;
            if (diff <= 2) return 'text-orange-400';
            return 'text-white/30';
        },

        // ── Date formatter ────────────────────────────────────────────
        formatDate(d) {
            if (!d) return '';
            const date = new Date(d + 'T00:00:00');
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        // ── Toast helper ──────────────────────────────────────────────
        _toast(msg) {
            try {
                Alpine.store('toast').success(msg);
            } catch (_) {}
        },
    };
}
</script>
