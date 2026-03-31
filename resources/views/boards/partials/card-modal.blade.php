<!-- Card Detail Modal -->
<div
    x-show="showCardModal"
    style="display: none"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="if(showCardModal) closeCardModal()"
    class="fixed inset-0 z-[60] overflow-y-auto"
    x-cloak
>
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/60" @click="closeCardModal()"></div>

    <!-- Modal container -->
    <div class="relative min-h-full flex items-start justify-center p-4 sm:p-6 pt-12 sm:pt-20">
        <div
            x-show="showCardModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="relative bg-gray-100 dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-[768px] mb-8"
            @click.stop
        >
            <!-- Loading state -->
            <template x-if="!activeCardFull">
                <div class="p-16 text-center">
                    <div class="inline-flex items-center gap-3 text-gray-400">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span class="text-sm">Loading card...</span>
                    </div>
                </div>
            </template>

            <template x-if="activeCardFull">
                <div>
                    <!-- Cover area -->
                    <template x-if="activeCardFull.cover_color">
                        <div class="h-28 rounded-t-2xl relative" :style="'background:' + activeCardFull.cover_color">
                            <button @click="closeCardModal()" class="absolute top-3 right-3 p-1.5 rounded-full bg-black/20 hover:bg-black/40 text-white/80 hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>

                    <!-- Close button (when no cover) -->
                    <template x-if="!activeCardFull.cover_color">
                        <button @click="closeCardModal()" class="absolute top-3 right-3 p-1.5 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 z-10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </template>

                    <!-- Main content: 2-column layout -->
                    <div class="flex flex-col md:flex-row gap-0">
                        {{-- ======= LEFT COLUMN: Card Content ======= --}}
                        <div class="flex-1 p-4 sm:p-6 min-w-0 space-y-5">

                            <!-- Header: Labels row -->
                            <div class="flex flex-wrap gap-1" x-show="activeCardFull.labels?.length">
                                <template x-for="label in activeCardFull.labels" :key="label.id">
                                    <span class="px-3 py-1 rounded text-xs font-semibold text-white min-w-[48px] text-center" :style="'background:' + getLabelHex(label.color)" x-text="label.name || label.color"></span>
                                </template>
                            </div>

                            <!-- Title -->
                            <div x-data="{ editing: false }">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-gray-400 mt-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/></svg>
                                    <div class="flex-1 min-w-0">
                                        <template x-if="!editing">
                                            <h2 class="text-xl font-bold text-gray-900 dark:text-white cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 rounded px-1 py-0.5 -mx-1 transition-colors"
                                                @click="if(canEdit) editing = true" x-text="activeCardFull.title"></h2>
                                        </template>
                                        <template x-if="editing">
                                            <textarea x-model="activeCardFull.title" rows="2"
                                                @blur="editing = false; apiCall(`/api/cards/${activeCardFull.id}`, 'PUT', { title: activeCardFull.title }); updateCardInList(activeCardFull.id, { title: activeCardFull.title })"
                                                @keydown.enter.prevent="editing = false; apiCall(`/api/cards/${activeCardFull.id}`, 'PUT', { title: activeCardFull.title }); updateCardInList(activeCardFull.id, { title: activeCardFull.title })"
                                                @keydown.escape="editing = false"
                                                class="w-full text-xl font-bold rounded-lg border-2 border-primary-500 px-2 py-1 focus:outline-none dark:bg-gray-800 dark:text-white resize-none"></textarea>
                                        </template>
                                        <p class="text-xs text-gray-500 mt-1">in list <span class="font-semibold underline decoration-dotted" x-text="activeCardFull.board_list?.name"></span></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Info row: Dates / Watch / Vote -->
                            <div class="flex flex-wrap items-center gap-2">
                                <!-- Start date -->
                                <template x-if="activeCardFull.start_date">
                                    <div class="text-xs bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1.5 rounded">
                                        <span class="font-semibold text-gray-500 uppercase text-[10px] block leading-none mb-0.5">Start date</span>
                                        <span x-text="new Date(activeCardFull.start_date).toLocaleDateString('en', {month:'short', day:'numeric'})"></span>
                                    </div>
                                </template>
                                <!-- Due date -->
                                <template x-if="activeCardFull.due_date">
                                    <div class="text-xs px-2.5 py-1.5 rounded"
                                        :class="{
                                            'bg-danger-500 text-white': activeCardFull.due_status === 'overdue',
                                            'bg-sunny-400 text-sunny-900': activeCardFull.due_status === 'due_soon',
                                            'bg-success-500 text-white': activeCardFull.due_status === 'complete',
                                            'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400': !activeCardFull.due_status || activeCardFull.due_status === 'normal',
                                        }">
                                        <span class="font-semibold uppercase text-[10px] block leading-none mb-0.5" :class="activeCardFull.due_status === 'overdue' || activeCardFull.due_status === 'complete' ? 'text-white/70' : 'text-gray-500'">Due date</span>
                                        <span x-text="new Date(activeCardFull.due_date).toLocaleDateString('en', {month:'short', day:'numeric'})"></span>
                                    </div>
                                </template>

                                <!-- Watch -->
                                <div x-data="{ watching: false }" x-init="watching = activeCardFull.watchers?.some(w => w.id == {{ auth()->id() }}) || false">
                                    <button @click="apiCall(`/api/cards/${activeCardFull.id}/watch`, 'POST').then(d => watching = d.watching)"
                                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded text-xs font-medium transition-colors"
                                        :class="watching ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400' : 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-700'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span x-text="watching ? 'Watching' : 'Watch'"></span>
                                    </button>
                                </div>

                                <!-- Vote -->
                                <div x-data="{ voted: false, voteCount: 0 }" x-init="voted = activeCardFull.votes?.some(v => v.user_id == {{ auth()->id() }}) || false; voteCount = activeCardFull.votes?.length || 0">
                                    <button @click="apiCall(`/api/cards/${activeCardFull.id}/vote`, 'POST').then(d => { voted = d.voted; voteCount = d.vote_count })"
                                        class="flex items-center gap-1.5 px-2.5 py-1.5 rounded text-xs font-medium transition-colors"
                                        :class="voted ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-400' : 'bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-300 dark:hover:bg-gray-700'">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M2 20h2V8H2v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L15.17 1 7.59 8.59C7.22 8.95 7 9.45 7 10v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                                        <span x-text="voteCount"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- Description -->
                            <div x-data="{ editing: false }">
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                    Description
                                </h3>
                                <template x-if="!editing">
                                    <div @click="if(canEdit) editing = true"
                                        class="min-h-[56px] px-3 py-2.5 rounded-lg bg-gray-200 dark:bg-gray-800 text-sm text-gray-600 dark:text-gray-400 cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors prose prose-sm max-w-none dark:prose-invert [&_p]:my-1"
                                        x-html="activeCardFull.description ? DOMPurify.sanitize(marked.parse(activeCardFull.description)) : 'Add a more detailed description...'">
                                    </div>
                                </template>
                                <template x-if="editing">
                                    <div>
                                        <textarea x-model="activeCardFull.description" rows="5"
                                            class="w-full rounded-lg border-2 border-primary-500 px-3 py-2 text-sm resize-y dark:bg-gray-800 dark:text-gray-200 focus:outline-none"
                                            placeholder="Add a more detailed description..."></textarea>
                                        <div class="flex gap-2 mt-2">
                                            <button @click="editing = false; apiCall(`/api/cards/${activeCardFull.id}`, 'PUT', { description: activeCardFull.description })" class="px-4 py-1.5 rounded-lg gradient-primary text-white text-sm font-medium">Save</button>
                                            <button @click="editing = false" class="px-3 py-1.5 rounded-lg text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 text-sm">Cancel</button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Checklists -->
                            <template x-for="checklist in activeCardFull.checklists || []" :key="checklist.id">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            <span x-text="checklist.name"></span>
                                        </h3>
                                        <button @click="if(confirm('Delete checklist?')) { apiCall(`/api/checklists/${checklist.id}`, 'DELETE'); activeCardFull.checklists = activeCardFull.checklists.filter(c => c.id !== checklist.id) }" class="text-xs text-gray-400 hover:text-danger-500 px-2 py-1 rounded hover:bg-gray-200 dark:hover:bg-gray-800">Delete</button>
                                    </div>
                                    <!-- Progress bar -->
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] text-gray-400 w-7 text-right" x-text="Math.round((checklist.items?.filter(i => i.is_checked).length || 0) / Math.max(checklist.items?.length || 1, 1) * 100) + '%'"></span>
                                        <div class="flex-1 h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-300"
                                                :class="(checklist.items?.filter(i => i.is_checked).length || 0) === (checklist.items?.length || 0) && checklist.items?.length > 0 ? 'bg-success-500' : 'bg-primary-500'"
                                                :style="'width:' + Math.round((checklist.items?.filter(i => i.is_checked).length || 0) / Math.max(checklist.items?.length || 1, 1) * 100) + '%'"></div>
                                        </div>
                                    </div>
                                    <!-- Items -->
                                    <template x-for="item in checklist.items || []" :key="item.id">
                                        <div class="flex items-start gap-2 py-1 px-1 rounded hover:bg-gray-200 dark:hover:bg-gray-800 group" x-data="{ editingItem: false }">
                                            <input type="checkbox" :checked="item.is_checked" @change="item.is_checked = !item.is_checked; apiCall(`/api/checklist-items/${item.id}/toggle`, 'PATCH')" class="w-4 h-4 mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 shrink-0" />
                                            <template x-if="!editingItem">
                                                <span @click="if(canEdit) editingItem = true" class="text-sm flex-1 cursor-pointer leading-snug" :class="item.is_checked ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300'" x-text="item.content"></span>
                                            </template>
                                            <template x-if="editingItem">
                                                <input x-model="item.content"
                                                    @blur="editingItem = false; apiCall(`/api/checklist-items/${item.id}`, 'PUT', { content: item.content })"
                                                    @keydown.enter="editingItem = false; apiCall(`/api/checklist-items/${item.id}`, 'PUT', { content: item.content })"
                                                    @keydown.escape="editingItem = false"
                                                    class="text-sm flex-1 rounded border-2 border-primary-500 px-2 py-0.5 focus:outline-none dark:bg-gray-800" />
                                            </template>
                                            <button @click.prevent="apiCall(`/api/checklist-items/${item.id}`, 'DELETE'); checklist.items = checklist.items.filter(i => i.id !== item.id)" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-danger-500 shrink-0 p-0.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <!-- Add item -->
                                    <template x-if="canEdit">
                                        <div x-data="{ adding: false, content: '' }">
                                            <template x-if="!adding">
                                                <button @click="adding = true" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:underline">+ Add an item</button>
                                            </template>
                                            <template x-if="adding">
                                                <form @submit.prevent="if(content.trim()) { apiCall(`/api/checklists/${checklist.id}/items`, 'POST', { content }).then(item => { checklist.items.push(item); content = ''; }) }">
                                                    <input x-model="content" placeholder="Add an item" class="w-full rounded-lg border-0 shadow-sm px-3 py-1.5 text-sm dark:bg-gray-800 focus:ring-2 focus:ring-primary-500" @keydown.escape="adding = false" />
                                                    <div class="flex gap-2 mt-1">
                                                        <button type="submit" class="px-3 py-1 rounded gradient-primary text-white text-xs font-medium">Add</button>
                                                        <button type="button" @click="adding = false" class="text-xs text-gray-400 hover:text-gray-600">Cancel</button>
                                                    </div>
                                                </form>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Attachments -->
                            <template x-if="activeCardFull.attachments?.length">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        Attachments
                                    </h3>
                                    <div class="space-y-1.5">
                                        <template x-for="att in activeCardFull.attachments" :key="att.id">
                                            <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-200 dark:bg-gray-800 group hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors">
                                                <template x-if="att.is_image">
                                                    <img :src="att.url" class="w-16 h-12 object-cover rounded" />
                                                </template>
                                                <template x-if="!att.is_image">
                                                    <div class="w-16 h-12 rounded bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-[10px] font-bold text-gray-500 uppercase" x-text="att.filename.split('.').pop()"></div>
                                                </template>
                                                <div class="flex-1 min-w-0">
                                                    <a :href="`/api/attachments/${att.id}/download`" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-primary-600 hover:underline truncate block" x-text="att.filename"></a>
                                                    <span class="text-[11px] text-gray-400" x-text="att.human_size"></span>
                                                </div>
                                                <button @click="if(confirm('Delete?')) { apiCall(`/api/attachments/${att.id}`, 'DELETE'); activeCardFull.attachments = activeCardFull.attachments.filter(a => a.id !== att.id) }" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-danger-500 p-1 transition-opacity">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <!-- Comments -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    Comments
                                </h3>
                                <template x-if="canEdit">
                                    <div x-data="commentBox()" class="mb-4 relative">
                                        <textarea
                                            x-ref="commentTextarea"
                                            x-model="body"
                                            @focus="focused = true"
                                            @input="checkMention($event)"
                                            @keydown="handleMentionKey($event)"
                                            :rows="focused ? 3 : 1"
                                            placeholder="Write a comment... Type @ to mention someone"
                                            class="w-full rounded-lg border-0 shadow-sm px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 resize-none transition-all"
                                        ></textarea>
                                        <!-- @mention autocomplete dropdown -->
                                        <div x-show="showMentions" x-cloak class="absolute left-0 bottom-full mb-1 w-64 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 z-[70] max-h-48 overflow-y-auto">
                                            <p class="px-3 py-1 text-[10px] font-semibold text-gray-400 uppercase">Members</p>
                                            <template x-for="(member, idx) in filteredMembers" :key="member.id">
                                                <button
                                                    @click="insertMention(member)"
                                                    class="w-full flex items-center gap-2 px-3 py-1.5 text-sm hover:bg-primary-50 dark:hover:bg-gray-700 transition-colors"
                                                    :class="idx === mentionIndex ? 'bg-primary-50 dark:bg-gray-700' : ''"
                                                >
                                                    <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white text-[8px] font-bold flex items-center justify-center" x-text="member.name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase()"></div>
                                                    <span class="text-gray-700 dark:text-gray-300" x-text="member.name"></span>
                                                </button>
                                            </template>
                                            <template x-if="filteredMembers.length === 0">
                                                <p class="px-3 py-2 text-xs text-gray-400">No matching members</p>
                                            </template>
                                        </div>
                                        <div x-show="focused && body.trim()" class="flex gap-2 mt-1.5">
                                            <button @click="submitComment()" class="px-4 py-1.5 rounded-lg gradient-primary text-white text-sm font-medium">Save</button>
                                            <button @click="body = ''; focused = false" class="px-3 py-1.5 rounded-lg text-sm text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700">Cancel</button>
                                        </div>
                                    </div>
                                </template>
                                <div class="space-y-3">
                                    <template x-for="comment in activeCardFull.comments || []" :key="comment.id">
                                        <div class="flex gap-3" x-data="{ editingComment: false, editBody: comment.body }">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white text-xs font-bold flex items-center justify-center shrink-0" x-text="comment.user?.name?.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase()"></div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-baseline gap-2 flex-wrap">
                                                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200" x-text="comment.user?.name"></span>
                                                    <span class="text-[11px] text-gray-400" x-text="new Date(comment.created_at).toLocaleDateString()"></span>
                                                    <template x-if="comment.user_id == {{ auth()->id() }}">
                                                        <div class="flex items-center gap-1">
                                                            <button @click="editingComment = true; editBody = comment.body" class="text-[11px] text-gray-400 hover:text-primary-600 hover:underline">Edit</button>
                                                            <span class="text-gray-300">-</span>
                                                            <button @click="if(confirm('Delete?')) { apiCall(`/api/comments/${comment.id}`, 'DELETE'); activeCardFull.comments = activeCardFull.comments.filter(c => c.id !== comment.id) }" class="text-[11px] text-gray-400 hover:text-danger-500 hover:underline">Delete</button>
                                                        </div>
                                                    </template>
                                                </div>
                                                <template x-if="!editingComment">
                                                                    <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 bg-white dark:bg-gray-800 rounded-lg shadow-sm px-3 py-2 prose prose-sm max-w-none dark:prose-invert [&_p]:my-1" x-html="renderComment(comment.body)"></div>
                                                </template>
                                                <template x-if="editingComment">
                                                    <div class="mt-1">
                                                        <textarea x-model="editBody" rows="3" class="w-full rounded-lg border-2 border-primary-500 px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-200 focus:outline-none resize-none"></textarea>
                                                        <div class="flex gap-2 mt-1">
                                                            <button @click="apiCall(`/api/comments/${comment.id}`, 'PUT', { body: editBody }).then(() => { comment.body = editBody; editingComment = false })" class="px-3 py-1 rounded gradient-primary text-white text-xs font-medium">Save</button>
                                                            <button @click="editingComment = false" class="text-xs text-gray-400 hover:text-gray-600">Cancel</button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- ======= RIGHT COLUMN: Sidebar Actions ======= --}}
                        <div class="w-full md:w-44 p-4 sm:p-5 md:pl-0 space-y-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Add to card</p>

                            <!-- Members -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Members
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 md:left-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 z-50 max-h-60 overflow-y-auto">
                                    <template x-for="member in boardMembers" :key="member.id">
                                        <button @click="apiCall(`/api/cards/${activeCardFull.id}/members`, 'POST', { user_id: member.id }).then(d => { activeCardFull.members = d.members })" class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs text-gray-700 dark:text-gray-300">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white text-[8px] font-bold flex items-center justify-center" x-text="member.name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase()"></div>
                                            <span class="truncate" x-text="member.name"></span>
                                            <svg x-show="activeCardFull.members?.some(m => m.id === member.id)" class="w-3.5 h-3.5 text-primary-500 ml-auto shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Labels -->
                            <div x-data="{ open: false, editMode: false, editLabel: null, newName: '', newColor: 'green' }" class="relative">
                                <button @click="open = !open; editMode = false" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                                    Labels
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 md:left-0 mt-1 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 z-50 max-h-72 overflow-y-auto">
                                    <template x-if="!editMode">
                                        <div>
                                            <template x-for="label in allLabels" :key="label.id">
                                                <button @click="apiCall(`/api/cards/${activeCardFull.id}/labels`, 'POST', { label_id: label.id }).then(d => { activeCardFull.labels = d.labels })" class="w-full flex items-center gap-2 px-3 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs">
                                                    <span class="w-full h-6 rounded flex items-center px-2 text-white text-[11px] font-semibold" :style="'background:' + getLabelHex(label.color)" x-text="label.name || label.color"></span>
                                                    <svg x-show="activeCardFull.labels?.some(l => l.id === label.id)" class="w-4 h-4 text-primary-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                                </button>
                                            </template>
                                            <hr class="my-1 border-gray-100 dark:border-gray-700">
                                            <button @click="editLabel = null; editMode = true; newName = ''; newColor = 'green'" class="w-full text-left px-3 py-1.5 text-xs text-primary-600 hover:bg-primary-50 dark:hover:bg-gray-700 font-medium">+ Create new label</button>
                                        </div>
                                    </template>
                                    <template x-if="editMode">
                                        <div class="p-3 space-y-2">
                                            <button @click="editMode = false" class="text-xs text-gray-400 hover:text-gray-600">&larr; Back</button>
                                            <div class="h-7 rounded flex items-center px-2" :style="'background:' + getLabelHex(newColor)"><span class="text-xs font-medium text-white" x-text="newName || newColor"></span></div>
                                            <input x-model="newName" placeholder="Label name" class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700 dark:border-gray-600 focus:ring-1 focus:ring-primary-500" />
                                            <div class="grid grid-cols-5 gap-1">
                                                <template x-for="c in ['green','yellow','orange','red','purple','blue','sky','lime','pink','black']" :key="c">
                                                    <button @click="newColor = c" class="h-6 rounded transition-all" :class="newColor === c ? 'ring-2 ring-gray-800 scale-110' : ''" :style="'background:' + getLabelHex(c)"></button>
                                                </template>
                                            </div>
                                            <template x-if="editLabel">
                                                <div class="flex gap-1">
                                                    <button @click="apiCall(`/api/labels/${editLabel.id}`, 'PUT', { name: newName, color: newColor }).then(() => { Object.assign(allLabels.find(la => la.id === editLabel.id), { name: newName, color: newColor }); editMode = false })" class="flex-1 px-2 py-1 rounded gradient-primary text-white text-xs font-medium">Save</button>
                                                    <button @click="if(confirm('Delete?')) { apiCall(`/api/labels/${editLabel.id}`, 'DELETE').then(() => { allLabels = allLabels.filter(l => l.id !== editLabel.id); editMode = false }) }" class="px-2 py-1 rounded bg-danger-100 text-danger-600 text-xs">Del</button>
                                                </div>
                                            </template>
                                            <template x-if="!editLabel">
                                                <button @click="apiCall(`/api/boards/${boardId}/labels`, 'POST', { name: newName, color: newColor }).then(l => { allLabels.push(l); editMode = false })" class="w-full px-2 py-1 rounded gradient-primary text-white text-xs font-medium">Create</button>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Checklist -->
                            <div x-data="{ open: false, name: 'Checklist' }" class="relative">
                                <button @click="open = !open" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    Checklist
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 md:left-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-3 z-50">
                                    <input x-model="name" class="w-full rounded border border-gray-300 px-2 py-1 text-xs mb-2 dark:bg-gray-700 dark:border-gray-600 focus:ring-1 focus:ring-primary-500" @keydown.enter="apiCall(`/api/cards/${activeCardFull.id}/checklists`, 'POST', { name }).then(c => { activeCardFull.checklists.push(c); open = false; name = 'Checklist' })" />
                                    <button @click="apiCall(`/api/cards/${activeCardFull.id}/checklists`, 'POST', { name }).then(c => { activeCardFull.checklists.push(c); open = false; name = 'Checklist' })" class="w-full px-2 py-1 rounded gradient-primary text-white text-xs font-medium">Add</button>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Dates
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 md:left-0 mt-1 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-3 z-50 space-y-2">
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase">Start</label>
                                    <input type="date" :value="activeCardFull.start_date?.substring(0,10)" @change="apiCall(`/api/cards/${activeCardFull.id}`, 'PUT', { start_date: $event.target.value }); activeCardFull.start_date = $event.target.value" class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700 dark:border-gray-600" />
                                    <label class="text-[10px] font-semibold text-gray-500 uppercase">Due</label>
                                    <input type="date" :value="activeCardFull.due_date?.substring(0,10)" @change="apiCall(`/api/cards/${activeCardFull.id}`, 'PUT', { due_date: $event.target.value }); activeCardFull.due_date = $event.target.value" class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700 dark:border-gray-600" />
                                    <button @click="apiCall(`/api/cards/${activeCardFull.id}`, 'PUT', { due_date: null, start_date: null }); activeCardFull.due_date = null; activeCardFull.start_date = null; open = false" class="w-full text-xs text-danger-500 hover:underline text-left">Remove dates</button>
                                </div>
                            </div>

                            <!-- Attachment -->
                            <label class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                Attachment
                                <input type="file" class="hidden" @change="
                                    const fd = new FormData();
                                    fd.append('file', $event.target.files[0]);
                                    fetch(`/api/cards/${activeCardFull.id}/attachments`, {
                                        method: 'POST',
                                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                        body: fd
                                    }).then(r => r.json()).then(a => { activeCardFull.attachments.push(a); });
                                    $event.target.value = '';
                                " />
                            </label>

                            <!-- Dependency -->
                            <div x-data="{ open: false, depCardId: '' }" class="relative">
                                <button @click="open = !open" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    Dependency
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 md:left-0 mt-1 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-3 z-50">
                                    <p class="text-[10px] text-gray-500 mb-1.5">This card depends on:</p>
                                    <select x-model="depCardId" class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:bg-gray-700 dark:border-gray-600 mb-2">
                                        <option value="">Choose card...</option>
                                        <template x-for="list in lists" :key="list.id">
                                            <optgroup :label="list.name">
                                                <template x-for="c in list.cards.filter(c => c.id !== activeCardFull.id)" :key="c.id">
                                                    <option :value="c.id" x-text="c.title"></option>
                                                </template>
                                            </optgroup>
                                        </template>
                                    </select>
                                    <button @click="if(depCardId) { apiCall(`/api/cards/${activeCardFull.id}/dependencies`, 'POST', { depends_on_card_id: parseInt(depCardId) }).then(() => { Alpine.store('toast').success('Dependency added'); open = false; depCardId = '' }) }" class="w-full px-2 py-1 rounded gradient-primary text-white text-xs font-medium">Add</button>
                                </div>
                            </div>

                            <!-- Separator -->
                            <div class="pt-2">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Actions</p>
                            </div>

                            <!-- Duplicate -->
                            <button @click="apiCall(`/api/cards/${activeCardFull.id}/duplicate`, 'POST').then(c => {
                                const list = lists.find(l => l.id === activeCardFull.board_list_id);
                                if(list) { c.labels = c.labels||[]; c.members = c.members||[]; c.checklist_total=0; c.checklist_checked=0; c.vote_count=0; c.age_days=0; c.description=!!c.description; c.comments_count=0; c.attachments_count=0; list.cards.push(c); }
                                Alpine.store('toast').success('Card duplicated');
                            })" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Duplicate
                            </button>

                            <!-- Save as Template -->
                            <button @click="apiCall(`/api/cards/${activeCardFull.id}/save-as-template`, 'POST').then(() => Alpine.store('toast').success('Saved as template'))" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                                Save as Template
                            </button>

                            <!-- Archive -->
                            <button @click="apiCall(`/api/cards/${activeCardFull.id}/archive`, 'POST').then(() => { removeCardFromList(activeCardFull.id); closeCardModal(); })" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                Archive
                            </button>

                            <!-- Delete -->
                            <button @click="if(confirm('Permanently delete this card?')) { apiCall(`/api/cards/${activeCardFull.id}`, 'DELETE').then(() => { removeCardFromList(activeCardFull.id); closeCardModal(); }) }" class="w-full text-left px-3 py-1.5 text-xs font-medium rounded-md bg-gray-200 dark:bg-gray-800 hover:bg-danger-100 dark:hover:bg-danger-900/30 hover:text-danger-600 transition-colors flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
