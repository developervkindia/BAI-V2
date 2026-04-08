<x-layouts.knowledge :title="'Revisions: '.$article->title" currentView="home">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-[20px] font-bold text-white/90">Revision history</h1>
                <p class="text-[12px] text-white/35 mt-1">{{ $article->title }}</p>
            </div>
            <a href="{{ route('knowledge.articles.show', $article) }}" class="text-[13px] text-sky-400 hover:text-sky-300">Back to article</a>
        </div>

        <ul class="space-y-4">
            @foreach($revisions as $rev)
                <li class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 mb-2">
                        <span class="text-[13px] font-medium text-white/75">{{ $rev->title }}</span>
                        <span class="text-[11px] text-white/30">{{ $rev->created_at->format('Y-m-d H:i') }} · {{ $rev->user?->name }}</span>
                    </div>
                    <p class="text-[12px] text-white/45 line-clamp-6 border border-white/[0.05] rounded-lg p-3 bg-black/20">
                        {{ \Illuminate\Support\Str::limit(strip_tags($rev->body_html), 400) }}
                    </p>
                    @can('update', $article)
                        <form method="post" action="{{ route('knowledge.articles.revisions.restore', [$article, $rev]) }}" class="mt-3" onsubmit="return confirm('Restore this revision? Current content will be saved as a new revision first.');">
                            @csrf
                            <button type="submit" class="text-[12px] text-sky-400 hover:text-sky-300">Restore this version</button>
                        </form>
                    @endcan
                </li>
            @endforeach
        </ul>

        <div>{{ $revisions->links() }}</div>
    </div>
</x-layouts.knowledge>
