<x-layouts.knowledge title="Knowledge Hub" currentView="home">
    @php
        $catGradients = [
            'from-sky-500/35 to-cyan-600/25 border-sky-500/20',
            'from-violet-500/35 to-purple-600/25 border-violet-500/20',
            'from-indigo-500/35 to-blue-600/25 border-indigo-500/20',
            'from-fuchsia-500/30 to-pink-600/25 border-fuchsia-500/20',
            'from-teal-500/30 to-emerald-600/25 border-teal-500/20',
            'from-amber-500/25 to-orange-600/20 border-amber-500/20',
        ];
    @endphp

    {{-- Hero --}}
    <section class="text-center max-w-3xl mx-auto mb-12 lg:mb-16">
        <h1 class="text-[clamp(1.75rem,4vw,2.5rem)] font-bold text-white tracking-tight leading-tight">
            Knowledge Hub
        </h1>
        <p class="text-[15px] text-white/45 mt-3 leading-relaxed">
            Find playbooks, stack notes, HR policies, and how-tos — curated for <span class="text-white/65">{{ auth()->user()->currentOrganization()?->name }}</span>.
        </p>
        @plan_feature('knowledge_base', 'fulltext_search')
        <form method="get" action="{{ route('knowledge.search') }}" class="mt-8 relative max-w-xl mx-auto">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-white/25 pointer-events-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="search" name="q" value="" placeholder="Search articles, docs, SOPs, or resources…"
                   class="w-full rounded-2xl bg-[#14142A] border border-white/[0.1] pl-12 pr-4 py-3.5 text-[15px] text-white/90 placeholder-white/30 shadow-[0_8px_40px_-12px_rgba(0,0,0,0.6)] focus:outline-none focus:ring-2 focus:ring-sky-500/35 focus:border-sky-500/30 transition-shadow"/>
        </form>
        @endif
    </section>

    {{-- Categories grid --}}
    <section id="kb-categories" class="scroll-mt-24 mb-14 lg:mb-20">
        <div class="flex items-end justify-between gap-4 mb-6">
            <div>
                <h2 class="text-[11px] font-bold text-sky-400/80 uppercase tracking-[0.2em]">Categories</h2>
                <p class="text-[20px] font-semibold text-white/90 mt-1">Browse by topic</p>
            </div>
            @can_permission('knowledge.moderate')
            <a href="{{ route('knowledge.categories.create') }}" class="text-[12px] font-semibold text-sky-400 hover:text-sky-300">+ New category</a>
            @endif
        </div>

        @if($categories->isEmpty())
            <div class="rounded-2xl border border-dashed border-white/[0.12] bg-white/[0.02] px-8 py-16 text-center">
                <p class="text-[15px] text-white/40">No categories yet.</p>
                @can_permission('knowledge.moderate')
                    <a href="{{ route('knowledge.categories.create') }}" class="inline-flex mt-4 px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-500/25 to-violet-600/20 border border-sky-500/30 text-[13px] font-semibold text-sky-200">Create your first category</a>
                @else
                    <p class="text-[13px] text-white/30 mt-2">Ask an admin to add categories.</p>
                @endif
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
                @foreach($categories as $cat)
                    @php $g = $catGradients[$loop->index % count($catGradients)]; @endphp
                    <a href="{{ route('knowledge.categories.show', $cat) }}"
                       class="group rounded-2xl border border-white/[0.08] bg-[#14142A]/60 p-5 text-left transition-all duration-200 hover:border-sky-500/25 hover:bg-[#16162e] hover:shadow-[0_12px_40px_-16px_rgba(56,189,248,0.15)]">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $g }} border flex items-center justify-center mb-4 group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h3 class="text-[16px] font-semibold text-white group-hover:text-sky-100 transition-colors">{{ $cat->name }}</h3>
                        @if($cat->description)
                            <p class="text-[13px] text-white/40 mt-2 line-clamp-2 leading-relaxed">{{ $cat->description }}</p>
                        @endif
                        <p class="text-[11px] text-sky-400/70 mt-4 font-medium">{{ $cat->articles_count }} {{ Str::plural('article', $cat->articles_count) }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Popular / filtered articles --}}
    <section id="kb-articles" class="scroll-mt-24">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-8">
            <div>
                <h2 class="text-[11px] font-bold text-sky-400/80 uppercase tracking-[0.2em]">Library</h2>
                <p class="text-[20px] font-semibold text-white/90 mt-1">Popular articles</p>
            </div>
            <form method="get" action="{{ route('knowledge.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="category" class="rounded-xl bg-[#14142A] border border-white/[0.1] px-3 py-2 text-[13px] text-white/80 min-w-[140px] focus:ring-2 focus:ring-sky-500/30 focus:outline-none">
                    <option value="">All categories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->slug }}" @selected($filterCategory === $c->slug)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <select name="tag" class="rounded-xl bg-[#14142A] border border-white/[0.1] px-3 py-2 text-[13px] text-white/80 min-w-[130px] focus:ring-2 focus:ring-sky-500/30 focus:outline-none">
                    <option value="">All tags</option>
                    @foreach($allTags as $t)
                        <option value="{{ $t->slug }}" @selected($filterTag === $t->slug)>{{ $t->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-sky-500/25 to-violet-600/20 border border-sky-500/30 px-4 py-2 text-[12px] font-semibold text-sky-100 hover:from-sky-500/35 hover:to-violet-600/30 transition-colors">Apply</button>
                @if($filterCategory || $filterTag)
                    <a href="{{ route('knowledge.index') }}#kb-articles" class="text-[12px] text-white/40 hover:text-white/65">Clear filters</a>
                @endif
            </form>
        </div>

        @if($popularArticles->isEmpty())
            <div class="py-8 px-4 text-center rounded-2xl border border-white/[0.06] bg-white/[0.02] space-y-3">
                @if($filterCategory || $filterTag)
                    <p class="text-[14px] text-white/35">No articles match your filters.</p>
                    <a href="{{ route('knowledge.index') }}#kb-articles" class="text-[13px] font-medium text-sky-400 hover:text-sky-300">Clear filters</a>
                @else
                    <p class="text-[14px] text-white/35">No articles in the library yet.</p>
                    <p class="text-[12px] text-white/25 max-w-md mx-auto">Published articles appear here. Drafts you can open also show if you have editor access.</p>
                    @can_permission('knowledge.contribute')
                        <a href="{{ route('knowledge.articles.create') }}" class="inline-flex mt-1 px-5 py-2.5 rounded-xl bg-gradient-to-r from-sky-500/25 to-violet-600/20 border border-sky-500/30 text-[13px] font-semibold text-sky-200">Write an article</a>
                    @endif
                @endif
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6">
                @foreach($popularArticles as $article)
                    @include('knowledge.partials.article-card', [
                        'article' => $article,
                        'featured' => $featuredArticle && $featuredArticle->id === $article->id,
                    ])
                @endforeach
            </div>
        @endif
    </section>

    {{-- Bottom spotlight (reference-style horizontal feature) — only when enough content and no filters --}}
    @if($featuredArticle && $popularArticles->count() >= 4 && ! $filterCategory && ! $filterTag)
        <section class="mt-16 lg:mt-20">
            <h2 class="text-[11px] font-bold text-violet-400/80 uppercase tracking-[0.2em] mb-4">From the hub</h2>
            <a href="{{ route('knowledge.articles.show', $featuredArticle) }}"
               class="group flex flex-col md:flex-row rounded-2xl overflow-hidden border border-white/[0.1] bg-gradient-to-br from-[#14142A] to-[#1a1530] hover:border-sky-500/25 transition-all shadow-[0_20px_60px_-24px_rgba(99,102,241,0.25)]">
                <div class="md:w-2/5 min-h-[200px] bg-gradient-to-br from-sky-500/25 via-violet-600/20 to-indigo-900/50 relative">
                    <div class="absolute inset-0 opacity-50" style="background-image: radial-gradient(circle at 30% 70%, rgba(56,189,248,0.3), transparent 55%);"></div>
                    <span class="absolute top-4 left-4 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg bg-black/35 text-sky-200 border border-white/10 backdrop-blur-sm">Featured</span>
                </div>
                <div class="p-6 md:p-8 flex flex-col justify-center flex-1">
                    @if($featuredArticle->category)
                        <span class="text-[11px] font-semibold text-sky-400/90 uppercase tracking-wider">{{ $featuredArticle->category->name }}</span>
                    @endif
                    <h3 class="text-[22px] lg:text-[26px] font-bold text-white mt-2 leading-snug group-hover:text-sky-50 transition-colors">{{ $featuredArticle->title }}</h3>
                    @if($featuredArticle->excerpt)
                        <p class="text-[14px] text-white/45 mt-3 line-clamp-3 leading-relaxed">{{ $featuredArticle->excerpt }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-3 mt-6">
                        <span class="w-10 h-10 rounded-full bg-gradient-to-br from-sky-500/35 to-violet-600/35 border border-white/10 flex items-center justify-center text-[12px] font-bold text-sky-100">
                            {{ strtoupper(substr($featuredArticle->author?->name ?? '?', 0, 2)) }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-white/80">{{ $featuredArticle->author?->name ?? 'Member' }}</p>
                            <p class="text-[11px] text-white/35">Updated {{ $featuredArticle->updated_at->diffForHumans() }}</p>
                        </div>
                        <span class="text-[13px] font-semibold text-sky-400 flex items-center gap-1 group-hover:gap-2 transition-all shrink-0">
                            Read article
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </span>
                    </div>
                </div>
            </a>
        </section>
    @endif
</x-layouts.knowledge>
