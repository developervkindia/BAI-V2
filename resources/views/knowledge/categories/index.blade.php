<x-layouts.knowledge title="Categories" currentView="categories">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-[22px] font-bold text-white/90">Categories</h1>
        <a href="{{ route('knowledge.categories.create') }}" class="inline-flex items-center justify-center rounded-lg bg-sky-500/20 border border-sky-500/30 text-sky-200 px-4 py-2 text-[13px] font-medium hover:bg-sky-500/30">New category</a>
    </div>
    <ul class="space-y-2">
        @foreach($categories as $cat)
            <li class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-white/[0.07] bg-white/[0.02] px-4 py-3">
                <div>
                    <a href="{{ route('knowledge.categories.show', $cat) }}" class="text-[14px] font-medium text-white/80 hover:text-sky-200">{{ $cat->name }}</a>
                    <p class="text-[11px] text-white/30">{{ $cat->articles_count }} articles · order {{ $cat->sort_order }}</p>
                </div>
                <a href="{{ route('knowledge.categories.edit', $cat) }}" class="text-[12px] text-sky-400 hover:text-sky-300">Edit</a>
            </li>
        @endforeach
    </ul>
</x-layouts.knowledge>
