<x-layouts.board :board="$board">
    <div x-data="calendarView({{ Js::from([
        'boardId' => $board->id,
        'cards' => $board->lists->flatMap(fn($list) => $list->cards->map(fn($card) => [
            'id' => $card->id,
            'title' => $card->title,
            'due_date' => $card->due_date?->format('Y-m-d'),
            'start_date' => $card->start_date?->format('Y-m-d'),
            'due_status' => $card->due_status,
            'list_name' => $list->name,
            'labels' => $card->labels->map(fn($l) => ['color' => $l->color]),
            'members' => $card->members->map(fn($m) => ['name' => $m->name]),
        ]))->values(),
    ]) }})" class="h-full flex flex-col bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <button @click="prevMonth()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-200" x-text="monthNames[currentMonth] + ' ' + currentYear"></h2>
            <button @click="nextMonth()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Day headers -->
        <div class="grid grid-cols-7 border-b border-gray-200 dark:border-gray-700">
            <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']">
                <div class="px-2 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase" x-text="day"></div>
            </template>
        </div>

        <!-- Calendar grid -->
        <div class="flex-1 grid grid-cols-7 overflow-y-auto">
            <template x-for="(day, idx) in calendarDays" :key="idx">
                <div class="border-r border-b border-gray-100 dark:border-gray-800 min-h-[100px] p-1"
                    :class="day.isCurrentMonth ? '' : 'bg-gray-50 dark:bg-gray-800/50'">
                    <div class="text-xs font-medium mb-1 px-1"
                        :class="day.isToday ? 'text-white bg-primary-500 rounded-full w-6 h-6 flex items-center justify-center' : (day.isCurrentMonth ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400')"
                        x-text="day.date"></div>
                    <div class="space-y-0.5">
                        <template x-for="card in getCardsForDate(day.fullDate)" :key="card.id">
                            <div @click="window.location.href='/b/' + boardId"
                                class="text-[11px] px-1.5 py-0.5 rounded truncate cursor-pointer hover:opacity-80"
                                :class="{
                                    'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400': card.due_status === 'overdue',
                                    'bg-sunny-100 text-sunny-700 dark:bg-sunny-900/30 dark:text-sunny-400': card.due_status === 'due_soon',
                                    'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400': card.due_status === 'complete',
                                    'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400': !card.due_status || card.due_status === 'normal',
                                }"
                                x-text="card.title">
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <!-- Cards without due date -->
        <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-3" x-show="unscheduledCards.length > 0">
            <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">No due date (<span x-text="unscheduledCards.length"></span>)</h3>
            <div class="flex flex-wrap gap-2">
                <template x-for="card in unscheduledCards.slice(0, 10)" :key="card.id">
                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-lg" x-text="card.title"></span>
                </template>
            </div>
        </div>
    </div>

    <script>
    function calendarView(data) {
        const now = new Date();
        return {
            boardId: data.boardId,
            cards: data.cards,
            currentMonth: now.getMonth(),
            currentYear: now.getFullYear(),
            monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],

            get calendarDays() {
                const firstDay = new Date(this.currentYear, this.currentMonth, 1);
                const lastDay = new Date(this.currentYear, this.currentMonth + 1, 0);
                const startPad = firstDay.getDay();
                const days = [];
                const today = new Date();

                // Previous month padding
                const prevLastDay = new Date(this.currentYear, this.currentMonth, 0);
                for (let i = startPad - 1; i >= 0; i--) {
                    const d = prevLastDay.getDate() - i;
                    const m = this.currentMonth - 1;
                    const y = m < 0 ? this.currentYear - 1 : this.currentYear;
                    days.push({ date: d, fullDate: `${y}-${String((m + 12) % 12 + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`, isCurrentMonth: false, isToday: false });
                }

                // Current month
                for (let d = 1; d <= lastDay.getDate(); d++) {
                    const fullDate = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                    days.push({ date: d, fullDate, isCurrentMonth: true, isToday: today.getFullYear() === this.currentYear && today.getMonth() === this.currentMonth && today.getDate() === d });
                }

                // Next month padding
                const remaining = 42 - days.length;
                for (let d = 1; d <= remaining; d++) {
                    const m = this.currentMonth + 1;
                    const y = m > 11 ? this.currentYear + 1 : this.currentYear;
                    days.push({ date: d, fullDate: `${y}-${String(m % 12 + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`, isCurrentMonth: false, isToday: false });
                }

                return days;
            },

            getCardsForDate(dateStr) {
                return this.cards.filter(c => c.due_date === dateStr);
            },

            get unscheduledCards() {
                return this.cards.filter(c => !c.due_date);
            },

            prevMonth() {
                if (this.currentMonth === 0) { this.currentMonth = 11; this.currentYear--; }
                else this.currentMonth--;
            },
            nextMonth() {
                if (this.currentMonth === 11) { this.currentMonth = 0; this.currentYear++; }
                else this.currentMonth++;
            },
        };
    }
    </script>
</x-layouts.board>
