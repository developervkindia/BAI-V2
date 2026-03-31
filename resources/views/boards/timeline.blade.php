<x-layouts.board :board="$board">
    <div x-data="timelineView({{ Js::from([
        'boardId' => $board->id,
        'lists' => $board->lists->map(fn($list) => [
            'id' => $list->id,
            'name' => $list->name,
            'cards' => $list->cards->filter(fn($c) => $c->due_date || $c->start_date)->map(fn($card) => [
                'id' => $card->id,
                'title' => $card->title,
                'start_date' => $card->start_date?->format('Y-m-d'),
                'due_date' => $card->due_date?->format('Y-m-d'),
                'due_status' => $card->due_status,
                'labels' => $card->labels->map(fn($l) => ['color' => $l->color]),
            ])->values(),
        ]),
    ]) }})" class="h-full flex flex-col bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm">
        <!-- Header -->
        <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <button @click="scrollWeeks(-2)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200" x-text="rangeLabel"></h2>
            <button @click="scrollWeeks(2)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            <button @click="resetToToday()" class="px-3 py-1.5 text-sm bg-primary-100 text-primary-700 rounded-lg hover:bg-primary-200 dark:bg-primary-900/30 dark:text-primary-400">Today</button>
        </div>

        <!-- Timeline -->
        <div class="flex-1 overflow-auto">
            <div class="min-w-[800px]">
                <!-- Week headers -->
                <div class="flex border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900 z-10">
                    <div class="w-48 flex-shrink-0 px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700">List</div>
                    <div class="flex-1 flex">
                        <template x-for="(day, idx) in timelineDays" :key="idx">
                            <div class="flex-1 min-w-[40px] px-1 py-2 text-center text-[10px] border-r border-gray-100 dark:border-gray-800"
                                :class="day.isToday ? 'bg-primary-50 dark:bg-primary-900/20 font-bold text-primary-700 dark:text-primary-400' : 'text-gray-500 dark:text-gray-500'">
                                <div x-text="day.dayName"></div>
                                <div x-text="day.date"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Rows per list -->
                <template x-for="list in lists" :key="list.id">
                    <div class="flex border-b border-gray-100 dark:border-gray-800 min-h-[60px]">
                        <div class="w-48 flex-shrink-0 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700 truncate" x-text="list.name"></div>
                        <div class="flex-1 relative">
                            <template x-for="card in list.cards" :key="card.id">
                                <div
                                    class="absolute h-7 rounded-md px-2 py-1 text-[11px] font-medium truncate cursor-pointer hover:opacity-80 flex items-center shadow-sm"
                                    :class="{
                                        'bg-danger-200 text-danger-800': card.due_status === 'overdue',
                                        'bg-sunny-200 text-sunny-800': card.due_status === 'due_soon',
                                        'bg-success-200 text-success-800': card.due_status === 'complete',
                                        'bg-primary-200 text-primary-800': !card.due_status || card.due_status === 'normal',
                                    }"
                                    :style="getBarStyle(card)"
                                    x-text="card.title">
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
    function timelineView(data) {
        const now = new Date();
        return {
            boardId: data.boardId,
            lists: data.lists,
            startDate: new Date(now.getFullYear(), now.getMonth(), now.getDate() - now.getDay()),
            totalDays: 28,

            get timelineDays() {
                const days = [];
                const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                const today = new Date(); today.setHours(0,0,0,0);
                for (let i = 0; i < this.totalDays; i++) {
                    const d = new Date(this.startDate);
                    d.setDate(d.getDate() + i);
                    days.push({
                        dayName: dayNames[d.getDay()],
                        date: d.getDate(),
                        fullDate: d.toISOString().split('T')[0],
                        isToday: d.getTime() === today.getTime(),
                    });
                }
                return days;
            },

            get rangeLabel() {
                const end = new Date(this.startDate);
                end.setDate(end.getDate() + this.totalDays - 1);
                const opts = { month: 'short', day: 'numeric' };
                return this.startDate.toLocaleDateString('en', opts) + ' - ' + end.toLocaleDateString('en', opts) + ', ' + end.getFullYear();
            },

            getBarStyle(card) {
                const start = card.start_date || card.due_date;
                const end = card.due_date || card.start_date;
                if (!start) return 'display:none';

                const startD = new Date(start);
                const endD = new Date(end);
                const timelineStart = this.startDate.getTime();
                const dayWidth = 100 / this.totalDays;

                const startOffset = Math.max(0, (startD.getTime() - timelineStart) / 86400000);
                const duration = Math.max(1, (endD.getTime() - startD.getTime()) / 86400000 + 1);

                const left = startOffset * dayWidth;
                const width = duration * dayWidth;

                if (left > 100 || left + width < 0) return 'display:none';

                return `left:${Math.max(0, left)}%;width:${Math.min(width, 100 - Math.max(0, left))}%;top:4px`;
            },

            scrollWeeks(n) {
                const d = new Date(this.startDate);
                d.setDate(d.getDate() + n * 7);
                this.startDate = d;
            },
            resetToToday() {
                const now = new Date();
                this.startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - now.getDay());
            },
        };
    }
    </script>
</x-layouts.board>
