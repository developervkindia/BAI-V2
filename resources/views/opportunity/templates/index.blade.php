<x-layouts.opportunity title="Templates" currentView="templates">

<div class="px-6 py-6 max-w-5xl mx-auto">
    <h1 class="text-[20px] font-bold text-white/90 mb-6">Templates</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($templates ?? [] as $tmpl)
            <div class="bg-[#111122] border border-white/[0.07] rounded-2xl p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: {{ $tmpl->color }}22">
                        <svg class="w-5 h-5" style="color: {{ $tmpl->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[14px] font-semibold text-white/80">{{ $tmpl->name }}</h3>
                        <p class="text-[11px] text-white/30">{{ $tmpl->sections_count ?? 0 }} sections · {{ $tmpl->tasks_count ?? 0 }} tasks</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('opportunity.templates.create', $tmpl) }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="name" required placeholder="New project name" class="flex-1 px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[12px] text-white/65 focus:outline-none focus:ring-1 focus:ring-teal-500/40 placeholder-white/20"/>
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-teal-500/20 text-teal-400 text-[12px] font-medium hover:bg-teal-500/30">Use template</button>
                </form>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <h3 class="text-[15px] text-white/50 mb-2">No templates yet</h3>
                <p class="text-[13px] text-white/25">Save any project as a template from its settings</p>
            </div>
        @endforelse
    </div>
</div>

</x-layouts.opportunity>
