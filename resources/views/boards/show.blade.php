<x-layouts.board :board="$board">
    <div
        x-data="boardManager({{ Js::from([
            'boardId' => $board->id,
            'canEdit' => $board->canEdit(auth()->user()),
            'isAdmin' => $board->isAdmin(auth()->user()),
            'isObserver' => $board->isObserver(auth()->user()),
            'lists' => $board->lists->map(fn($list) => [
                'id' => $list->id,
                'name' => $list->name,
                'position' => (float)$list->position,
                'cards' => $list->cards->map(fn($card) => [
                    'id' => $card->id,
                    'title' => $card->title,
                    'position' => (float)$card->position,
                    'description' => $card->description ? true : false,
                    'cover_color' => $card->cover_color,
                    'cover_url' => $card->cover_url,
                    'due_date' => $card->due_date?->format('M j'),
                    'due_date_raw' => $card->due_date?->toISOString(),
                    'start_date' => $card->start_date?->format('M j'),
                    'due_status' => $card->due_status,
                    'labels' => $card->labels->map(fn($l) => ['id' => $l->id, 'color' => $l->color, 'name' => $l->name]),
                    'members' => $card->members->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'avatar_url' => $m->avatar_url]),
                    'checklist_total' => $card->checklist_progress['total'],
                    'checklist_checked' => $card->checklist_progress['checked'],
                    'comments_count' => $card->comments_count ?? 0,
                    'attachments_count' => $card->attachments_count ?? 0,
                    'vote_count' => $card->votes()->count(),
                    'age_days' => $card->age_days,
                ]),
            ]),
            'labels' => $board->labels,
            'members' => $board->members->map(fn($m) => ['id' => $m->id, 'name' => $m->name, 'avatar_url' => $m->avatar_url]),
        ]) }})"
        x-init="$nextTick(() => { setupSortable(); initEcho() })"
        x-effect="lists; $nextTick(() => setupCardSortables())"
        @keydown.window="handleKeyboard($event)"
        class="h-full flex flex-col"
    >
        <!-- Filter Bar -->
        @include('boards.partials.filter-bar')

        <!-- Bulk Actions Bar -->
        <div x-show="bulkMode" style="display:none" class="bg-neutral-900 border-b border-white/10 text-white px-4 py-2 flex items-center gap-4 text-sm z-20">
            <span x-text="selectedCards.length + ' card(s) selected'"></span>
            <button @click="bulkAction('archive')" class="px-3 py-1 bg-white/10 rounded-lg hover:bg-white/15 transition-colors">Archive</button>
            <button @click="bulkAction('delete')" class="px-3 py-1 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-colors">Delete</button>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="px-3 py-1 bg-white/10 rounded-lg hover:bg-white/15 transition-colors">Move to...</button>
                <div x-show="open" @click.away="open = false" x-cloak class="absolute top-full left-0 mt-1 w-48 bg-neutral-800 rounded-lg shadow-xl border border-white/10 py-1 z-50">
                    <template x-for="list in lists" :key="list.id">
                        <button @click="bulkAction('move', { board_list_id: list.id }); open = false" class="w-full text-left px-4 py-2 text-sm text-white/60 hover:bg-white/5" x-text="list.name"></button>
                    </template>
                </div>
            </div>
            <div class="flex-1"></div>
            <button @click="bulkMode = false; selectedCards = []" class="px-3 py-1 bg-white/10 rounded-lg hover:bg-white/15 transition-colors">Cancel</button>
        </div>

        <!-- Lists Container -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden p-4 pb-2 scrollbar-board">
            <div class="flex gap-3 items-start h-full" id="lists-container">
                <!-- Lists -->
                <template x-for="list in lists" :key="list.id">
                    <div class="flex-shrink-0 w-[272px] bg-neutral-900/95 backdrop-blur-sm rounded-xl flex flex-col max-h-[calc(100vh-6rem)] shadow-lg border border-white/5" :data-list-id="list.id">
                        <!-- List Header -->
                        <div class="px-3 py-2.5 flex items-center gap-2 list-drag-handle cursor-grab active:cursor-grabbing">
                            <template x-if="editingListId !== list.id">
                                <h3 class="flex-1 font-semibold text-white/90 text-sm leading-tight px-1.5 py-0.5 cursor-pointer rounded hover:bg-white/10 truncate"
                                    @click="if(canEdit) { editingListId = list.id; $nextTick(() => { let el = document.getElementById('listInput-' + list.id); if(el) el.focus(); }) }"
                                    x-text="list.name"></h3>
                            </template>
                            <template x-if="editingListId === list.id">
                                <input
                                    :id="'listInput-' + list.id"
                                    x-model="list.name"
                                    @blur="updateListName(list)"
                                    @keydown.enter="updateListName(list); editingListId = null"
                                    @keydown.escape="editingListId = null"
                                    class="flex-1 text-sm font-semibold bg-neutral-800 text-white rounded-lg px-2 py-1 border border-white/20 focus:outline-none focus:border-white/40"
                                />
                            </template>
                            <template x-if="canEdit">
                                <div x-data="{ open: false }" class="relative">
                                    <button @click.stop="open = !open" class="p-1 rounded-md hover:bg-white/10 text-white/30 hover:text-white/60">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"/></svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-1 w-48 bg-neutral-800 rounded-lg shadow-xl border border-white/10 py-1 z-50">
                                        <button @click="copyList(list); open = false" class="w-full text-left px-4 py-2 text-sm text-white/70 hover:bg-white/10">Copy list</button>
                                        <button @click="archiveList(list); open = false" class="w-full text-left px-4 py-2 text-sm text-white/70 hover:bg-white/10">Archive list</button>
                                        <hr class="my-1 border-white/10">
                                        <button @click="$dispatch('confirm-modal', { title: 'Delete List', message: 'Delete this list and all cards?', confirmLabel: 'Delete', variant: 'danger', onConfirm: () => { deleteList(list); open = false } })" class="w-full text-left px-4 py-2 text-sm text-red-400 hover:bg-white/10">Delete list</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Cards Container -->
                        <div class="flex-1 overflow-y-auto px-1.5 pb-1 space-y-1.5 scrollbar-thin cards-container min-h-[4px]" :data-list-id="list.id">
                            <template x-for="card in list.cards.filter(c => isCardVisible(c))" :key="card.id">
                                <div
                                    class="bg-neutral-800 rounded-lg shadow-sm hover:ring-1 hover:ring-white/20 cursor-pointer transition-all duration-100 group relative border border-white/5"
                                    :data-card-id="card.id"
                                    :style="card.age_days > 30 ? 'opacity:0.45' : card.age_days > 14 ? 'opacity:0.65' : card.age_days > 7 ? 'opacity:0.85' : ''"
                                    @click="bulkMode ? toggleCardSelection(card.id) : openCardDetail(card.id)"
                                >
                                    <!-- Bulk select checkbox -->
                                    <template x-if="bulkMode">
                                        <div class="absolute top-1.5 right-1.5 z-10">
                                            <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-colors"
                                                :class="selectedCards.includes(card.id) ? 'bg-blue-500 border-blue-500' : 'border-white/20 bg-neutral-700'">
                                                <svg x-show="selectedCards.includes(card.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Cover -->
                                    <template x-if="card.cover_color">
                                        <div class="h-8 rounded-t-lg" :style="'background:' + card.cover_color"></div>
                                    </template>
                                    <template x-if="card.cover_url">
                                        <img :src="card.cover_url" class="h-32 w-full object-cover rounded-t-lg" />
                                    </template>

                                    <div class="px-2 py-1.5">
                                        <!-- Labels -->
                                        <template x-if="card.labels.length">
                                            <div class="flex flex-wrap gap-1 mb-1.5">
                                                <template x-for="label in card.labels" :key="label.id">
                                                    <span class="h-2 w-10 rounded-full" :style="'background:' + getLabelHex(label.color)"></span>
                                                </template>
                                            </div>
                                        </template>

                                        <!-- Title -->
                                        <p class="text-sm text-white/90 leading-snug" x-text="card.title"></p>

                                        <!-- Footer badges -->
                                        <div class="flex items-center gap-2 mt-1.5 text-xs text-white/30 flex-wrap" x-show="card.due_date || card.description || card.checklist_total > 0 || card.members.length || card.vote_count > 0 || card.comments_count > 0 || card.attachments_count > 0">
                                            <!-- Due date -->
                                            <template x-if="card.due_date">
                                                <span class="flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[11px]"
                                                    :class="{
                                                        'bg-danger-500 text-white': card.due_status === 'overdue',
                                                        'bg-sunny-400 text-sunny-900': card.due_status === 'due_soon',
                                                        'bg-success-500 text-white': card.due_status === 'complete',
                                                        'bg-white/10 text-white/50': card.due_status === 'normal',
                                                    }">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span x-text="card.due_date"></span>
                                                </span>
                                            </template>
                                            <!-- Description icon -->
                                            <template x-if="card.description">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                            </template>
                                            <!-- Comments -->
                                            <template x-if="card.comments_count > 0">
                                                <span class="flex items-center gap-0.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                    <span x-text="card.comments_count"></span>
                                                </span>
                                            </template>
                                            <!-- Attachments -->
                                            <template x-if="card.attachments_count > 0">
                                                <span class="flex items-center gap-0.5">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                    <span x-text="card.attachments_count"></span>
                                                </span>
                                            </template>
                                            <!-- Checklist -->
                                            <template x-if="card.checklist_total > 0">
                                                <span class="flex items-center gap-0.5" :class="card.checklist_checked === card.checklist_total ? 'bg-success-500 text-white px-1.5 py-0.5 rounded' : ''">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                    <span x-text="card.checklist_checked + '/' + card.checklist_total"></span>
                                                </span>
                                            </template>
                                            <!-- Vote count -->
                                            <template x-if="card.vote_count > 0">
                                                <span class="flex items-center gap-0.5 text-primary-500">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M2 20h2V8H2v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L15.17 1 7.59 8.59C7.22 8.95 7 9.45 7 10v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                                                    <span x-text="card.vote_count"></span>
                                                </span>
                                            </template>
                                            <!-- Spacer -->
                                            <div class="flex-1"></div>
                                            <!-- Members -->
                                            <div class="flex -space-x-1">
                                                <template x-for="m in card.members.slice(0, 3)" :key="m.id">
                                                    <div class="w-6 h-6 rounded-full bg-white/15 text-white/60 text-[8px] font-bold flex items-center justify-center ring-1 ring-neutral-800" x-text="m.name.split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase()"></div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Add Card -->
                        <template x-if="canEdit">
                            <div class="px-2 pb-2 pt-1" x-data="{ adding: false, title: '' }">
                                <template x-if="!adding">
                                    <button @click.stop="adding = true; $nextTick(() => $refs.newCardInput?.focus())" class="w-full text-left px-2 py-1.5 rounded-lg text-sm text-white/40 hover:bg-white/10 transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add a card
                                    </button>
                                </template>
                                <template x-if="adding">
                                    <form @submit.prevent="if(title.trim()) { addCard(list, title); title = ''; adding = false }">
                                        <textarea
                                            x-ref="newCardInput"
                                            x-model="title"
                                            @keydown.enter.prevent="if(title.trim()) { addCard(list, title); title = ''; adding = false }"
                                            @keydown.escape="adding = false"
                                            placeholder="Enter a title for this card..."
                                            rows="3"
                                            class="w-full rounded-lg border border-white/10 bg-neutral-800 px-2.5 py-2 text-sm text-white/90 resize-none focus:ring-1 focus:ring-white/20 focus:border-white/20 placeholder-white/30"
                                        ></textarea>
                                        <div class="flex items-center gap-2 mt-1.5">
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-xs font-semibold hover:bg-white/20">Add card</button>
                                            <button type="button" @click="adding = false" class="p-1 rounded hover:bg-white/10 text-white/30">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </form>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Add List -->
                <template x-if="canEdit">
                    <div class="flex-shrink-0 w-[272px]" x-data="{ adding: false, name: '' }">
                        <template x-if="!adding">
                            <button @click="adding = true; $nextTick(() => $refs.newListInput?.focus())" class="w-full px-3 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-white/40 hover:text-white/60 text-sm font-medium transition-all flex items-center gap-2 border border-white/5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add another list
                            </button>
                        </template>
                        <template x-if="adding">
                            <form @submit.prevent="if(name.trim()) { addList(name); name = ''; adding = false }" class="bg-neutral-900/95 rounded-xl p-2 border border-white/5">
                                <input
                                    x-ref="newListInput"
                                    x-model="name"
                                    @keydown.escape="adding = false"
                                    placeholder="Enter list title..."
                                    class="w-full rounded-lg border border-white/10 bg-neutral-800 px-2.5 py-2 text-sm text-white/90 placeholder-white/30 focus:ring-1 focus:ring-white/20"
                                />
                                <div class="flex items-center gap-2 mt-1.5">
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-white/10 text-white text-xs font-semibold hover:bg-white/20">Add list</button>
                                    <button type="button" @click="adding = false" class="p-1 rounded hover:bg-white/10 text-white/30">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Card Detail Modal -->
        @include('boards.partials.card-modal')

        <!-- Board Menu Sidebar -->
        @include('boards.partials.board-menu')
    </div>

    <script>
    let boardManagerRef = null;
    function boardManager(data) {
        return {
            init() { boardManagerRef = this; },
            boardId: data.boardId,
            canEdit: data.canEdit || false,
            isAdmin: data.isAdmin || false,
            isObserver: data.isObserver || false,
            lists: data.lists,
            allLabels: data.labels,
            boardMembers: data.members,
            editingListId: null,
            activeCard: null,
            activeCardFull: null,
            showCardModal: false,

            // Filters
            showFilters: false,
            filters: { labels: [], members: [], dueDate: null, keyword: '' },

            // Bulk actions
            bulkMode: false,
            selectedCards: [],

            // Sortable instances
            _listSortable: null,
            _cardSortables: [],

            labelColors: {
                green: '#22c55e', yellow: '#eab308', orange: '#f97316', red: '#ef4444',
                purple: '#a855f7', blue: '#3b82f6', sky: '#0ea5e9', lime: '#84cc16',
                pink: '#ec4899', black: '#1f2937',
            },

            getLabelHex(color) {
                return this.labelColors[color] || color;
            },

            csrf() {
                return document.querySelector('meta[name="csrf-token"]').content;
            },

            async apiCall(url, method = 'GET', body = null) {
                const opts = {
                    method,
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                };
                if (body) opts.body = JSON.stringify(body);
                const res = await fetch(url, opts);
                if (!res.ok) throw new Error('API error');
                return res.json();
            },

            // ─── DRAG & DROP ─────────────────────────────────
            setupSortable() {
                const listsContainer = document.getElementById('lists-container');
                if (!listsContainer) return;

                // Destroy old
                if (this._listSortable) { this._listSortable.destroy(); this._listSortable = null; }

                this._listSortable = new Sortable(listsContainer, {
                    group: 'lists',
                    animation: 150,
                    easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                    handle: '.list-drag-handle',
                    draggable: '[data-list-id]',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    filter: 'input, textarea, button, [x-data]',
                    preventOnFilter: false,
                    onEnd: (evt) => {
                        const items = [];
                        evt.to.querySelectorAll('[data-list-id]').forEach((el, idx) => {
                            const id = parseInt(el.dataset.listId);
                            const pos = (idx + 1) * 1024;
                            items.push({ id, position: pos });
                            const list = this.lists.find(l => l.id === id);
                            if (list) list.position = pos;
                        });
                        this.lists.sort((a, b) => a.position - b.position);
                        this.apiCall(`/api/boards/${this.boardId}/lists/reorder`, 'PATCH', { items });
                    }
                });

                this.setupCardSortables();
            },

            setupCardSortables() {
                // Destroy all old card sortables
                this._cardSortables.forEach(s => { try { s.destroy(); } catch(e) {} });
                this._cardSortables = [];

                document.querySelectorAll('.cards-container').forEach(container => {
                    const sortable = new Sortable(container, {
                        group: 'cards',
                        animation: 150,
                        easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                        draggable: '[data-card-id]',
                        ghostClass: 'sortable-ghost',
                        chosenClass: 'sortable-chosen',
                        dragClass: 'sortable-drag',
                        filter: 'input, textarea, button',
                        preventOnFilter: false,
                        emptyInsertThreshold: 20,
                        onEnd: (evt) => {
                            const cardId = parseInt(evt.item.dataset.cardId);
                            const toListId = parseInt(evt.to.dataset.listId);
                            const fromListId = parseInt(evt.from.dataset.listId);

                            // Calculate position
                            const cardEls = Array.from(evt.to.querySelectorAll('[data-card-id]'));
                            const idx = cardEls.indexOf(evt.item);
                            let position;

                            const toList = this.lists.find(l => l.id === toListId);
                            const fromList = this.lists.find(l => l.id === fromListId);

                            if (cardEls.length <= 1) {
                                position = 1024;
                            } else if (idx === 0) {
                                const nextId = parseInt(cardEls[1]?.dataset.cardId);
                                const nextCard = toList?.cards.find(c => c.id === nextId);
                                position = nextCard ? nextCard.position / 2 : 1024;
                            } else if (idx >= cardEls.length - 1) {
                                const prevId = parseInt(cardEls[idx - 1]?.dataset.cardId);
                                const prevCard = toList?.cards.find(c => c.id === prevId);
                                position = prevCard ? prevCard.position + 1024 : (idx + 1) * 1024;
                            } else {
                                const prevId = parseInt(cardEls[idx - 1]?.dataset.cardId);
                                const nextId = parseInt(cardEls[idx + 1]?.dataset.cardId);
                                const prevCard = toList?.cards.find(c => c.id === prevId);
                                const nextCard = toList?.cards.find(c => c.id === nextId);
                                position = (prevCard && nextCard)
                                    ? (prevCard.position + nextCard.position) / 2
                                    : (idx + 1) * 1024;
                            }

                            // Move card in data
                            if (fromList && toList) {
                                const card = fromList.cards.find(c => c.id === cardId);
                                if (card) {
                                    // Remove from source
                                    fromList.cards = fromList.cards.filter(c => c.id !== cardId);
                                    // Set new position
                                    card.position = position;
                                    // Add to target (if different list or re-add)
                                    if (!toList.cards.find(c => c.id === cardId)) {
                                        toList.cards.push(card);
                                    }
                                    toList.cards.sort((a, b) => a.position - b.position);
                                }
                            }

                            // API call
                            this.apiCall(`/api/cards/${cardId}/move`, 'PUT', {
                                board_list_id: toListId,
                                position: position,
                            });
                        }
                    });
                    this._cardSortables.push(sortable);
                });
            },

            // ─── FILTERING ───────────────────────────────────
            isCardVisible(card) {
                if (this.filters.keyword) {
                    const kw = this.filters.keyword.toLowerCase();
                    if (!card.title.toLowerCase().includes(kw)) return false;
                }
                if (this.filters.labels.length) {
                    if (!card.labels.some(l => this.filters.labels.includes(l.id))) return false;
                }
                if (this.filters.members.length) {
                    if (!card.members.some(m => this.filters.members.includes(m.id))) return false;
                }
                if (this.filters.dueDate) {
                    if (this.filters.dueDate === 'overdue' && card.due_status !== 'overdue') return false;
                    if (this.filters.dueDate === 'due_soon' && card.due_status !== 'due_soon' && card.due_status !== 'overdue') return false;
                    if (this.filters.dueDate === 'no_date' && card.due_date) return false;
                    if (this.filters.dueDate === 'complete' && card.due_status !== 'complete') return false;
                }
                return true;
            },

            get hasActiveFilters() {
                return this.filters.labels.length || this.filters.members.length || this.filters.dueDate || this.filters.keyword;
            },

            clearFilters() {
                this.filters = { labels: [], members: [], dueDate: null, keyword: '' };
            },

            toggleFilterLabel(id) {
                const idx = this.filters.labels.indexOf(id);
                idx === -1 ? this.filters.labels.push(id) : this.filters.labels.splice(idx, 1);
            },

            toggleFilterMember(id) {
                const idx = this.filters.members.indexOf(id);
                idx === -1 ? this.filters.members.push(id) : this.filters.members.splice(idx, 1);
            },

            // ─── BULK ACTIONS ────────────────────────────────
            toggleCardSelection(cardId) {
                const idx = this.selectedCards.indexOf(cardId);
                idx === -1 ? this.selectedCards.push(cardId) : this.selectedCards.splice(idx, 1);
            },

            async bulkAction(action, extra = {}) {
                if (!this.selectedCards.length) return;
                try {
                    await this.apiCall(`/api/boards/${this.boardId}/bulk-actions`, 'POST', {
                        card_ids: this.selectedCards,
                        action,
                        ...extra,
                    });
                    if (action === 'archive' || action === 'delete') {
                        for (const list of this.lists) {
                            list.cards = list.cards.filter(c => !this.selectedCards.includes(c.id));
                        }
                    } else if (action === 'move' && extra.board_list_id) {
                        const targetList = this.lists.find(l => l.id === extra.board_list_id);
                        for (const list of this.lists) {
                            const moved = list.cards.filter(c => this.selectedCards.includes(c.id));
                            list.cards = list.cards.filter(c => !this.selectedCards.includes(c.id));
                            if (targetList && list.id !== targetList.id) {
                                targetList.cards.push(...moved);
                            }
                        }
                    }
                    this.selectedCards = [];
                    this.bulkMode = false;
                    Alpine.store('toast').success('Bulk action completed');
                } catch(e) {
                    Alpine.store('toast').error('Bulk action failed');
                }
            },

            // ─── KEYBOARD SHORTCUTS ──────────────────────────
            handleKeyboard(e) {
                if (e.target.matches('input,textarea,[contenteditable]')) return;
                if (this.showCardModal) return; // don't fire shortcuts when modal is open
                if (e.key === 'n') { e.preventDefault(); }
                if (e.key === 'f') { e.preventDefault(); this.showFilters = !this.showFilters; }
                if (e.key === 'b') { e.preventDefault(); this.$dispatch('open-board-menu'); }
                if (e.key === 'x') { e.preventDefault(); this.bulkMode = !this.bulkMode; this.selectedCards = []; }
            },

            // ─── RENDER HELPERS ───────────────────────────────
            renderComment(text) {
                if (!text) return '';
                // Highlight @mentions
                let processed = text.replace(/@([\w.\-]+)/g, '<span class="text-primary-600 font-semibold cursor-pointer hover:underline">@$1</span>');
                // Render markdown safely
                if (typeof marked !== 'undefined' && typeof DOMPurify !== 'undefined') {
                    return DOMPurify.sanitize(marked.parse(processed));
                }
                return processed.replace(/\n/g, '<br>');
            },

            // ─── HELPERS ─────────────────────────────────────
            updateCardInList(cardId, changes) {
                for (const list of this.lists) {
                    const card = list.cards.find(c => c.id === cardId);
                    if (card) { Object.assign(card, changes); break; }
                }
            },

            removeCardFromList(cardId) {
                for (const list of this.lists) {
                    const idx = list.cards.findIndex(c => c.id === cardId);
                    if (idx !== -1) { list.cards.splice(idx, 1); break; }
                }
            },

            // ─── LIST OPERATIONS ─────────────────────────────
            async addList(name) {
                if (!name.trim()) return;
                const list = await this.apiCall(`/api/boards/${this.boardId}/lists`, 'POST', { name });
                list.cards = list.cards || [];
                this.lists.push(list);
            },

            async updateListName(list) {
                this.editingListId = null;
                await this.apiCall(`/api/lists/${list.id}`, 'PUT', { name: list.name });
            },

            async deleteList(list) {
                await this.apiCall(`/api/lists/${list.id}`, 'DELETE');
                this.lists = this.lists.filter(l => l.id !== list.id);
            },

            async archiveList(list) {
                await this.apiCall(`/api/lists/${list.id}/archive`, 'POST');
                this.lists = this.lists.filter(l => l.id !== list.id);
            },

            async copyList(list) {
                const newList = await this.apiCall(`/api/lists/${list.id}/copy`, 'POST');
                this.lists.push(newList);
            },

            // ─── CARD OPERATIONS ─────────────────────────────
            async addCard(list, title) {
                if (!title.trim()) return;
                const card = await this.apiCall(`/api/lists/${list.id}/cards`, 'POST', { title });
                card.labels = card.labels || [];
                card.members = card.members || [];
                card.checklist_total = 0;
                card.checklist_checked = 0;
                card.comments_count = 0;
                card.attachments_count = 0;
                card.vote_count = 0;
                card.age_days = 0;
                card.description = false;
                list.cards.push(card);
            },

            async openCardDetail(cardId) {
                this.showCardModal = true;
                this.activeCardFull = null;
                this.activeCardFull = await this.apiCall(`/api/cards/${cardId}`);
            },

            closeCardModal() {
                this.showCardModal = false;
                this.activeCardFull = null;
            },

            // ─── REAL-TIME ───────────────────────────────────
            initEcho() {
                if (typeof Echo === 'undefined') return;
                Echo.private(`board.${this.boardId}`)
                    .listen('.card.created', (e) => {
                        const list = this.lists.find(l => l.id === e.list_id);
                        if (list && !list.cards.find(c => c.id === e.card.id)) {
                            e.card.labels = e.card.labels || [];
                            e.card.members = e.card.members || [];
                            e.card.checklist_total = 0;
                            e.card.checklist_checked = 0;
                            e.card.vote_count = 0;
                            e.card.age_days = 0;
                            list.cards.push(e.card);
                        }
                    })
                    .listen('.card.moved', (e) => {
                        const fromList = this.lists.find(l => l.id === e.from_list_id);
                        const toList = this.lists.find(l => l.id === e.to_list_id);
                        if (fromList && toList) {
                            const card = fromList.cards.find(c => c.id === e.card_id);
                            if (card) {
                                fromList.cards = fromList.cards.filter(c => c.id !== e.card_id);
                                card.position = e.position;
                                toList.cards.push(card);
                                toList.cards.sort((a, b) => a.position - b.position);
                            }
                        }
                    })
                    .listen('.card.updated', (e) => {
                        this.updateCardInList(e.card_id, e.changes);
                    })
                    .listen('.chat.message', (e) => {
                        window.dispatchEvent(new CustomEvent('chat-incoming', { detail: e }));
                    })
                    .listen('.chat.message.deleted', (e) => {
                        window.dispatchEvent(new CustomEvent('chat-deleted', { detail: e }));
                    });
            },
        };
    }
    function commentBox() {
        return {
            body: '',
            focused: false,
            showMentions: false,
            mentionQuery: '',
            mentionStart: -1,
            mentionIndex: 0,

            get filteredMembers() {
                // boardMembers is accessible from parent boardManager scope
                const members = boardManagerRef?.boardMembers || [];
                if (!this.mentionQuery) return members;
                const q = this.mentionQuery.toLowerCase();
                return members.filter(m => m.name.toLowerCase().includes(q));
            },

            checkMention(e) {
                const textarea = this.$refs.commentTextarea;
                const val = textarea.value;
                const pos = textarea.selectionStart;

                // Find the last @ before cursor
                const before = val.substring(0, pos);
                const atIdx = before.lastIndexOf('@');

                if (atIdx >= 0) {
                    const afterAt = before.substring(atIdx + 1);
                    // Only show if no space in the mention query (still typing the name)
                    if (!afterAt.includes(' ') && !afterAt.includes('\n')) {
                        this.mentionStart = atIdx;
                        this.mentionQuery = afterAt;
                        this.showMentions = true;
                        this.mentionIndex = 0;
                        return;
                    }
                }
                this.showMentions = false;
            },

            handleMentionKey(e) {
                if (!this.showMentions) return;
                const members = this.filteredMembers;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.mentionIndex = Math.min(this.mentionIndex + 1, members.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.mentionIndex = Math.max(this.mentionIndex - 1, 0);
                } else if (e.key === 'Enter' && members.length > 0) {
                    e.preventDefault();
                    this.insertMention(members[this.mentionIndex]);
                } else if (e.key === 'Escape') {
                    this.showMentions = false;
                }
            },

            insertMention(member) {
                const textarea = this.$refs.commentTextarea;
                const pos = textarea.selectionStart;
                const before = this.body.substring(0, this.mentionStart);
                const after = this.body.substring(pos);
                const mentionName = member.name.replace(/\s+/g, '.');
                this.body = before + '@' + mentionName + ' ' + after;
                this.showMentions = false;
                this.$nextTick(() => {
                    const newPos = this.mentionStart + mentionName.length + 2;
                    textarea.focus();
                    textarea.setSelectionRange(newPos, newPos);
                });
            },

            async submitComment() {
                if (!this.body.trim()) return;
                try {
                    const card = boardManagerRef?.activeCardFull;
                    if (!card) return;
                    const csrf = document.querySelector('meta[name="csrf-token"]').content;
                    const res = await fetch(`/api/cards/${card.id}/comments`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ body: this.body })
                    });
                    if (res.ok) {
                        const comment = await res.json();
                        card.comments.unshift(comment);
                        this.body = '';
                        this.focused = false;
                    }
                } catch(e) {
                    console.error('Comment failed', e);
                }
            }
        };
    }
    </script>
</x-layouts.board>
