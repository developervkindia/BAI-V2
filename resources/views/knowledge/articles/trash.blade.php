<x-layouts.knowledge title="Trash" currentView="trash">
    <h1 class="text-[22px] font-bold text-white/90 mb-6">Deleted articles</h1>
    <ul class="space-y-3">
        @forelse($articles as $article)
            <li class="rounded-lg border border-white/[0.07] bg-white/[0.02] px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[14px] font-medium text-white/80">{{ $article->title }}</p>
                    <p class="text-[11px] text-white/30">{{ $article->category?->name }} · deleted {{ $article->deleted_at?->diffForHumans() }}</p>
                </div>
                <div class="flex gap-2">
                    <form method="post" action="{{ route('knowledge.articles.restore', $article->id) }}">
                        @csrf
                        <button type="submit" class="text-[12px] text-sky-400 hover:text-sky-300">Restore</button>
                    </form>
                    <form method="post" action="{{ route('knowledge.articles.forceDestroy', $article->id) }}" onsubmit="return confirm('Permanently delete? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[12px] text-red-400/90 hover:text-red-400">Delete forever</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="text-[13px] text-white/35">Trash is empty.</li>
        @endforelse
    </ul>
    <div class="mt-8">
        {{ $articles->links() }}
    </div>
</x-layouts.knowledge>
