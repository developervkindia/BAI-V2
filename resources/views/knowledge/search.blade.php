<x-layouts.knowledge title="Search" currentView="search">
    <div class="space-y-10">
        <div class="text-center max-w-2xl mx-auto">
            <h1 class="text-[28px] sm:text-[32px] font-bold text-white tracking-tight">Search</h1>
            <p class="text-[14px] text-white/40 mt-2">Find articles across your organization’s knowledge base.</p>
        </div>

        <form method="get" action="{{ route('knowledge.search') }}" class="max-w-3xl mx-auto">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/25 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="search" name="q" value="{{ $query }}" placeholder="Search articles, titles, and content…" autofocus
                           class="w-full rounded-2xl bg-[#14142A] border border-white/[0.1] pl-12 pr-4 py-3.5 text-[15px] text-white/90 placeholder-white/30 shadow-[0_8px_40px_-12px_rgba(0,0,0,0.5)] focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500/30"/>
                </div>
                <button type="submit" class="rounded-2xl bg-gradient-to-r from-sky-500/25 to-violet-600/25 border border-sky-500/35 text-sky-100 px-8 py-3.5 text-[14px] font-semibold hover:from-sky-500/35 hover:to-violet-600/35 transition-all shrink-0">
                    Search
                </button>
            </div>
        </form>

        @if(strlen($query) > 0)
            <div>
                <p class="text-[12px] text-white/35 mb-6">
                    <span class="text-white/55 font-medium">{{ $results->count() }}</span>
                    {{ \Illuminate\Support\Str::plural('result', $results->count()) }} for “<span class="text-sky-300/90">{{ $query }}</span>”
                </p>

                @if($results->isEmpty())
                    <div class="rounded-2xl border border-dashed border-white/[0.1] bg-white/[0.02] py-16 text-center">
                        <p class="text-[15px] text-white/45">No matches. Try different keywords or browse categories from the hub.</p>
                        <a href="{{ route('knowledge.index') }}" class="inline-block mt-4 text-[13px] font-semibold text-sky-400 hover:text-sky-300">← Back to Knowledge Hub</a>
                    </div>
                @else
                    <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($results as $article)
                            @include('knowledge.partials.article-card', ['article' => $article, 'featured' => false])
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-layouts.knowledge>
