<x-layouts.opportunity title="List" :project="$project" currentView="list">

@php
$initData = [
    'projectId' => $project->id,
    'projectSlug' => $project->slug,
    'sections' => $project->sections->map(fn($s) => [
        'id' => $s->id,
        'name' => $s->name,
        'position' => $s->position,
        'tasks' => $s->tasks->map(fn($t) => [
            'id' => $t->id, 'title' => $t->title, 'status' => $t->status,
            'assignee_id' => $t->assignee_id,
            'assignee' => $t->assignee ? ['id' => $t->assignee->id, 'name' => $t->assignee->name] : null,
            'assignees' => $t->assignees->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->values(),
            'due_date' => $t->due_date?->format('Y-m-d'),
            'position' => $t->position, 'parent_task_id' => $t->parent_task_id,
            'subtasks_count' => $t->subtasks_count ?? 0,
            'comments_count' => $t->comments_count ?? 0,
            'attachments_count' => $t->attachments_count ?? 0,
        ])->values(),
    ])->values(),
    'members' => $project->members->map(fn($m) => ['id' => $m->id, 'name' => $m->name])->values(),
];
@endphp

<div class="flex h-[calc(100vh-140px)]" x-data="oppList({{ Js::from($initData) }})">

    {{-- LEFT: Sections + Tasks --}}
    <div class="flex-1 flex flex-col min-w-0" :class="selectedTask ? 'border-r border-white/[0.06]' : ''">

        {{-- Toolbar --}}
        <div class="shrink-0 flex items-center gap-2 px-5 py-2 border-b border-white/[0.06]">
            <button @click="addingTaskToSection = sections[0]?.id; $nextTick(() => $refs.addTaskInput?.focus())"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-500 text-white text-[12px] font-semibold hover:bg-teal-400 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add task
            </button>
            <div class="flex-1"></div>
            <button class="px-2.5 py-1.5 rounded-md text-[12px] text-white/35 hover:text-white/55 hover:bg-white/[0.04]">Filter</button>
            <button class="px-2.5 py-1.5 rounded-md text-[12px] text-white/35 hover:text-white/55 hover:bg-white/[0.04]">Sort</button>
            <button class="px-2.5 py-1.5 rounded-md text-[12px] text-white/35 hover:text-white/55 hover:bg-white/[0.04]">Group</button>
        </div>

        {{-- Column headers --}}
        <div class="shrink-0 grid grid-cols-12 px-5 py-2 text-[11px] font-semibold text-white/30 uppercase tracking-wider border-b border-white/[0.06]">
            <div class="col-span-6">Name</div>
            <div class="col-span-3">Assignee</div>
            <div class="col-span-3">Due date</div>
        </div>

        {{-- Scrollable content --}}
        <div class="flex-1 overflow-y-auto">
            <template x-for="section in sections" :key="section.id">
                <div class="border-b border-white/[0.06]">
                    {{-- Section header --}}
                    <div class="flex items-center gap-2 px-5 py-2 bg-white/[0.02] group cursor-pointer"
                         @click="toggleSection(section.id)">
                        <svg class="w-3.5 h-3.5 text-white/25 transition-transform" :class="collapsedSections.includes(section.id) ? '' : 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>

                        {{-- Editable section name --}}
                        <template x-if="editingSectionId === section.id">
                            <input type="text" x-model="editingSectionName"
                                @keydown.enter="saveSection(section)" @blur="saveSection(section)" @click.stop
                                x-effect="if(editingSectionId === section.id) $nextTick(() => $el.focus())"
                                class="bg-transparent text-[14px] font-semibold text-white/70 focus:outline-none border-b border-teal-500/50 py-0.5"/>
                        </template>
                        <template x-if="editingSectionId !== section.id">
                            <span class="text-[14px] font-semibold text-white/70" @dblclick.stop="editingSectionId = section.id; editingSectionName = section.name" x-text="section.name"></span>
                        </template>

                        <span class="text-[11px] text-white/20 ml-1" x-text="section.tasks.length"></span>

                        {{-- Add task to this section --}}
                        <button @click.stop="addingTaskToSection = section.id; $nextTick(() => $refs.addTaskInput?.focus())"
                            class="ml-auto opacity-0 group-hover:opacity-100 text-white/25 hover:text-teal-400 transition-all p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>

                    {{-- Tasks in this section --}}
                    <div x-show="!collapsedSections.includes(section.id)">
                        <template x-for="task in section.tasks" :key="task.id">
                            <div class="grid grid-cols-12 items-center px-5 py-[7px] border-t border-white/[0.04] cursor-pointer group transition-colors"
                                 :class="selectedTask?.id === task.id ? 'bg-teal-500/[0.06]' : 'hover:bg-white/[0.02]'"
                                 @click="openTask(task)">

                                {{-- Name --}}
                                <div class="col-span-6 flex items-center gap-3 min-w-0 pr-3">
                                    <button @click.stop="toggleComplete(task, section)"
                                        class="w-[18px] h-[18px] rounded-full border-2 shrink-0 flex items-center justify-center transition-all"
                                        :class="task.status === 'complete' ? 'bg-teal-500 border-teal-500' : 'border-white/20 hover:border-teal-400'">
                                        <svg x-show="task.status === 'complete'" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <template x-if="editingTaskId === task.id">
                                        <input type="text" x-model="editingTitle" @keydown.enter="saveTitleInline(task)" @blur="saveTitleInline(task)" @click.stop
                                            x-effect="if(editingTaskId === task.id) $nextTick(() => $el.focus())"
                                            class="flex-1 min-w-0 bg-transparent text-[13px] text-white/80 focus:outline-none border-b border-teal-500/50"/>
                                    </template>
                                    <template x-if="editingTaskId !== task.id">
                                        <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                            <span class="text-[13px] truncate" :class="task.status === 'complete' ? 'line-through text-white/25' : 'text-white/75'"
                                                @dblclick.stop="editingTaskId = task.id; editingTitle = task.title" x-text="task.title"></span>
                                            <span x-show="task.subtasks_count > 0" class="text-[11px] text-white/20 shrink-0" x-text="task.subtasks_count"></span>
                                            <span x-show="task.comments_count > 0" class="flex items-center gap-0.5 text-[11px] text-white/20 shrink-0">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                <span x-text="task.comments_count"></span>
                                            </span>
                                            <svg x-show="task.attachments_count > 0" class="w-3 h-3 text-white/20 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        </div>
                                    </template>
                                </div>

                                {{-- Assignee --}}
                                <div class="col-span-3 flex items-center gap-1.5">
                                    <template x-if="task.assignee">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-6 h-6 rounded-full text-[8px] font-bold flex items-center justify-center ring-2 ring-[#1A1A2E]"
                                                :style="'background:'+strColor(task.assignee.name)+'33;color:'+strColor(task.assignee.name)"
                                                :title="task.assignee.name"
                                                x-text="task.assignee.name.slice(0,2).toUpperCase()"></div>
                                            <span class="text-[12px] text-white/45 truncate max-w-[80px]" x-text="task.assignee.name.split(' ')[0] + ' ' + (task.assignee.name.split(' ')[1]||'').slice(0,2) + '...'"></span>
                                        </div>
                                    </template>
                                </div>

                                {{-- Due date --}}
                                <div class="col-span-3">
                                    <span class="text-[12px]"
                                        :class="isOverdue(task) ? 'text-red-400' : (isToday(task) ? 'text-red-400' : 'text-white/40')"
                                        x-text="task.due_date ? formatDueDate(task.due_date) : ''"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Add task input (per section) --}}
                        <div x-show="addingTaskToSection === section.id" x-cloak class="grid grid-cols-12 items-center px-5 py-2 border-t border-white/[0.04] bg-white/[0.015]">
                            <div class="col-span-6 flex items-center gap-3">
                                <div class="w-[18px] h-[18px] rounded-full border-2 border-white/15 shrink-0"></div>
                                <input x-ref="addTaskInput" type="text" x-model="newTaskTitle" placeholder="Write a task name"
                                    @keydown.enter="createTask(section)" @keydown.escape="addingTaskToSection = null; newTaskTitle = ''"
                                    class="flex-1 bg-transparent text-[13px] text-white/80 placeholder-white/25 focus:outline-none"/>
                            </div>
                        </div>

                        {{-- "Add task" link at bottom of section --}}
                        <div x-show="addingTaskToSection !== section.id"
                             @click="addingTaskToSection = section.id; $nextTick(() => $refs.addTaskInput?.focus())"
                             class="flex items-center gap-3 px-5 py-2 border-t border-white/[0.04] text-white/20 hover:text-white/40 cursor-pointer transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="text-[13px]">Add task</span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Add section --}}
            <div class="px-5 py-3">
                <template x-if="!addingSectionName">
                    <button @click="addingSectionName = 'Untitled section'; $nextTick(() => $refs.newSectionInput?.focus())"
                        class="flex items-center gap-2 text-[13px] text-white/25 hover:text-white/50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add section
                    </button>
                </template>
                <template x-if="addingSectionName !== null && addingSectionName !== false">
                    <input x-ref="newSectionInput" type="text" x-model="addingSectionName" placeholder="Section name"
                        @keydown.enter="createSection()" @keydown.escape="addingSectionName = null"
                        @blur="if(addingSectionName) createSection()"
                        class="bg-transparent text-[14px] font-semibold text-white/70 placeholder-white/25 focus:outline-none border-b border-teal-500/50 py-1"/>
                </template>
            </div>
        </div>
    </div>

    {{-- RIGHT: Detail Panel --}}
    <div x-show="selectedTask" x-cloak x-transition class="w-[480px] shrink-0 flex flex-col bg-[#1A1A2E] overflow-y-auto">
        <template x-if="selectedTask">
            <div>
                <div class="sticky top-0 z-10 flex items-center gap-2 px-4 py-2.5 bg-[#1A1A2E] border-b border-white/[0.06]">
                    {{-- Mark complete button --}}
                    <button @click="toggleCompleteDetail()"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium border transition-colors"
                        :class="selectedTask.status === 'complete' ? 'border-teal-500/30 bg-teal-500/10 text-teal-400' : 'border-white/[0.1] text-white/50 hover:bg-white/[0.04]'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="selectedTask.status === 'complete' ? 'Completed' : 'Mark complete'"></span>
                    </button>

                    <div class="flex-1"></div>

                    {{-- Collaborator avatars (Asana style) --}}
                    <div class="flex items-center -space-x-1.5 mr-2">
                        <template x-if="selectedTask.assignee">
                            <div class="w-6 h-6 rounded-full text-[8px] font-bold flex items-center justify-center ring-2 ring-[#1A1A2E]"
                                :style="'background:'+strColor(selectedTask.assignee.name)+'33;color:'+strColor(selectedTask.assignee.name)"
                                x-text="selectedTask.assignee.name.slice(0,2).toUpperCase()"></div>
                        </template>
                        <template x-for="a in (selectedTask.assignees||[]).slice(0,3)" :key="a.id">
                            <div class="w-6 h-6 rounded-full text-[8px] font-bold flex items-center justify-center ring-2 ring-[#1A1A2E]"
                                :style="'background:'+strColor(a.name)+'33;color:'+strColor(a.name)"
                                x-text="a.name.slice(0,2).toUpperCase()"></div>
                        </template>
                    </div>

                    {{-- Share button — copies link to clipboard --}}
                    <button @click="shareTask()" class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[11px] font-medium bg-teal-500 text-white hover:bg-teal-400 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        <span x-text="shareMsg || 'Share'"></span>
                    </button>

                    {{-- Like button --}}
                    <button @click="toggleLike()" class="p-1.5 transition-colors" :class="isLiked ? 'text-teal-400' : 'text-white/25 hover:text-white/50'" :title="isLiked ? 'Unlike' : 'Like'">
                        <svg class="w-4 h-4" :fill="isLiked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                    </button>

                    {{-- Copy link --}}
                    <button @click="copyLink()" class="p-1.5 transition-colors" :class="linkCopied ? 'text-teal-400' : 'text-white/25 hover:text-white/50'" :title="linkCopied ? 'Copied!' : 'Copy link'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </button>

                    {{-- Fullscreen (open task in dedicated view) --}}
                    <button @click="expandTask()" class="p-1.5 text-white/25 hover:text-white/50 transition-colors" title="Full screen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                    </button>

                    {{-- More menu --}}
                    <div x-data="{ moreOpen: false }" class="relative">
                        <button @click="moreOpen = !moreOpen" class="p-1.5 text-white/25 hover:text-white/50 transition-colors" title="More actions">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                        </button>
                        <div x-show="moreOpen" x-cloak @click.outside="moreOpen = false"
                             class="absolute right-0 top-full mt-1 w-48 bg-[#1E1E32] border border-white/[0.1] rounded-xl shadow-2xl py-1 z-50">
                            <button @click="duplicateTask(); moreOpen = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors text-left">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Duplicate task
                            </button>
                            <button @click="copyLink(); moreOpen = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors text-left">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/></svg>
                                Copy task link
                            </button>
                            <button @click="toggleFollowTask(); moreOpen = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors text-left">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                <span x-text="isFollowing ? 'Unfollow task' : 'Follow task'"></span>
                            </button>
                            <div class="border-t border-white/[0.06] my-1"></div>
                            <button @click="deleteTask(); moreOpen = false"
                                class="w-full flex items-center gap-2 px-3 py-2 text-[12px] text-red-400/70 hover:bg-red-500/10 hover:text-red-400 transition-colors text-left">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete task
                            </button>
                        </div>
                    </div>

                    {{-- Close panel --}}
                    <button @click="selectedTask = null" class="p-1.5 text-white/25 hover:text-white/50 transition-colors" title="Close">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <div class="px-5 pt-4 pb-2">
                    <input type="text" x-model="selectedTask.title" @blur="updateDetail('title', selectedTask.title)"
                        class="w-full bg-transparent text-[18px] font-semibold text-white/90 focus:outline-none"/>
                </div>

                <div class="px-5 space-y-3 pb-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-4">
                        <span class="text-[12px] text-white/35 w-20">Assignee</span>
                        <select @change="updateDetail('assignee_id', $event.target.value || null)"
                            class="bg-white/[0.05] border border-white/[0.08] rounded-lg px-2 py-1 text-[12px] text-white/60 focus:outline-none focus:ring-1 focus:ring-teal-500/40">
                            <option value="">No assignee</option>
                            <template x-for="m in members" :key="m.id">
                                <option :value="m.id" x-text="m.name" :selected="selectedTask.assignee_id == m.id"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-[12px] text-white/35 w-20">Due date</span>
                        <input type="date" :value="selectedTask.due_date" @change="updateDetail('due_date', $event.target.value || null)"
                            class="px-2 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[12px] text-white/60 focus:outline-none focus:ring-1 focus:ring-teal-500/40"/>
                    </div>
                </div>

                <div class="px-5 py-4 border-b border-white/[0.06]">
                    <span class="text-[12px] text-white/35 block mb-2">Description</span>
                    <textarea x-model="selectedTask.description" @blur="updateDetail('description', selectedTask.description)"
                        rows="3" placeholder="Add description..."
                        class="w-full bg-white/[0.03] border border-white/[0.06] rounded-lg px-3 py-2 text-[13px] text-white/65 placeholder-white/20 focus:outline-none focus:ring-1 focus:ring-teal-500/30 resize-none"></textarea>
                </div>

                {{-- Subtasks --}}
                <div class="px-5 py-4 border-b border-white/[0.06]">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-[13px] font-medium text-white/60">Subtasks</span>
                        <button @click="showSubtaskInput = true; $nextTick(() => $refs.subtaskInput?.focus())" class="text-white/20 hover:text-teal-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                    </div>
                    <template x-for="sub in (selectedTask.subtasks || [])" :key="sub.id">
                        <div class="flex items-center gap-2.5 py-1.5">
                            <button @click="toggleSubComplete(sub)" class="w-4 h-4 rounded-full border-2 shrink-0 flex items-center justify-center"
                                :class="sub.status==='complete' ? 'bg-teal-500 border-teal-500' : 'border-white/20 hover:border-teal-400'">
                                <svg x-show="sub.status==='complete'" class="w-2 h-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <span class="text-[13px]" :class="sub.status==='complete'?'line-through text-white/25':'text-white/65'" x-text="sub.title"></span>
                        </div>
                    </template>
                    <div x-show="showSubtaskInput" x-cloak class="flex items-center gap-2.5 py-1.5">
                        <div class="w-4 h-4 rounded-full border-2 border-white/15 shrink-0"></div>
                        <input x-ref="subtaskInput" type="text" x-model="newSubtaskTitle" placeholder="Add subtask..."
                            @keydown.enter="createSubtask()" @keydown.escape="showSubtaskInput=false;newSubtaskTitle=''"
                            class="flex-1 bg-transparent text-[13px] text-white/65 placeholder-white/20 focus:outline-none"/>
                    </div>
                </div>

                {{-- Comments / All Activity (Asana style) --}}
                <div class="px-5 py-4">
                    <div class="flex items-center gap-0 mb-3 border-b border-white/[0.06] -mx-5 px-5">
                        <button @click="detailTab='comments'" :class="detailTab==='comments'?'border-white/50 text-white/70':'border-transparent text-white/30'"
                            class="px-3 py-2 text-[13px] font-medium border-b-2 transition-colors">Comments</button>
                        <button @click="detailTab='activity'" :class="detailTab==='activity'?'border-white/50 text-white/70':'border-transparent text-white/30'"
                            class="px-3 py-2 text-[13px] font-medium border-b-2 transition-colors">All activity</button>
                        <div class="flex-1"></div>
                        <button class="flex items-center gap-1 text-[11px] text-white/25 hover:text-white/45 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                            Oldest
                        </button>
                    </div>

                    {{-- Comments view --}}
                    <div x-show="detailTab==='comments'" class="space-y-4">
                        <template x-for="c in (selectedTask.comments||[])" :key="c.id">
                            <div class="flex gap-2.5 group/comment">
                                <div class="w-8 h-8 rounded-full text-[9px] font-bold flex items-center justify-center shrink-0 mt-0.5"
                                    :style="'background:'+strColor(c.user.name)+'33;color:'+strColor(c.user.name)" x-text="c.user.name.slice(0,2).toUpperCase()"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-[13px] font-semibold text-white/75" x-text="c.user.name"></span>
                                        <span class="text-[11px] text-white/25" x-text="timeAgo(c.created_at)"></span>
                                        {{-- Comment actions (visible on hover, like Asana) --}}
                                        <div class="ml-auto opacity-0 group-hover/comment:opacity-100 flex items-center gap-0.5 transition-opacity">
                                            <button class="p-1 rounded hover:bg-white/[0.06] text-white/20 hover:text-white/50" title="Like">👍</button>
                                            <button class="p-1 rounded hover:bg-white/[0.06] text-white/20 hover:text-white/50" title="Celebrate">🎉</button>
                                            <button class="p-1 rounded hover:bg-white/[0.06] text-white/20 hover:text-white/50" title="React">😊</button>
                                            <button class="p-1 rounded hover:bg-white/[0.06] text-white/20 hover:text-white/50" title="More">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-[13px] text-white/55 mt-1 whitespace-pre-wrap leading-relaxed" x-text="c.body"></p>
                                </div>
                            </div>
                        </template>
                        <template x-if="(selectedTask.comments||[]).length === 0">
                            <p class="text-[12px] text-white/20 text-center py-3">No comments yet</p>
                        </template>
                    </div>

                    {{-- All activity view --}}
                    <div x-show="detailTab==='activity'" class="space-y-3">
                        <template x-for="a in (selectedTask.activity||[])" :key="a.id">
                            <div class="flex gap-2.5">
                                <div class="w-7 h-7 rounded-full text-[8px] font-bold flex items-center justify-center shrink-0 mt-0.5"
                                    :style="'background:'+strColor(a.user?.name||'?')+'33;color:'+strColor(a.user?.name||'?')" x-text="(a.user?.name||'?').slice(0,2).toUpperCase()"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[12px]">
                                        <span class="text-white/60 font-medium" x-text="a.user?.name||''"></span>
                                        <span class="text-white/35" x-text="' ' + fmtAction(a)"></span>
                                        <template x-if="a.new_value && a.field_name === 'due_date'">
                                            <span class="text-teal-400 font-medium" x-text="' ' + formatDueDate(a.new_value)"></span>
                                        </template>
                                        <span class="text-white/20 ml-1" x-text="'· ' + timeAgo(a.created_at)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="(selectedTask.activity||[]).length === 0">
                            <p class="text-[12px] text-white/20 text-center py-3">No activity yet</p>
                        </template>
                    </div>

                    {{-- Comment input (Asana-style rich editor) --}}
                    <div class="mt-4 flex gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-teal-500/20 text-teal-400 text-[9px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                        <div class="flex-1">
                            <div class="border border-white/[0.08] rounded-lg overflow-hidden focus-within:ring-1 focus-within:ring-teal-500/30 focus-within:border-teal-500/20">
                                <textarea x-model="newComment" rows="3" placeholder="Type / for menu"
                                    class="w-full bg-white/[0.02] px-3 py-2.5 text-[13px] text-white/65 placeholder-white/20 focus:outline-none resize-none border-none"></textarea>
                                {{-- Toolbar (Asana style) --}}
                                <div class="flex items-center justify-between px-2 py-1.5 bg-white/[0.02] border-t border-white/[0.06]">
                                    <div class="flex items-center gap-0.5">
                                        <button type="button" class="p-1.5 rounded hover:bg-white/[0.06] text-white/25 hover:text-white/50 transition-colors" title="Insert">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                        <button type="button" class="p-1.5 rounded hover:bg-white/[0.06] text-white/25 hover:text-white/50 transition-colors text-[13px] font-bold" title="Formatting">A</button>
                                        <button type="button" class="p-1.5 rounded hover:bg-white/[0.06] text-white/25 hover:text-white/50 transition-colors" title="Emoji">😊</button>
                                        <button type="button" class="p-1.5 rounded hover:bg-white/[0.06] text-white/25 hover:text-white/50 transition-colors" title="Mention">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                                        </button>
                                        <button type="button" class="p-1.5 rounded hover:bg-white/[0.06] text-white/25 hover:text-white/50 transition-colors" title="Attach">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span x-show="newComment.trim()" class="text-[10px] text-white/20" x-text="members.length + ' people will be notified'"></span>
                                        <button @click="postComment()" x-show="newComment.trim()"
                                            class="px-3 py-1 rounded-md bg-teal-500 text-white text-[12px] font-semibold hover:bg-teal-400 transition-colors">
                                            Comment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function oppList(data) {
    return {
        sections: data.sections, members: data.members,
        selectedTask: null, detailTab: 'comments',
        collapsedSections: [],
        addingTaskToSection: null, newTaskTitle: '',
        editingTaskId: null, editingTitle: '',
        editingSectionId: null, editingSectionName: '',
        addingSectionName: null,
        showSubtaskInput: false, newSubtaskTitle: '', newComment: '',
        isLiked: false, shareMsg: '', linkCopied: false, isFollowing: false,

        toggleSection(id) { const i = this.collapsedSections.indexOf(id); i===-1 ? this.collapsedSections.push(id) : this.collapsedSections.splice(i,1); },

        async createTask(section) {
            if (!this.newTaskTitle.trim()) return;
            const r = await this.api('POST', '/api/opp/tasks', {
                title: this.newTaskTitle.trim(),
                project_id: data.projectId,
                section_id: section.id,
                assignee_id: {{ auth()->id() }},
            });
            if (r?.task) {
                r.task.subtasks_count = 0;
                r.task.comments_count = 0;
                r.task.attachments_count = 0;
                section.tasks.push(r.task);
                this.newTaskTitle = '';
            }
        },

        async toggleComplete(task, section) {
            const r = await this.api('POST', '/api/opp/tasks/' + task.id + '/complete');
            if (r?.task) {
                Object.assign(task, r.task);
                if (r.task.status === 'complete') {
                    // Remove from section after brief delay to show the check animation
                    setTimeout(() => { section.tasks = section.tasks.filter(t => t.id !== task.id); }, 600);
                }
            }
        },

        async saveTitleInline(task) {
            if (this.editingTitle.trim() && this.editingTitle !== task.title) {
                const r = await this.api('PUT', '/api/opp/tasks/' + task.id, { title: this.editingTitle.trim() });
                if (r?.task) Object.assign(task, r.task);
            }
            this.editingTaskId = null;
        },

        async openTask(task) {
            this.selectedTask = { ...task }; this.detailTab = 'comments';
            this.showSubtaskInput = false; this.newSubtaskTitle = ''; this.newComment = '';
            this.isLiked = false; this.shareMsg = ''; this.linkCopied = false; this.isFollowing = false;
            const r = await this.api('GET', '/api/opp/tasks/' + task.id);
            if (r?.task) this.selectedTask = r.task;
        },

        async toggleCompleteDetail() {
            if (!this.selectedTask) return;
            const r = await this.api('POST', '/api/opp/tasks/' + this.selectedTask.id + '/complete');
            if (r?.task) {
                this.selectedTask = { ...this.selectedTask, ...r.task };
                // Update in section list too
                for (const s of this.sections) {
                    const i = s.tasks.findIndex(t => t.id === r.task.id);
                    if (i !== -1) { Object.assign(s.tasks[i], r.task); break; }
                }
            }
        },

        async updateDetail(field, value) {
            if (!this.selectedTask) return;
            const r = await this.api('PUT', '/api/opp/tasks/' + this.selectedTask.id, { [field]: value });
            if (r?.task) {
                Object.assign(this.selectedTask, r.task);
                for (const s of this.sections) {
                    const i = s.tasks.findIndex(t => t.id === r.task.id);
                    if (i !== -1) { Object.assign(s.tasks[i], r.task); break; }
                }
            }
        },

        async saveSection(section) {
            if (this.editingSectionName.trim() && this.editingSectionName !== section.name) {
                await this.api('PUT', '/api/opp/sections/' + section.id, { name: this.editingSectionName.trim() });
                section.name = this.editingSectionName.trim();
            }
            this.editingSectionId = null;
        },

        async createSection() {
            if (!this.addingSectionName?.trim()) { this.addingSectionName = null; return; }
            const r = await this.api('POST', '/api/opp/sections', { name: this.addingSectionName.trim(), project_id: data.projectId });
            if (r?.section) this.sections.push({ ...r.section, tasks: [] });
            this.addingSectionName = null;
        },

        async toggleLike() {
            if (!this.selectedTask) return;
            const r = await this.api('POST', '/api/opp/tasks/' + this.selectedTask.id + '/like');
            if (r) { this.selectedTask.likes_count = r.likes_count; this.isLiked = r.liked; }
        },

        shareTask() {
            const url = window.location.origin + '/opportunity/projects/' + data.projectSlug + '?task=' + this.selectedTask?.id;
            navigator.clipboard.writeText(url).then(() => {
                this.shareMsg = 'Copied!';
                setTimeout(() => { this.shareMsg = ''; }, 2000);
            }).catch(() => {
                prompt('Copy this link:', url);
            });
        },

        copyLink() {
            const url = window.location.origin + '/opportunity/projects/' + data.projectSlug + '?task=' + this.selectedTask?.id;
            navigator.clipboard.writeText(url).then(() => {
                this.linkCopied = true;
                setTimeout(() => { this.linkCopied = false; }, 2000);
            });
        },

        expandTask() {
            // Open task in a full-width overlay
            if (!this.selectedTask) return;
            const url = window.location.origin + '/opportunity/projects/' + data.projectSlug + '?task=' + this.selectedTask.id;
            window.open(url, '_blank');
        },

        async duplicateTask() {
            if (!this.selectedTask) return;
            const r = await this.api('POST', '/api/opp/tasks/' + this.selectedTask.id + '/duplicate');
            if (r?.task) {
                // Add duplicated task to the correct section
                for (const s of this.sections) {
                    if (s.id === r.task.section_id) {
                        r.task.subtasks_count = 0; r.task.comments_count = 0; r.task.attachments_count = 0;
                        s.tasks.push(r.task);
                        break;
                    }
                }
            }
        },

        async toggleFollowTask() {
            if (!this.selectedTask) return;
            const r = await this.api('POST', '/api/opp/tasks/' + this.selectedTask.id + '/followers', { user_id: {{ auth()->id() }} });
            if (r) { this.isFollowing = r.added; }
        },

        async deleteTask() {
            if (!this.selectedTask) return;
            if (!confirm('Delete "' + this.selectedTask.title + '"? This cannot be undone.')) return;
            const taskId = this.selectedTask.id;
            const r = await this.api('DELETE', '/api/opp/tasks/' + taskId);
            if (r) {
                for (const s of this.sections) {
                    s.tasks = s.tasks.filter(t => t.id !== taskId);
                }
                this.selectedTask = null;
            }
        },

        async toggleSubComplete(sub) {
            const r = await this.api('POST', '/api/opp/tasks/' + sub.id + '/complete');
            if (r?.task && this.selectedTask?.subtasks) {
                const idx = this.selectedTask.subtasks.findIndex(s => s.id === sub.id);
                if (idx !== -1) {
                    this.selectedTask.subtasks[idx] = { ...this.selectedTask.subtasks[idx], ...r.task };
                    // Force Alpine reactivity by replacing the array
                    this.selectedTask.subtasks = [...this.selectedTask.subtasks];
                }
            }
        },

        async createSubtask() {
            if (!this.newSubtaskTitle.trim() || !this.selectedTask) return;
            const r = await this.api('POST', '/api/opp/tasks', { title: this.newSubtaskTitle.trim(), project_id: data.projectId, parent_task_id: this.selectedTask.id });
            if (r?.task) { if (!this.selectedTask.subtasks) this.selectedTask.subtasks = []; this.selectedTask.subtasks.push(r.task); this.newSubtaskTitle = ''; }
        },

        async postComment() {
            if (!this.newComment.trim() || !this.selectedTask) return;
            const r = await this.api('POST', '/api/opp/comments', { body: this.newComment.trim(), task_id: this.selectedTask.id });
            if (r?.comment) { if (!this.selectedTask.comments) this.selectedTask.comments = []; this.selectedTask.comments.push(r.comment); this.newComment = ''; }
        },

        isOverdue(t) { return t.due_date && new Date(t.due_date+'T23:59:59') < new Date() && t.status !== 'complete'; },
        isToday(t) { return t.due_date === new Date().toISOString().slice(0,10); },
        formatDueDate(d) {
            if (!d) return '';
            const dt = new Date(d+'T00:00:00'), now = new Date(); now.setHours(0,0,0,0);
            const diff = Math.round((dt-now)/86400000);
            if (diff===0) return 'Today'; if (diff===1) return 'Tomorrow'; if (diff===-1) return 'Yesterday';
            return dt.toLocaleDateString('en-US',{month:'short',day:'numeric'});
        },
        timeAgo(s) { if(!s)return'';const d=(Date.now()-new Date(s).getTime())/1000;if(d<60)return'just now';if(d<3600)return Math.floor(d/60)+'m ago';if(d<86400)return Math.floor(d/3600)+'h ago';return Math.floor(d/86400)+'d ago';},
        fmtAction(a) { const m={'task.created':'created this task','task.completed':'completed this task','task.reopened':'marked incomplete','comment.added':'commented'};return m[a.action]||(a.field_name?'changed '+a.field_name.replace('_',' '):a.action); },
        strColor(s) { if(!s)return'#6B7280';let h=0;for(let i=0;i<s.length;i++)h=s.charCodeAt(i)+((h<<5)-h);return['#F43F5E','#EC4899','#A855F7','#6366F1','#3B82F6','#14B8A6','#10B981','#F59E0B','#EF4444','#8B5CF6'][Math.abs(h)%10]; },
        async api(m,u,b=null){try{const o={method:m,headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}};if(b)o.body=JSON.stringify(b);const r=await fetch(u,o);if(!r.ok)return null;return await r.json();}catch(e){console.error(e);return null;}},
    };
}
</script>

</x-layouts.opportunity>
