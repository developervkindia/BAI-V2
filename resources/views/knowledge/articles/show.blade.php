<x-layouts.knowledge :title="$article->title" currentView="home">
    <x-slot name="breadcrumb">
        @php
            $articleCrumbs = [
                ['label' => 'Knowledge Hub', 'url' => route('knowledge.index')],
            ];
            if ($article->category) {
                $articleCrumbs[] = ['label' => $article->category->name, 'url' => route('knowledge.categories.show', $article->category)];
            }
            $articleCrumbs[] = ['label' => \Illuminate\Support\Str::limit($article->title, 48), 'url' => ''];
        @endphp
        @include('knowledge.partials.breadcrumbs', ['items' => $articleCrumbs])
    </x-slot>

    <div class="space-y-8 lg:space-y-10">
        <div class="flex flex-col gap-6">
            <div class="flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-wider">
                @if($article->published_at)
                    <time datetime="{{ $article->published_at->toIso8601String() }}" class="px-2.5 py-1 rounded-lg bg-white/[0.06] text-white/50 border border-white/[0.08]">
                        {{ $article->published_at->format('M j, Y') }}
                    </time>
                @endif
                @if($article->category)
                    <a href="{{ route('knowledge.categories.show', $article->category) }}" class="px-2.5 py-1 rounded-lg bg-sky-500/15 text-sky-300/90 border border-sky-500/20 hover:bg-sky-500/25 transition-colors">
                        {{ $article->category->name }}
                    </a>
                @endif
                @if($article->status === 'draft')
                    <span class="px-2.5 py-1 rounded-lg bg-amber-500/15 text-amber-200 border border-amber-500/25">Draft</span>
                @endif
                @if($article->pinned)
                    <span class="px-2.5 py-1 rounded-lg bg-violet-500/15 text-violet-200 border border-violet-500/25">Pinned</span>
                @endif
            </div>

            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <h1 class="text-[28px] sm:text-[34px] lg:text-[40px] font-bold text-white leading-[1.15] tracking-tight max-w-4xl">
                    {{ $article->title }}
                </h1>
                <div class="flex flex-wrap gap-2 shrink-0">
                    @can('update', $article)
                        <a href="{{ route('knowledge.articles.edit', $article) }}" class="rounded-xl bg-sky-500/20 border border-sky-500/30 text-sky-200 px-4 py-2.5 text-[13px] font-medium hover:bg-sky-500/30 transition-colors">Edit</a>
                    @endcan
                    @plan_feature('knowledge_base', 'revision_history')
                        @can('update', $article)
                            <a href="{{ route('knowledge.articles.revisions.index', $article) }}" class="rounded-xl border border-white/[0.1] text-white/55 px-4 py-2.5 text-[13px] hover:bg-white/[0.04] transition-colors">Revisions</a>
                        @endcan
                    @endif
                    @can('delete', $article)
                        <form method="post" action="{{ route('knowledge.articles.destroy', $article) }}" onsubmit="return confirm('Move this article to trash?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-xl border border-red-500/25 text-red-400/90 px-4 py-2.5 text-[13px] hover:bg-red-500/10 transition-colors">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="inline-flex items-center gap-3 rounded-2xl border border-sky-500/20 bg-gradient-to-r from-sky-500/[0.12] to-violet-600/[0.08] px-4 py-3 max-w-xl">
                <span class="w-11 h-11 rounded-full bg-gradient-to-br from-sky-500/35 to-violet-600/35 border border-white/10 flex items-center justify-center text-[13px] font-bold text-sky-100 shrink-0">
                    {{ strtoupper(substr($article->author?->name ?? '?', 0, 2)) }}
                </span>
                <div class="min-w-0">
                    <p class="text-[14px] font-semibold text-white/90">{{ $article->author?->name ?? 'Unknown' }}</p>
                    <p class="text-[12px] text-white/40">Updated {{ $article->updated_at->diffForHumans() }}</p>
                </div>
            </div>

            @if($article->tags->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach($article->tags as $tag)
                        <span class="text-[11px] px-2.5 py-1 rounded-lg bg-white/[0.05] text-white/45 border border-white/[0.08]">{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="rounded-2xl overflow-hidden border border-white/[0.08] min-h-[180px] md:min-h-[240px] bg-gradient-to-br from-sky-500/20 via-violet-600/15 to-indigo-950/50 relative">
            <div class="absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 25% 75%, rgba(56,189,248,0.2), transparent 50%), radial-gradient(circle at 80% 20%, rgba(139,92,246,0.18), transparent 45%);"></div>
        </div>

        @if($article->excerpt)
            <div class="rounded-2xl border border-sky-500/20 bg-gradient-to-br from-sky-500/[0.08] to-violet-600/[0.06] px-6 py-5">
                <p class="text-[11px] font-bold text-violet-300/80 uppercase tracking-[0.18em] mb-2">Summary</p>
                <p class="text-[15px] text-white/70 leading-relaxed">{{ $article->excerpt }}</p>
            </div>
        @endif

        <article class="kb-prose border-t border-white/[0.06] pt-10">
            {!! $article->body_html !!}
        </article>

        @if($article->attachments->isNotEmpty())
            <div class="rounded-2xl border border-white/[0.08] bg-[#14142A]/50 p-6">
                <h2 class="text-[11px] font-bold text-violet-400/80 uppercase tracking-[0.2em] mb-4">Attachments</h2>
                <ul class="space-y-2">
                    @foreach($article->attachments as $att)
                        <li>
                            <a href="{{ route('knowledge.files.download', $att) }}" class="text-[14px] text-sky-300 hover:text-sky-200 font-medium">{{ $att->original_name }}</a>
                            <span class="text-[11px] text-white/30 ml-2">{{ number_format($att->size / 1024, 1) }} KB</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-layouts.knowledge>
