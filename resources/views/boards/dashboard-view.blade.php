<x-layouts.board :board="$board">
    @php
        $allCards = $board->lists->flatMap(fn($l) => $l->cards);
        $totalCards = $allCards->count();
        $overdueCards = $allCards->filter(fn($c) => $c->due_status === 'overdue')->count();
        $completedCards = $allCards->filter(fn($c) => $c->due_date_complete)->count();
        $cardsWithDue = $allCards->filter(fn($c) => $c->due_date)->count();

        $listStats = $board->lists->map(fn($l) => [
            'name' => $l->name,
            'count' => $l->cards->count(),
        ]);

        $memberStats = [];
        foreach ($allCards as $card) {
            foreach ($card->members as $m) {
                if (!isset($memberStats[$m->id])) {
                    $memberStats[$m->id] = ['name' => $m->name, 'count' => 0];
                }
                $memberStats[$m->id]['count']++;
            }
        }
        $memberStats = collect($memberStats)->sortByDesc('count')->values();

        $checklistTotal = 0; $checklistChecked = 0;
        foreach ($allCards as $card) {
            $p = $card->checklist_progress;
            $checklistTotal += $p['total'];
            $checklistChecked += $p['checked'];
        }
    @endphp

    <div class="h-full overflow-y-auto bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm p-6">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Stats cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-3xl font-bold text-gray-800 dark:text-gray-200">{{ $totalCards }}</div>
                    <div class="text-sm text-gray-500 mt-1">Total Cards</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-3xl font-bold text-danger-600">{{ $overdueCards }}</div>
                    <div class="text-sm text-gray-500 mt-1">Overdue</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-3xl font-bold text-success-600">{{ $completedCards }}</div>
                    <div class="text-sm text-gray-500 mt-1">Completed</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-3xl font-bold text-primary-600">{{ $checklistTotal > 0 ? round(($checklistChecked / $checklistTotal) * 100) : 0 }}%</div>
                    <div class="text-sm text-gray-500 mt-1">Checklist Done</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Cards per list (bar chart) -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4 uppercase tracking-wide">Cards per List</h3>
                    @php $maxCount = $listStats->max('count') ?: 1; @endphp
                    <div class="space-y-3">
                        @foreach($listStats as $stat)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700 dark:text-gray-300 truncate">{{ $stat['name'] }}</span>
                                    <span class="text-gray-500 font-medium">{{ $stat['count'] }}</span>
                                </div>
                                <div class="w-full h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-primary-500 to-secondary-500 rounded-full transition-all" style="width: {{ ($stat['count'] / $maxCount) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Member workload -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4 uppercase tracking-wide">Member Workload</h3>
                    @if($memberStats->isEmpty())
                        <p class="text-gray-400 text-sm">No cards assigned to members</p>
                    @else
                        @php $maxMemberCount = $memberStats->max('count') ?: 1; @endphp
                        <div class="space-y-3">
                            @foreach($memberStats->take(10) as $stat)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white text-[8px] font-bold flex items-center justify-center">
                                                {{ strtoupper(substr($stat['name'], 0, 2)) }}
                                            </div>
                                            <span class="text-gray-700 dark:text-gray-300">{{ $stat['name'] }}</span>
                                        </div>
                                        <span class="text-gray-500 font-medium">{{ $stat['count'] }} cards</span>
                                    </div>
                                    <div class="w-full h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-accent-500 to-primary-500 rounded-full transition-all" style="width: {{ ($stat['count'] / $maxMemberCount) * 100 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Due date overview -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-4 uppercase tracking-wide">Due Date Overview</h3>
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-xl">
                        <div class="text-2xl font-bold text-danger-600">{{ $overdueCards }}</div>
                        <div class="text-xs text-danger-500 mt-1">Overdue</div>
                    </div>
                    <div class="p-4 bg-sunny-50 dark:bg-sunny-900/20 rounded-xl">
                        <div class="text-2xl font-bold text-sunny-600">{{ $allCards->filter(fn($c) => $c->due_status === 'due_soon')->count() }}</div>
                        <div class="text-xs text-sunny-500 mt-1">Due Soon</div>
                    </div>
                    <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-xl">
                        <div class="text-2xl font-bold text-success-600">{{ $completedCards }}</div>
                        <div class="text-xs text-success-500 mt-1">Complete</div>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                        <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $totalCards - $cardsWithDue }}</div>
                        <div class="text-xs text-gray-500 mt-1">No Date</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.board>
