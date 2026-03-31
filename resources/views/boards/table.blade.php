<x-layouts.board :board="$board">
    <div x-data="tableView({{ Js::from([
        'boardId' => $board->id,
        'cards' => $board->lists->flatMap(fn($list) => $list->cards->map(fn($card) => [
            'id' => $card->id,
            'title' => $card->title,
            'description' => $card->description ? \Str::limit($card->description, 80) : '',
            'list_name' => $list->name,
            'list_id' => $list->id,
            'due_date' => $card->due_date?->format('Y-m-d'),
            'due_date_display' => $card->due_date?->format('M j, Y'),
            'start_date' => $card->start_date?->format('Y-m-d'),
            'start_date_display' => $card->start_date?->format('M j, Y'),
            'due_status' => $card->due_status,
            'labels' => $card->labels->map(fn($l) => ['id' => $l->id, 'color' => $l->color, 'name' => $l->name]),
            'members' => $card->members->map(fn($m) => ['id' => $m->id, 'name' => $m->name]),
            'checklist_progress' => $card->checklist_progress,
        ]))->values(),
    ]) }})" class="h-full flex flex-col bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm">

        <!-- Table -->
        <div class="flex-1 overflow-auto">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800 z-10">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th @click="sortBy('title')" class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-gray-800 select-none">
                            Title <span x-show="sortCol === 'title'" x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                        </th>
                        <th @click="sortBy('list_name')" class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-gray-800 select-none w-36">
                            List <span x-show="sortCol === 'list_name'" x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                        </th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 w-40">Labels</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 w-32">Members</th>
                        <th @click="sortBy('start_date')" class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-gray-800 select-none w-32">
                            Start <span x-show="sortCol === 'start_date'" x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                        </th>
                        <th @click="sortBy('due_date')" class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 cursor-pointer hover:text-gray-800 select-none w-32">
                            Due <span x-show="sortCol === 'due_date'" x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                        </th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 w-24">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="card in sortedCards" :key="card.id">
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50 cursor-pointer transition-colors"
                            @click="window.location.href='/b/' + boardId">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800 dark:text-gray-200" x-text="card.title"></div>
                                <div class="text-xs text-gray-400 mt-0.5 truncate" x-text="card.description" x-show="card.description"></div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-lg" x-text="card.list_name"></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="label in card.labels" :key="label.id">
                                        <span class="w-6 h-3 rounded-full inline-block" :style="'background:' + (labelColors[label.color] || label.color)"></span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex -space-x-1">
                                    <template x-for="m in card.members.slice(0, 3)" :key="m.id">
                                        <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white text-[8px] font-bold flex items-center justify-center ring-1 ring-white dark:ring-gray-900" x-text="m.name.split(' ').map(w => w[0]).join('').substring(0,2).toUpperCase()"></div>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500" x-text="card.start_date_display || '—'"></td>
                            <td class="px-4 py-3">
                                <span x-show="card.due_date_display" class="text-xs px-2 py-0.5 rounded"
                                    :class="{
                                        'bg-danger-100 text-danger-700': card.due_status === 'overdue',
                                        'bg-sunny-100 text-sunny-700': card.due_status === 'due_soon',
                                        'bg-success-100 text-success-700': card.due_status === 'complete',
                                        'text-gray-500': card.due_status === 'normal',
                                    }"
                                    x-text="card.due_date_display"></span>
                                <span x-show="!card.due_date_display" class="text-xs text-gray-400">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <template x-if="card.checklist_progress.total > 0">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full" :class="card.checklist_progress.percent === 100 ? 'bg-success-500' : 'bg-primary-500'" :style="'width:' + card.checklist_progress.percent + '%'"></div>
                                        </div>
                                        <span class="text-[10px] text-gray-500" x-text="card.checklist_progress.checked + '/' + card.checklist_progress.total"></span>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <template x-if="sortedCards.length === 0">
                <div class="text-center py-16 text-gray-400">No cards to display</div>
            </template>
        </div>
    </div>

    <script>
    function tableView(data) {
        return {
            boardId: data.boardId,
            cards: data.cards,
            sortCol: 'title',
            sortDir: 'asc',
            labelColors: {
                green: '#22c55e', yellow: '#eab308', orange: '#f97316', red: '#ef4444',
                purple: '#a855f7', blue: '#3b82f6', sky: '#0ea5e9', lime: '#84cc16',
                pink: '#ec4899', black: '#1f2937',
            },
            get sortedCards() {
                return [...this.cards].sort((a, b) => {
                    let aVal = a[this.sortCol] || '';
                    let bVal = b[this.sortCol] || '';
                    if (typeof aVal === 'string') aVal = aVal.toLowerCase();
                    if (typeof bVal === 'string') bVal = bVal.toLowerCase();
                    if (aVal < bVal) return this.sortDir === 'asc' ? -1 : 1;
                    if (aVal > bVal) return this.sortDir === 'asc' ? 1 : -1;
                    return 0;
                });
            },
            sortBy(col) {
                if (this.sortCol === col) this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
                else { this.sortCol = col; this.sortDir = 'asc'; }
            },
        };
    }
    </script>
</x-layouts.board>
