{{-- Task Detail Slide-over Panel --}}
{{-- This component is used inside the list view's Alpine.js context (oppListView) --}}
{{-- It expects `selectedTask`, `updateSelectedTask`, `closeTaskPanel`, `deleteTask`, `toggleComplete`, and `csrfToken` to be available --}}

<div class="h-full flex flex-col">

    {{-- Panel Header --}}
    <div class="flex items-center justify-between px-5 py-3 border-b border-opp-border shrink-0">
        <div class="flex items-center gap-2">
            <button @click="toggleComplete(selectedTask)"
                    class="shrink-0">
                <div class="w-5 h-5 rounded-full border-2 transition-all flex items-center justify-center"
                     :class="selectedTask.completed ? 'bg-opp-accent border-opp-accent' : 'border-opp-muted hover:border-opp-accent'">
                    <svg x-show="selectedTask.completed" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </button>
            <span class="text-xs px-2 py-0.5 rounded-full"
                  :class="selectedTask.completed ? 'bg-opp-accent/10 text-opp-accent' : 'bg-opp-border text-opp-muted'"
                  x-text="selectedTask.completed ? 'Complete' : 'Incomplete'"></span>
        </div>
        <div class="flex items-center gap-1">
            <button @click="deleteTask(selectedTask)" class="p-1.5 rounded-lg text-opp-muted hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Delete task">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
            <button @click="closeTaskPanel" class="p-1.5 rounded-lg text-opp-muted hover:text-opp-text hover:bg-opp-hover transition-colors" title="Close">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Panel Body --}}
    <div class="flex-1 overflow-y-auto">

        {{-- Task Title --}}
        <div class="px-5 pt-4 pb-2">
            <input type="text"
                   :value="selectedTask.title"
                   @change="updateSelectedTask('title', $event.target.value)"
                   class="w-full bg-transparent text-lg font-semibold text-opp-text outline-none border-b border-transparent hover:border-opp-border focus:border-opp-accent transition-colors pb-1"
                   :class="selectedTask.completed ? 'line-through text-opp-muted' : ''">
        </div>

        {{-- Task Metadata --}}
        <div class="px-5 py-3 space-y-3">

            {{-- Assignee --}}
            <div class="flex items-center gap-3">
                <span class="text-xs text-opp-muted w-20 shrink-0">Assignee</span>
                <div x-data="{ editing: false }" class="flex-1">
                    <template x-if="!editing">
                        <button @click="editing = true" class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-opp-hover transition-colors w-full text-left">
                            <template x-if="selectedTask.assignee">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold"
                                         :style="'background:' + (selectedTask.assignee_color || '#14B8A6') + '33; color:' + (selectedTask.assignee_color || '#14B8A6')"
                                         x-text="selectedTask.assignee_initials"></div>
                                    <span class="text-sm text-opp-text" x-text="selectedTask.assignee"></span>
                                </div>
                            </template>
                            <template x-if="!selectedTask.assignee">
                                <span class="text-sm text-opp-muted">No assignee</span>
                            </template>
                        </button>
                    </template>
                    <template x-if="editing">
                        <input type="text"
                               :value="selectedTask.assignee || ''"
                               @blur="updateSelectedTask('assignee', $event.target.value); editing = false"
                               @keydown.enter="updateSelectedTask('assignee', $event.target.value); editing = false"
                               @keydown.escape="editing = false"
                               x-init="$nextTick(() => $el.focus())"
                               placeholder="Type a name..."
                               class="w-full bg-opp-bg border border-opp-border rounded-lg px-3 py-1.5 text-sm text-opp-text placeholder-opp-muted outline-none focus:border-opp-accent/50">
                    </template>
                </div>
            </div>

            {{-- Due Date --}}
            <div class="flex items-center gap-3">
                <span class="text-xs text-opp-muted w-20 shrink-0">Due date</span>
                <div class="flex-1">
                    <input type="date"
                           :value="selectedTask.due_date_raw || ''"
                           @change="updateSelectedTask('due_date', $event.target.value); updateSelectedTask('due_date_raw', $event.target.value)"
                           class="bg-opp-bg border border-opp-border rounded-lg px-3 py-1.5 text-sm text-opp-text outline-none focus:border-opp-accent/50 transition-colors">
                </div>
            </div>

            {{-- Priority --}}
            <div class="flex items-center gap-3">
                <span class="text-xs text-opp-muted w-20 shrink-0">Priority</span>
                <div class="flex-1">
                    <select :value="selectedTask.priority || ''"
                            @change="updateSelectedTask('priority', $event.target.value)"
                            class="bg-opp-bg border border-opp-border rounded-lg px-3 py-1.5 text-sm text-opp-text outline-none focus:border-opp-accent/50 transition-colors">
                        <option value="">No priority</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            {{-- Status --}}
            <div class="flex items-center gap-3">
                <span class="text-xs text-opp-muted w-20 shrink-0">Status</span>
                <div class="flex-1">
                    <select :value="selectedTask.status || 'todo'"
                            @change="updateSelectedTask('status', $event.target.value)"
                            class="bg-opp-bg border border-opp-border rounded-lg px-3 py-1.5 text-sm text-opp-text outline-none focus:border-opp-accent/50 transition-colors">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="done">Done</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="border-t border-opp-border mx-5"></div>

        {{-- Description --}}
        <div class="px-5 py-4">
            <h4 class="text-xs font-semibold text-opp-muted uppercase tracking-wider mb-2">Description</h4>
            <textarea :value="selectedTask.description || ''"
                      @blur="updateSelectedTask('description', $event.target.value)"
                      placeholder="Add a description..."
                      rows="3"
                      class="w-full bg-opp-bg border border-opp-border rounded-lg px-3 py-2 text-sm text-opp-text placeholder-opp-muted outline-none focus:border-opp-accent/50 transition-colors resize-none"></textarea>
        </div>

        <div class="border-t border-opp-border mx-5"></div>

        {{-- Subtasks --}}
        <div class="px-5 py-4" x-data="{ newSubtask: '' }">
            <h4 class="text-xs font-semibold text-opp-muted uppercase tracking-wider mb-3">
                Subtasks
                <span x-show="selectedTask.subtasks && selectedTask.subtasks.length"
                      class="text-opp-accent ml-1"
                      x-text="selectedTask.subtasks.filter(s => s.completed).length + '/' + selectedTask.subtasks.length"></span>
            </h4>

            {{-- Subtask progress --}}
            <div x-show="selectedTask.subtasks && selectedTask.subtasks.length" class="mb-3">
                <div class="h-1.5 bg-opp-border rounded-full overflow-hidden">
                    <div class="h-full rounded-full opp-gradient transition-all"
                         :style="'width:' + (selectedTask.subtasks.length ? (selectedTask.subtasks.filter(s => s.completed).length / selectedTask.subtasks.length * 100) : 0) + '%'"></div>
                </div>
            </div>

            {{-- Subtask List --}}
            <div class="space-y-1 mb-2">
                <template x-for="(sub, idx) in (selectedTask.subtasks || [])" :key="sub.id || idx">
                    <div class="flex items-center gap-2 py-1 group/sub">
                        <button @click="sub.completed = !sub.completed" class="shrink-0">
                            <div class="w-4 h-4 rounded border transition-all flex items-center justify-center"
                                 :class="sub.completed ? 'bg-opp-accent border-opp-accent' : 'border-opp-muted/40 hover:border-opp-accent'">
                                <svg x-show="sub.completed" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </button>
                        <span class="text-sm flex-1" :class="sub.completed ? 'line-through text-opp-muted' : 'text-opp-text'" x-text="sub.title"></span>
                        <button @click="selectedTask.subtasks.splice(idx, 1)"
                                class="p-0.5 rounded text-opp-muted/0 group-hover/sub:text-opp-muted hover:!text-red-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Add Subtask --}}
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded border border-opp-muted/20 shrink-0"></div>
                <input x-model="newSubtask"
                       @keydown.enter="if (newSubtask.trim()) { if (!selectedTask.subtasks) selectedTask.subtasks = []; selectedTask.subtasks.push({ id: Date.now(), title: newSubtask.trim(), completed: false }); newSubtask = ''; }"
                       placeholder="Add subtask..."
                       class="flex-1 bg-transparent text-sm text-opp-text placeholder-opp-muted outline-none">
            </div>
        </div>

        <div class="border-t border-opp-border mx-5"></div>

        {{-- Attachments --}}
        <div class="px-5 py-4" x-data="{ attachments: selectedTask.attachments || [] }">
            <h4 class="text-xs font-semibold text-opp-muted uppercase tracking-wider mb-3">Attachments</h4>

            <div class="space-y-2 mb-3">
                <template x-for="(file, idx) in attachments" :key="idx">
                    <div class="flex items-center gap-2 px-3 py-2 bg-opp-bg rounded-lg border border-opp-border group/file">
                        <svg class="w-4 h-4 text-opp-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        <span class="text-sm text-opp-text flex-1 truncate" x-text="file.name"></span>
                        <span class="text-[10px] text-opp-muted" x-text="file.size"></span>
                        <button @click="attachments.splice(idx, 1)"
                                class="p-0.5 rounded text-opp-muted/0 group-hover/file:text-opp-muted hover:!text-red-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            <button class="flex items-center gap-2 px-3 py-2 rounded-lg border border-dashed border-opp-border hover:border-opp-accent/30 text-sm text-opp-muted hover:text-opp-accent transition-colors w-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Attach a file
            </button>
        </div>

        <div class="border-t border-opp-border mx-5"></div>

        {{-- Comments --}}
        <div class="px-5 py-4" x-data="{ newComment: '' }">
            <h4 class="text-xs font-semibold text-opp-muted uppercase tracking-wider mb-3">Comments</h4>

            <div class="space-y-3 mb-4">
                <template x-for="(comment, idx) in (selectedTask.comments || [])" :key="comment.id || idx">
                    <div class="flex items-start gap-2.5">
                        <div class="w-7 h-7 rounded-full bg-opp-accent/20 flex items-center justify-center text-[10px] font-bold text-opp-accent shrink-0 mt-0.5"
                             x-text="(comment.author || 'U').charAt(0)"></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="text-sm font-medium text-opp-text" x-text="comment.author || 'Unknown'"></span>
                                <span class="text-[10px] text-opp-muted" x-text="comment.time || 'just now'"></span>
                            </div>
                            <p class="text-sm text-opp-text-dim leading-relaxed" x-text="comment.text"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Add Comment --}}
            <div class="flex items-start gap-2.5">
                <div class="w-7 h-7 rounded-full opp-gradient flex items-center justify-center text-[10px] font-bold text-white shrink-0 mt-0.5">
                    {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="flex-1">
                    <textarea x-model="newComment"
                              placeholder="Write a comment..."
                              rows="2"
                              class="w-full bg-opp-bg border border-opp-border rounded-lg px-3 py-2 text-sm text-opp-text placeholder-opp-muted outline-none focus:border-opp-accent/50 transition-colors resize-none"></textarea>
                    <div class="flex justify-end mt-1.5" x-show="newComment.trim()">
                        <button @click="if (!selectedTask.comments) selectedTask.comments = []; selectedTask.comments.push({ id: Date.now(), author: '{{ auth()->user()->name ?? 'You' }}', text: newComment.trim(), time: 'just now' }); newComment = ''"
                                class="px-3 py-1.5 bg-opp-accent hover:bg-opp-accent-light text-white text-xs font-medium rounded-lg transition-colors">
                            Comment
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-opp-border mx-5"></div>

        {{-- Activity Log --}}
        <div class="px-5 py-4 pb-8">
            <h4 class="text-xs font-semibold text-opp-muted uppercase tracking-wider mb-3">Activity</h4>

            <div class="space-y-3">
                <div class="flex items-start gap-2.5 text-xs">
                    <div class="w-1.5 h-1.5 rounded-full bg-opp-accent mt-1.5 shrink-0"></div>
                    <div>
                        <span class="text-opp-text-dim">Task created</span>
                        <span class="text-opp-muted ml-1">3 days ago</span>
                    </div>
                </div>
                <div class="flex items-start gap-2.5 text-xs" x-show="selectedTask.assignee">
                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400 mt-1.5 shrink-0"></div>
                    <div>
                        <span class="text-opp-text-dim">Assigned to <span class="text-opp-text" x-text="selectedTask.assignee"></span></span>
                        <span class="text-opp-muted ml-1">2 days ago</span>
                    </div>
                </div>
                <div class="flex items-start gap-2.5 text-xs" x-show="selectedTask.due_date">
                    <div class="w-1.5 h-1.5 rounded-full bg-yellow-400 mt-1.5 shrink-0"></div>
                    <div>
                        <span class="text-opp-text-dim">Due date set to <span class="text-opp-text" x-text="selectedTask.due_date"></span></span>
                        <span class="text-opp-muted ml-1">2 days ago</span>
                    </div>
                </div>
                <div class="flex items-start gap-2.5 text-xs" x-show="selectedTask.completed">
                    <div class="w-1.5 h-1.5 rounded-full bg-green-400 mt-1.5 shrink-0"></div>
                    <div>
                        <span class="text-opp-text-dim">Marked as complete</span>
                        <span class="text-opp-muted ml-1">just now</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
