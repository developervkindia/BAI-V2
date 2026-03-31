@props(['project', 'members' => [], 'milestones' => [], 'labels' => [], 'canEdit' => false])

{{--
  Shared SmartProjects task detail slide-over.
  Usage: <x-projects.task-detail :project="$project" :members="$members" :milestones="$milestones" :labels="$labels" :canEdit="$canEdit" />

  The parent view must have:
    - `selectedTask` (Alpine reactive) — null or task object
    - `openTask(task)` method that fetches full task data via GET /api/project-tasks/{id}
    - `updateTask(task, data)` method
    - `deleteTask(task)` method (optional)
--}}

<div
    x-show="selectedTask !== null"
    x-cloak
    class="fixed inset-0 z-50 flex justify-end"
    @keydown.escape.window="selectedTask = null"
>
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/40" @click="selectedTask = null"></div>

    {{-- Panel --}}
    <div
        class="relative w-full max-w-2xl bg-neutral-900 border-l border-white/10 h-full flex flex-col shadow-2xl"
        @click.stop
        x-data="taskDetailPanel()"
        x-init="init()"
    >
        <template x-if="selectedTask">
            <div class="flex flex-col h-full">

                {{-- Header --}}
                <div class="flex items-start gap-3 px-5 pt-5 pb-3 border-b border-white/5 shrink-0">
                    {{-- Issue type selector --}}
                    <div class="shrink-0 mt-0.5">
                        <select
                            x-model="selectedTask.issue_type"
                            @change="updateTask(selectedTask, {issue_type: selectedTask.issue_type})"
                            class="bg-transparent border-0 text-white/40 text-xs focus:ring-0 focus:outline-none cursor-pointer p-0"
                            title="Issue type"
                        >
                            <option value="task">📋 Task</option>
                            <option value="bug">🐛 Bug</option>
                            <option value="story">📖 Story</option>
                            <option value="epic">⚡ Epic</option>
                        </select>
                    </div>

                    {{-- Title --}}
                    <div class="flex-1 min-w-0">
                        <textarea
                            x-model="selectedTask.title"
                            @blur="updateTask(selectedTask, {title: selectedTask.title})"
                            @keydown.enter.prevent="$event.target.blur()"
                            rows="1"
                            class="w-full text-base font-semibold text-white/90 bg-transparent border-0 focus:outline-none focus:ring-0 p-0 leading-snug resize-none"
                            style="field-sizing: content"
                        ></textarea>
                        <div class="text-[10px] text-white/25 mt-0.5" x-text="selectedTask.task_list?.name ?? ''"></div>
                    </div>

                    {{-- Close --}}
                    <button @click="selectedTask = null" class="text-white/30 hover:text-white/60 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Tab bar --}}
                <div class="flex border-b border-white/5 px-5 shrink-0">
                    <button
                        @click="activeTab = 'details'"
                        class="px-4 py-2.5 text-xs font-medium transition-colors -mb-px"
                        :class="activeTab === 'details' ? 'border-b-2 border-amber-400 text-amber-400' : 'text-white/35 hover:text-white/60'"
                    >Details</button>
                    <button
                        @click="loadActivity()"
                        class="px-4 py-2.5 text-xs font-medium transition-colors -mb-px"
                        :class="activeTab === 'activity' ? 'border-b-2 border-amber-400 text-amber-400' : 'text-white/35 hover:text-white/60'"
                    >Activity</button>
                    <button
                        @click="loadTime()"
                        class="px-4 py-2.5 text-xs font-medium transition-colors -mb-px"
                        :class="activeTab === 'time' ? 'border-b-2 border-amber-400 text-amber-400' : 'text-white/35 hover:text-white/60'"
                    >Time</button>
                </div>

                {{-- Scrollable body --}}
                <div class="flex-1 overflow-y-auto">

                    {{-- ===== DETAILS TAB ===== --}}
                    <div x-show="activeTab === 'details'" class="p-5 space-y-5">

                        {{-- Fields grid --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] text-white/30 block mb-1">Status</label>
                                <select x-model="selectedTask.project_status_id"
                                    @change="updateTask(selectedTask, {project_status_id: parseInt(selectedTask.project_status_id)})"
                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none">
                                    <template x-for="st in (statuses || [])" :key="st.id">
                                        <option :value="st.id" x-text="st.name" :selected="st.id == selectedTask.project_status_id"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-white/30 block mb-1">Priority</label>
                                <select x-model="selectedTask.priority" @change="updateTask(selectedTask, {priority: selectedTask.priority})"
                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none">
                                    <option value="none">None</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-white/30 block mb-1">Assignee</label>
                                <select x-model="selectedTask.assignee_id" @change="updateTask(selectedTask, {assignee_id: selectedTask.assignee_id})"
                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none">
                                    <option value="">Unassigned</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member['id'] }}">{{ $member['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-white/30 block mb-1">Due date</label>
                                <input type="date" x-model="selectedTask.due_date" @change="updateTask(selectedTask, {due_date: selectedTask.due_date})"
                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none"/>
                            </div>
                            <div>
                                <label class="text-[10px] text-white/30 block mb-1">Milestone</label>
                                <select x-model="selectedTask.milestone_id" @change="updateTask(selectedTask, {milestone_id: selectedTask.milestone_id})"
                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none">
                                    <option value="">No milestone</option>
                                    @foreach($milestones as $ms)
                                        <option value="{{ $ms['id'] }}">{{ $ms['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] text-white/30 block mb-1">Story points</label>
                                <input type="number" min="0" max="999" x-model="selectedTask.story_points"
                                    @blur="updateTask(selectedTask, {story_points: selectedTask.story_points})"
                                    placeholder="—"
                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none"/>
                            </div>
                        </div>

                        {{-- Labels --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="text-[10px] text-white/30">Labels</label>
                                <button @click="showLabelPicker = !showLabelPicker" class="text-[10px] text-amber-400/70 hover:text-amber-400">+ Add</button>
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="label in (selectedTask.labels || [])" :key="label.id">
                                    <span class="flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full font-medium text-white/80 cursor-pointer hover:opacity-70"
                                        :style="`background: ${label.color}30; border: 1px solid ${label.color}60`"
                                        @click="toggleLabel(label.id)"
                                    >
                                        <span class="w-2 h-2 rounded-full" :style="`background: ${label.color}`"></span>
                                        <span x-text="label.name"></span>
                                    </span>
                                </template>
                                <span x-show="(selectedTask.labels || []).length === 0" class="text-[10px] text-white/20">No labels</span>
                            </div>

                            {{-- Label picker dropdown --}}
                            <div x-show="showLabelPicker" x-cloak class="mt-2 p-2 bg-neutral-800 border border-white/10 rounded-xl space-y-1 max-h-40 overflow-y-auto">
                                @foreach($labels as $label)
                                <button
                                    class="flex items-center gap-2 w-full px-2 py-1.5 rounded-lg hover:bg-white/5 text-xs text-white/70 transition-colors"
                                    x-on:click="toggleLabel({{ $label->id }})"
                                    :class="(selectedTask.labels || []).some(l => l.id === {{ $label->id }}) ? 'bg-white/5' : ''"
                                >
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background: {{ $label->color }}"></span>
                                    <span>{{ $label->name }}</span>
                                    <svg x-show="(selectedTask.labels || []).some(l => l.id === {{ $label->id }})" class="w-3 h-3 ml-auto text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                @endforeach
                                @if(collect($labels)->isEmpty())
                                <p class="text-[10px] text-white/25 text-center py-2">No labels yet. Create labels in the Overview tab.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Custom Fields --}}
                        <template x-if="(customFields || []).length > 0">
                            <div>
                                <label class="text-[10px] text-white/30 block mb-1.5">Custom Fields</label>
                                <div class="space-y-2">
                                    <template x-for="cf in customFields" :key="cf.id">
                                        <div>
                                            <label class="text-[9px] text-white/25 block mb-0.5" x-text="cf.name"></label>
                                            {{-- Text --}}
                                            <template x-if="cf.type === 'text' || cf.type === 'url'">
                                                <input :type="cf.type === 'url' ? 'url' : 'text'"
                                                    :value="getCustomFieldValue(cf.id)"
                                                    @blur="saveCustomFieldValue(cf.id, $event.target.value)"
                                                    :placeholder="cf.type === 'url' ? 'https://...' : 'Enter value...'"
                                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none"/>
                                            </template>
                                            {{-- Number --}}
                                            <template x-if="cf.type === 'number'">
                                                <input type="number"
                                                    :value="getCustomFieldValue(cf.id)"
                                                    @blur="saveCustomFieldValue(cf.id, $event.target.value)"
                                                    placeholder="0"
                                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none"/>
                                            </template>
                                            {{-- Date --}}
                                            <template x-if="cf.type === 'date'">
                                                <input type="date"
                                                    :value="getCustomFieldValue(cf.id)"
                                                    @change="saveCustomFieldValue(cf.id, $event.target.value)"
                                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none"/>
                                            </template>
                                            {{-- Dropdown --}}
                                            <template x-if="cf.type === 'dropdown'">
                                                <select
                                                    @change="saveCustomFieldValue(cf.id, $event.target.value)"
                                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none">
                                                    <option value="">Select...</option>
                                                    <template x-for="opt in (cf.options || [])" :key="opt">
                                                        <option :value="opt" x-text="opt" :selected="getCustomFieldValue(cf.id) === opt"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            {{-- Checkbox --}}
                                            <template x-if="cf.type === 'checkbox'">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox"
                                                        :checked="getCustomFieldValue(cf.id) === '1' || getCustomFieldValue(cf.id) === 'true'"
                                                        @change="saveCustomFieldValue(cf.id, $event.target.checked ? '1' : '0')"
                                                        class="w-3.5 h-3.5 rounded accent-amber-400"/>
                                                    <span class="text-xs text-white/50">Enabled</span>
                                                </label>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Description --}}
                        <div>
                            <label class="text-[10px] text-white/30 block mb-1.5">Description</label>
                            <textarea
                                x-model="selectedTask.description"
                                @blur="updateTask(selectedTask, {description: selectedTask.description})"
                                rows="4"
                                placeholder="Add a description…"
                                class="w-full px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none placeholder-white/20 resize-none leading-relaxed"
                            ></textarea>
                        </div>

                        {{-- Subtasks --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-[10px] text-white/30">Subtasks (<span x-text="(selectedTask.subtasks || []).length"></span>)</label>
                                <button @click="addingSubtask = true" class="text-[10px] text-amber-400/70 hover:text-amber-400">+ Add</button>
                            </div>
                            <div class="space-y-1">
                                <template x-for="sub in (selectedTask.subtasks || [])" :key="sub.id">
                                    <div class="flex items-center gap-2 py-1 px-2 rounded-lg hover:bg-white/[0.03] group">
                                        <input type="checkbox" :checked="sub.is_completed" @change="toggleSubtask(sub)"
                                            class="w-3.5 h-3.5 rounded accent-amber-400 shrink-0 cursor-pointer">
                                        <span class="text-xs flex-1 text-white/60 leading-snug" :class="sub.is_completed ? 'line-through text-white/25' : ''" x-text="sub.title"></span>
                                        <span class="text-[9px] px-1.5 py-0.5 rounded-full" :class="priorityClass(sub.priority)" x-text="sub.priority !== 'none' ? sub.priority : ''" x-show="sub.priority && sub.priority !== 'none'"></span>
                                    </div>
                                </template>
                            </div>
                            <div x-show="addingSubtask" x-cloak class="mt-2">
                                <input
                                    x-ref="subtaskInput"
                                    type="text"
                                    placeholder="Subtask title…"
                                    class="w-full px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none placeholder-white/20"
                                    @keydown.enter="createSubtask($event)"
                                    @keydown.escape="addingSubtask = false"
                                    @blur="addingSubtask = false"
                                    x-effect="if (addingSubtask) $nextTick(() => $refs.subtaskInput && $refs.subtaskInput.focus())"
                                />
                            </div>
                        </div>

                        {{-- Linked tasks --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-[10px] text-white/30">Linked Issues (<span x-text="(selectedTask.links || []).length"></span>)</label>
                                <button @click="showLinkForm = !showLinkForm" class="text-[10px] text-amber-400/70 hover:text-amber-400">+ Link</button>
                            </div>
                            <div class="space-y-1.5">
                                <template x-for="link in (selectedTask.links || [])" :key="link.id">
                                    <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg bg-white/[0.03] text-xs group">
                                        <span class="text-white/25 text-[10px] w-16 shrink-0" x-text="link.type_label"></span>
                                        <span class="flex-1 text-white/60 truncate" x-text="link.other_task?.title"></span>
                                        <span class="text-[9px] px-1.5 py-0.5 rounded-full" :class="statusClass(link.other_task?.status)" x-text="link.other_task?.status?.replace('_',' ')"></span>
                                        <button @click="deleteLink(link.id)" class="text-white/20 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <div x-show="showLinkForm" x-cloak class="mt-2 space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <select x-model="linkForm.type" class="px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none">
                                        <option value="relates_to">Relates to</option>
                                        <option value="blocks">Blocks</option>
                                        <option value="blocked_by">Blocked by</option>
                                        <option value="duplicates">Duplicates</option>
                                    </select>
                                    <input x-model="linkForm.task_id" type="number" placeholder="Task ID…"
                                        class="px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none placeholder-white/20"/>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="showLinkForm = false" class="flex-1 py-1.5 rounded-lg border border-white/10 text-white/40 text-xs hover:border-white/20 transition-colors">Cancel</button>
                                    <button @click="addLink()" class="flex-1 py-1.5 rounded-lg bg-amber-500/20 text-amber-400 text-xs hover:bg-amber-500/30 transition-colors border border-amber-500/20">Add Link</button>
                                </div>
                            </div>
                        </div>

                        {{-- Attachments --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-[10px] text-white/30">Attachments (<span x-text="(selectedTask.attachments || []).length"></span>)</label>
                                <label class="text-[10px] text-amber-400/70 hover:text-amber-400 cursor-pointer">
                                    + Upload
                                    <input type="file" multiple class="sr-only" @change="uploadAttachment($event)"/>
                                </label>
                            </div>
                            <div class="space-y-1.5">
                                <template x-for="att in (selectedTask.attachments || [])" :key="att.id">
                                    <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg bg-white/[0.03] group">
                                        <svg class="w-3.5 h-3.5 text-white/30 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <a :href="att.url" target="_blank" class="flex-1 text-xs text-white/60 hover:text-white/90 truncate transition-colors" x-text="att.filename"></a>
                                        <span class="text-[10px] text-white/25" x-text="att.size_fmt"></span>
                                        <button x-show="att.is_mine" @click="deleteAttachment(att.id)" class="text-white/20 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </template>
                                <div x-show="(selectedTask.attachments || []).length === 0" class="text-[10px] text-white/20">No attachments</div>
                            </div>
                        </div>

                        {{-- Watchers --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-[10px] text-white/30">Watchers</label>
                                <button @click="toggleWatch()" class="text-[10px]"
                                    :class="selectedTask.is_watching ? 'text-amber-400' : 'text-white/30 hover:text-amber-400/70'"
                                    x-text="selectedTask.is_watching ? '✓ Watching' : '+ Watch'"
                                ></button>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <template x-for="w in (selectedTask.watchers || [])" :key="w.id">
                                    <div class="w-6 h-6 rounded-full bg-white/10 text-white/40 text-[8px] font-bold flex items-center justify-center" :title="w.name" x-text="w.name.substring(0,2).toUpperCase()"></div>
                                </template>
                                <span x-show="(selectedTask.watchers || []).length === 0" class="text-[10px] text-white/20">No watchers</span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        @if($canEdit)
                        <div class="pt-2 border-t border-white/5">
                            <button @click="$dispatch('delete-task', selectedTask); selectedTask = null" class="text-xs text-red-400/60 hover:text-red-400 transition-colors">Delete task</button>
                        </div>
                        @endif
                    </div>

                    {{-- ===== ACTIVITY TAB ===== --}}
                    <div x-show="activeTab === 'activity'" class="flex flex-col h-full">
                        <div class="flex-1 overflow-y-auto p-5 space-y-4">
                            <div x-show="loadingActivity" class="text-center py-8 text-white/25 text-xs">Loading activity…</div>
                            <template x-for="entry in activityFeed" :key="entry.id + '_' + entry.type">
                                <div class="flex gap-3">
                                    <div class="w-6 h-6 rounded-full bg-white/10 text-white/40 text-[8px] font-bold flex items-center justify-center shrink-0 mt-0.5"
                                        x-text="entry.user.name.substring(0,2).toUpperCase()"></div>
                                    <div class="flex-1 min-w-0">
                                        <div x-show="entry.type === 'activity'" class="text-xs text-white/40 leading-relaxed" x-html="entry.description"></div>
                                        <div x-show="entry.type === 'comment'" class="bg-white/[0.04] border border-white/5 rounded-xl px-3 py-2">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-[10px] font-medium text-white/60" x-text="entry.user.name"></span>
                                                <button x-show="entry.is_mine" @click="deleteComment(entry.id)" class="text-[10px] text-white/20 hover:text-red-400">Delete</button>
                                            </div>
                                            <p class="text-xs text-white/60 leading-relaxed" x-text="entry.description"></p>
                                        </div>
                                        <div class="text-[10px] text-white/20 mt-1" x-text="entry.created_at"></div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="!loadingActivity && activityFeed.length === 0" class="text-center py-8 text-white/20 text-xs">No activity yet</div>
                        </div>
                        {{-- Comment input --}}
                        <div class="border-t border-white/5 p-4 shrink-0">
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    x-model="newComment"
                                    placeholder="Add a comment…"
                                    class="flex-1 px-3 py-2 rounded-xl bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none placeholder-white/20"
                                    @keydown.enter="addComment()"
                                />
                                <button @click="addComment()" class="px-3 py-2 rounded-xl bg-amber-500/20 text-amber-400 text-xs hover:bg-amber-500/30 transition-colors border border-amber-500/20">Send</button>
                            </div>
                        </div>
                    </div>

                    {{-- ===== TIME TAB ===== --}}
                    <div x-show="activeTab === 'time'" class="p-5 space-y-5">
                        <div x-show="loadingTime" class="text-center py-8 text-white/25 text-xs">Loading…</div>

                        {{-- Progress bar --}}
                        <div x-show="!loadingTime">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] text-white/40">Time logged</span>
                                <span class="text-[10px] text-white/40">
                                    <span x-text="timeData.total_logged || 0"></span>h
                                    <template x-if="timeData.estimated">
                                        <span> / <span x-text="timeData.estimated"></span>h estimated</span>
                                    </template>
                                </span>
                            </div>
                            <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400/60 rounded-full transition-all"
                                    :style="'width: ' + Math.min(100, timeData.estimated ? (timeData.total_logged / timeData.estimated) * 100 : 0) + '%'">
                                </div>
                            </div>
                        </div>

                        {{-- Log time form --}}
                        <div x-show="!loadingTime" class="bg-white/[0.03] border border-white/5 rounded-xl p-4 space-y-3">
                            <h4 class="text-xs font-medium text-white/50">Log time</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] text-white/30 block mb-1">Hours <span class="text-red-400">*</span></label>
                                    <input type="number" step="0.25" min="0.25" max="99" x-model="timeForm.hours" placeholder="e.g. 1.5"
                                        class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none"/>
                                </div>
                                <div>
                                    <label class="text-[10px] text-white/30 block mb-1">Date</label>
                                    <input type="date" x-model="timeForm.logged_at"
                                        class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none"/>
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-white/30 block mb-1">Notes</label>
                                <input type="text" x-model="timeForm.notes" placeholder="What did you work on?"
                                    class="w-full px-2 py-1.5 rounded-lg bg-white/5 border border-white/10 text-white/70 text-xs focus:ring-1 focus:ring-amber-500/40 focus:outline-none placeholder-white/20"/>
                            </div>
                            <button @click="logTime()" class="w-full py-2 rounded-xl bg-amber-500/20 text-amber-400 text-xs font-medium hover:bg-amber-500/30 transition-colors border border-amber-500/20">Log Time</button>
                        </div>

                        {{-- Time log list --}}
                        <div x-show="!loadingTime && (timeData.logs || []).length > 0" class="space-y-2">
                            <h4 class="text-[10px] text-white/30">History</h4>
                            <template x-for="log in (timeData.logs || [])" :key="log.id">
                                <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-white/[0.03] group">
                                    <div class="w-5 h-5 rounded-full bg-white/10 text-[8px] text-white/40 font-bold flex items-center justify-center shrink-0" x-text="log.user.name.substring(0,2).toUpperCase()"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs text-white/60"><span class="font-medium text-amber-400/80" x-text="log.hours + 'h'"></span> <span x-show="log.notes" class="text-white/40" x-text="'— ' + log.notes"></span></div>
                                        <div class="text-[10px] text-white/25" x-text="log.user.name + ' · ' + log.logged_at"></div>
                                    </div>
                                    <button x-show="log.is_mine" @click="deleteTimeLog(log.id)" class="text-white/20 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>{{-- end scrollable body --}}
            </div>
        </template>
    </div>
</div>

<script>
function taskDetailPanel() {
    return {
        activeTab: 'details',
        activityFeed: [],
        loadingActivity: false,
        activityLoaded: false,
        timeData: {},
        loadingTime: false,
        timeLoaded: false,
        newComment: '',
        addingSubtask: false,
        showLabelPicker: false,
        showLinkForm: false,
        linkForm: { type: 'relates_to', task_id: '' },
        timeForm: { hours: '', notes: '', logged_at: new Date().toISOString().split('T')[0] },

        init() {
            // Watch for task change — reset tabs
            this.$watch('selectedTask', (val) => {
                if (val) {
                    this.activeTab = 'details';
                    this.activityLoaded = false;
                    this.timeLoaded = false;
                    this.activityFeed = [];
                    this.timeData = {};
                    this.showLabelPicker = false;
                    this.showLinkForm = false;
                }
            });

            // Listen for delete-task event
            window.addEventListener('delete-task', (e) => {
                if (typeof deleteTask === 'function') deleteTask(e.detail);
            });
        },

        async loadActivity() {
            this.activeTab = 'activity';
            if (this.activityLoaded || !this.selectedTask) return;
            this.loadingActivity = true;
            const res = await this.api('GET', `/api/project-tasks/${this.selectedTask.id}/activity`);
            if (res) { this.activityFeed = res; this.activityLoaded = true; }
            this.loadingActivity = false;
        },

        async loadTime() {
            this.activeTab = 'time';
            if (this.timeLoaded || !this.selectedTask) return;
            this.loadingTime = true;
            const res = await this.api('GET', `/api/project-tasks/${this.selectedTask.id}/time-logs`);
            if (res) { this.timeData = res; this.timeLoaded = true; }
            this.loadingTime = false;
        },

        async addComment() {
            if (!this.newComment.trim() || !this.selectedTask) return;
            const res = await this.api('POST', `/api/project-tasks/${this.selectedTask.id}/comments`, { content: this.newComment });
            if (res) {
                this.activityFeed.unshift({
                    id: res.id, type: 'comment', description: res.content,
                    user: res.user, created_at: 'just now', is_mine: true, created_ts: Date.now(),
                });
                this.newComment = '';
            }
        },

        async deleteComment(id) {
            const res = await this.api('DELETE', `/api/project-comments/${id}`);
            if (res) this.activityFeed = this.activityFeed.filter(e => !(e.type === 'comment' && e.id === id));
        },

        async toggleLabel(labelId) {
            if (!this.selectedTask) return;
            const res = await this.api('POST', `/api/project-tasks/${this.selectedTask.id}/labels`, { label_id: labelId });
            if (res) {
                this.selectedTask.labels = res.labels;
                // Propagate to parent Alpine state
                this.$dispatch('task-labels-updated', { taskId: this.selectedTask.id, labels: res.labels });
            }
        },

        async toggleWatch() {
            if (!this.selectedTask) return;
            const res = await this.api('POST', `/api/project-tasks/${this.selectedTask.id}/watch`);
            if (res !== null) this.selectedTask.is_watching = res.watching;
        },

        async toggleSubtask(sub) {
            sub.is_completed = !sub.is_completed;
            await this.api('PUT', `/api/project-tasks/${sub.id}`, { is_completed: sub.is_completed });
        },

        async createSubtask(event) {
            const title = event.target.value.trim();
            if (!title || !this.selectedTask) return;
            event.target.value = '';
            this.addingSubtask = false;
            const taskListId = this.selectedTask.task_list_id;
            const res = await this.api('POST', `/api/projects/{{ $project->id }}/tasks`, {
                title, task_list_id: taskListId, parent_task_id: this.selectedTask.id,
            });
            if (res?.task) {
                if (!this.selectedTask.subtasks) this.selectedTask.subtasks = [];
                this.selectedTask.subtasks.push(res.task);
            }
        },

        async addLink() {
            if (!this.linkForm.task_id || !this.selectedTask) return;
            const res = await this.api('POST', `/api/project-tasks/${this.selectedTask.id}/links`, {
                linked_task_id: parseInt(this.linkForm.task_id), type: this.linkForm.type,
            });
            if (res?.id) {
                if (!this.selectedTask.links) this.selectedTask.links = [];
                this.selectedTask.links.push(res);
                this.linkForm.task_id = '';
                this.showLinkForm = false;
            }
        },

        async deleteLink(linkId) {
            const res = await this.api('DELETE', `/api/project-task-links/${linkId}`);
            if (res) this.selectedTask.links = this.selectedTask.links.filter(l => l.id !== linkId);
        },

        async uploadAttachment(event) {
            if (!this.selectedTask) return;
            const files = Array.from(event.target.files);
            for (const file of files) {
                const fd = new FormData();
                fd.append('file', file);
                try {
                    const r = await fetch(`/api/project-tasks/${this.selectedTask.id}/attachments`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        body: fd,
                    });
                    if (r.ok) {
                        const att = await r.json();
                        if (!this.selectedTask.attachments) this.selectedTask.attachments = [];
                        this.selectedTask.attachments.push(att);
                    }
                } catch {}
            }
            event.target.value = '';
        },

        async deleteAttachment(id) {
            const res = await this.api('DELETE', `/api/project-attachments/${id}`);
            if (res) this.selectedTask.attachments = this.selectedTask.attachments.filter(a => a.id !== id);
        },

        async logTime() {
            if (!this.timeForm.hours || !this.selectedTask) return;
            const res = await this.api('POST', `/api/project-tasks/${this.selectedTask.id}/time-logs`, this.timeForm);
            if (res) {
                if (!this.timeData.logs) this.timeData.logs = [];
                this.timeData.logs.unshift(res.log);
                this.timeData.total_logged = res.total_logged;
                this.timeForm.hours = '';
                this.timeForm.notes = '';
            }
        },

        async deleteTimeLog(id) {
            const res = await this.api('DELETE', `/api/project-time-logs/${id}`);
            if (res) {
                this.timeData.logs = this.timeData.logs.filter(l => l.id !== id);
                this.timeData.total_logged = res.total_logged;
            }
        },

        priorityClass(p) {
            const m = { critical: 'bg-red-500/20 text-red-400', high: 'bg-orange-500/20 text-orange-400', medium: 'bg-amber-500/20 text-amber-400', low: 'bg-blue-500/20 text-blue-400' };
            return m[p] ?? '';
        },

        statusClass(s) {
            const m = { open: 'bg-white/10 text-white/40', in_progress: 'bg-blue-500/20 text-blue-400', completed: 'bg-green-500/20 text-green-400', deferred: 'bg-white/5 text-white/20' };
            return m[s] ?? 'bg-white/10 text-white/40';
        },

        getCustomFieldValue(fieldId) {
            const vals = this.selectedTask?.custom_field_values || [];
            const entry = vals.find(v => v.field_id === fieldId);
            return entry?.value ?? '';
        },

        async saveCustomFieldValue(fieldId, value) {
            if (!this.selectedTask) return;
            await this.api('PUT', `/api/project-tasks/${this.selectedTask.id}/custom-fields`, {
                field_id: fieldId,
                value: value || null,
            });
            // Update local state
            if (!this.selectedTask.custom_field_values) this.selectedTask.custom_field_values = [];
            const idx = this.selectedTask.custom_field_values.findIndex(v => v.field_id === fieldId);
            if (idx !== -1) {
                this.selectedTask.custom_field_values[idx].value = value;
            } else {
                this.selectedTask.custom_field_values.push({ field_id: fieldId, value });
            }
        },

        async api(method, url, data = null) {
            try {
                const opts = { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } };
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
