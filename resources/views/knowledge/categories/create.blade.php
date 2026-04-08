<x-layouts.knowledge title="New category" currentView="categories">
    <h1 class="text-[22px] font-bold text-white/90 mb-6">New category</h1>
    <form method="post" action="{{ route('knowledge.categories.store') }}" class="max-w-lg space-y-4">
        @csrf
        <div>
            <label class="block text-[12px] font-medium text-white/45 mb-1.5">Name</label>
            <input name="name" value="{{ old('name') }}" required class="w-full rounded-lg bg-white/[0.06] border border-white/[0.1] px-3 py-2 text-[14px] text-white/85"/>
        </div>
        <div>
            <label class="block text-[12px] font-medium text-white/45 mb-1.5">Description</label>
            <textarea name="description" rows="3" class="w-full rounded-lg bg-white/[0.06] border border-white/[0.1] px-3 py-2 text-[14px] text-white/85">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-[12px] font-medium text-white/45 mb-1.5">Sort order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-32 rounded-lg bg-white/[0.06] border border-white/[0.1] px-3 py-2 text-[14px] text-white/85"/>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="rounded-lg bg-sky-500/25 border border-sky-500/35 text-sky-100 px-5 py-2 text-[13px] font-medium">Create</button>
            <a href="{{ route('knowledge.categories.index') }}" class="rounded-lg border border-white/[0.1] text-white/50 px-5 py-2 text-[13px]">Cancel</a>
        </div>
    </form>
</x-layouts.knowledge>
