<x-layouts.opportunity title="Projects" currentView="projects">

<div class="px-6 py-6 max-w-5xl mx-auto" x-data="{ showCreateModal: false }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-[20px] font-bold text-white/90">Projects</h1>
        <button @click="showCreateModal = true"
            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold hover:bg-teal-400 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Project
        </button>
    </div>

    {{-- Project cards grid --}}
    @if($projects->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($projects as $proj)
        <a href="{{ route('opportunity.projects.show', $proj) }}"
           class="bg-[#111122] border border-white/[0.07] rounded-2xl overflow-hidden hover:border-teal-500/30 hover:shadow-lg hover:shadow-teal-500/5 transition-all group">
            {{-- Color bar --}}
            <div class="h-1.5" style="background: {{ $proj->color ?? '#14B8A6' }}"></div>

            <div class="p-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" style="background: {{ $proj->color ?? '#14B8A6' }}18">
                        <svg class="w-5 h-5" style="color: {{ $proj->color ?? '#14B8A6' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-[14px] font-semibold text-white/80 group-hover:text-white/95 truncate">{{ $proj->name }}</h3>
                        @php
                            $total = $proj->tasks_count;
                            $done = $proj->completed_tasks_count;
                            $pct = $total > 0 ? round(($done / $total) * 100) : 0;
                        @endphp
                        <p class="text-[11px] text-white/30 mt-0.5">{{ $total }} tasks · {{ $done }} completed</p>
                    </div>
                </div>

                {{-- Progress bar --}}
                @if($total > 0)
                <div class="mt-3 h-1 bg-white/[0.06] rounded-full overflow-hidden">
                    <div class="h-full rounded-full" style="width: {{ $pct }}%; background: {{ $proj->color ?? '#14B8A6' }}"></div>
                </div>
                @endif

                {{-- Footer --}}
                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-teal-500/20 text-teal-400 text-[8px] font-bold flex items-center justify-center">
                            {{ strtoupper(substr($proj->owner->name ?? '', 0, 2)) }}
                        </div>
                        <span class="text-[11px] text-white/30">{{ $proj->owner->name ?? '' }}</span>
                    </div>
                    <span class="text-[10px] px-2 py-0.5 rounded-full
                        {{ $proj->status === 'on_track' ? 'bg-green-500/15 text-green-400' :
                           ($proj->status === 'at_risk' ? 'bg-amber-500/15 text-amber-400' :
                           ($proj->status === 'off_track' ? 'bg-red-500/15 text-red-400' : 'bg-white/[0.06] text-white/35')) }}">
                        {{ str_replace('_', ' ', ucfirst($proj->status)) }}
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="text-center py-20">
        <div class="w-16 h-16 rounded-2xl bg-teal-500/10 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-teal-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <h3 class="text-[16px] font-semibold text-white/60 mb-1">No projects yet</h3>
        <p class="text-[13px] text-white/30 mb-4">Create your first project to get started</p>
        <button @click="showCreateModal = true"
            class="px-4 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold hover:bg-teal-400 transition-colors">
            Create a project
        </button>
    </div>
    @endif

    {{-- ================================================================ --}}
    {{-- CREATE PROJECT MODAL (Asana-style)                               --}}
    {{-- ================================================================ --}}
    <div x-show="showCreateModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="showCreateModal = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

        <div class="relative bg-[#1A1A2E] border border-white/[0.1] rounded-2xl w-full max-w-lg shadow-2xl"
             x-transition>

            {{-- Modal header --}}
            <div class="flex items-center justify-between px-6 pt-5 pb-3">
                <h2 class="text-[18px] font-bold text-white/90">New project</h2>
                <button @click="showCreateModal = false" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/30 hover:text-white/60 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('opportunity.projects.store') }}" class="px-6 pb-6 space-y-4">
                @csrf

                {{-- Project name --}}
                <div>
                    <label class="block text-[12px] font-medium text-white/40 mb-1.5">Project name</label>
                    <input type="text" name="name" required autofocus placeholder="Enter project name..."
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/85 text-[14px] focus:ring-1 focus:ring-teal-500/40 focus:outline-none placeholder-white/20"/>
                    @error('name')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-[12px] font-medium text-white/40 mb-1.5">Description (optional)</label>
                    <textarea name="description" rows="2" placeholder="What's this project about?"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/75 text-[13px] focus:ring-1 focus:ring-teal-500/40 focus:outline-none placeholder-white/20 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Color --}}
                    <div>
                        <label class="block text-[12px] font-medium text-white/40 mb-1.5">Color</label>
                        <div class="flex gap-2 flex-wrap" x-data="{ selectedColor: '#14B8A6' }">
                            <input type="hidden" name="color" x-model="selectedColor"/>
                            @foreach(['#14B8A6', '#3B82F6', '#8B5CF6', '#EC4899', '#F43F5E', '#F59E0B', '#10B981', '#6366F1', '#EF4444', '#6B7280'] as $color)
                                <button type="button" @click="selectedColor = '{{ $color }}'"
                                    class="w-7 h-7 rounded-lg transition-all border-2"
                                    :class="selectedColor === '{{ $color }}' ? 'border-white/60 scale-110' : 'border-transparent'"
                                    style="background: {{ $color }}"></button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Privacy --}}
                    <div>
                        <label class="block text-[12px] font-medium text-white/40 mb-1.5">Privacy</label>
                        <select name="visibility"
                            class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:ring-1 focus:ring-teal-500/40 focus:outline-none">
                            <option value="public">Public to team</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-medium text-white/40 mb-1.5">Start date</label>
                        <input type="date" name="start_date"
                            class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/55 text-[13px] focus:ring-1 focus:ring-teal-500/40 focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[12px] font-medium text-white/40 mb-1.5">Due date</label>
                        <input type="date" name="due_date"
                            class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/55 text-[13px] focus:ring-1 focus:ring-teal-500/40 focus:outline-none"/>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showCreateModal = false"
                        class="px-4 py-2 rounded-lg text-[13px] text-white/50 hover:text-white/70 hover:bg-white/[0.05] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold hover:bg-teal-400 transition-colors">
                        Create project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-layouts.opportunity>
