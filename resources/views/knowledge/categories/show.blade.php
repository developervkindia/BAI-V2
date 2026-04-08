<x-layouts.knowledge :title="$category->name" currentView="home">
    <x-slot name="breadcrumb">
        @include('knowledge.partials.breadcrumbs', ['items' => [
            ['label' => 'Knowledge Hub', 'url' => route('knowledge.index')],
            ['label' => $category->name, 'url' => ''],
        ]])
    </x-slot>

    <div class="space-y-10">
        <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 pb-2 border-b border-white/[0.06]">
            <div class="max-w-2xl">
                <p class="text-[11px] font-bold text-violet-400/80 uppercase tracking-[0.2em] mb-2">Category</p>
                <h1 class="text-[28px] sm:text-[32px] font-bold text-white tracking-tight">{{ $category->name }}</h1>
                @if($category->description)
                    <p class="text-[15px] text-white/45 mt-3 leading-relaxed">{{ $category->description }}</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex rounded-xl border border-white/[0.1] p-1 bg-white/[0.03]">
                    <a href="{{ route('knowledge.categories.show', ['knowledge_category' => $category, 'sort' => 'updated']) }}"
                       class="px-3 py-1.5 rounded-lg text-[12px] font-medium transition-colors {{ $sort === 'updated' ? 'bg-sky-500/20 text-sky-200' : 'text-white/45 hover:text-white/70' }}">Updated</a>
                    <a href="{{ route('knowledge.categories.show', ['knowledge_category' => $category, 'sort' => 'title']) }}"
                       class="px-3 py-1.5 rounded-lg text-[12px] font-medium transition-colors {{ $sort === 'title' ? 'bg-sky-500/20 text-sky-200' : 'text-white/45 hover:text-white/70' }}">Title</a>
                </div>
                @can_permission('knowledge.contribute')
                    <a href="{{ route('knowledge.articles.create') }}?knowledge_category_id={{ $category->id }}" class="rounded-xl bg-sky-500/20 border border-sky-500/30 text-sky-200 px-4 py-2.5 text-[13px] font-medium hover:bg-sky-500/30 transition-colors">New article</a>
                @endif
                @can_permission('knowledge.moderate')
                    <a href="{{ route('knowledge.categories.edit', $category) }}" class="rounded-xl border border-white/[0.1] text-white/55 px-4 py-2.5 text-[13px] hover:bg-white/[0.04] transition-colors">Edit category</a>
                @endif
            </div>
        </header>

        @if($articles->isEmpty())
            <p class="text-[14px] text-white/35 py-8 text-center rounded-2xl border border-dashed border-white/[0.1] bg-white/[0.02]">No articles in this category yet.</p>
        @else
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($articles as $article)
                    @include('knowledge.partials.article-card', ['article' => $article, 'featured' => $article->pinned])
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.knowledge>
