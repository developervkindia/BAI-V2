<x-layouts.app title="Search" :workspaces="$workspaces ?? collect()">
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-heading">
                Search results for "<span class="text-primary-600">{{ $query }}</span>"
            </h1>
        </div>

        <!-- Boards -->
        @if($boards->count())
            <section>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Boards ({{ $boards->count() }})</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($boards as $board)
                        <x-board-card :board="$board" />
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Cards -->
        @if($cards->count())
            <section>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Cards ({{ $cards->count() }})</h2>
                <div class="space-y-3">
                    @foreach($cards as $card)
                        <a href="{{ route('boards.show', $card->board) }}" class="block bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-primary-200 transition-all">
                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-900 dark:text-white">{{ $card->title }}</h3>
                                    @if($card->description)
                                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ Str::limit(strip_tags($card->description), 150) }}</p>
                                    @endif
                                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-400">
                                        <span>{{ $card->board->name }}</span>
                                        <span>&rsaquo;</span>
                                        <span>{{ $card->boardList->name }}</span>
                                    </div>
                                </div>
                                @if($card->labels->count())
                                    <div class="flex gap-1">
                                        @foreach($card->labels->take(3) as $label)
                                            <span class="w-6 h-2 rounded-full" style="background: {{ \App\Models\Label::defaultColors()[$label->color] ?? $label->color }};"></span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($cards->isEmpty() && $boards->isEmpty() && $query)
            <div class="text-center py-16">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <h3 class="text-lg font-semibold text-gray-500 mb-2">No results found</h3>
                <p class="text-gray-400">Try searching for something else.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
