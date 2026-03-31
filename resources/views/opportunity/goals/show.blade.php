<x-layouts.opportunity title="{{ $goal->title }}" currentView="goals">

<div class="px-6 py-6 max-w-4xl mx-auto">
    <a href="{{ route('opportunity.goals.index') }}" class="text-[12px] text-white/30 hover:text-white/55 flex items-center gap-1 mb-4">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Back to goals
    </a>

    <div class="bg-[#111122] border border-white/[0.07] rounded-2xl p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h1 class="text-[20px] font-bold text-white/90">{{ $goal->title }}</h1>
                <div class="flex items-center gap-3 mt-2">
                    <span class="text-[11px] px-2 py-0.5 rounded-full {{ $goal->status === 'on_track' ? 'bg-green-500/15 text-green-400' : ($goal->status === 'achieved' ? 'bg-teal-500/15 text-teal-400' : 'bg-amber-500/15 text-amber-400') }}">
                        {{ str_replace('_', ' ', ucfirst($goal->status)) }}
                    </span>
                    <span class="text-[12px] text-white/30">{{ ucfirst($goal->goal_type) }} goal</span>
                    @if($goal->due_date)<span class="text-[12px] text-white/30">Due {{ $goal->due_date->format('M j, Y') }}</span>@endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-teal-500/20 text-teal-400 text-[10px] font-bold flex items-center justify-center">{{ strtoupper(substr($goal->owner->name, 0, 2)) }}</div>
                <span class="text-[13px] text-white/55">{{ $goal->owner->name }}</span>
            </div>
        </div>

        {{-- Progress --}}
        <div class="mb-6" x-data="{ currentValue: {{ $goal->current_value }}, targetValue: {{ $goal->target_value ?? 100 }} }">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[13px] text-white/50">Progress</span>
                <span class="text-[14px] font-bold text-white/70">{{ round($goal->progress) }}%</span>
            </div>
            <div class="h-3 bg-white/[0.06] rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-teal-500 transition-all" style="width: {{ min($goal->progress, 100) }}%"></div>
            </div>
            <div class="flex items-center gap-3 mt-3">
                <input type="number" x-model="currentValue" step="1" class="w-24 px-2 py-1 rounded-lg bg-white/[0.05] border border-white/[0.08] text-[13px] text-white/60 focus:outline-none focus:ring-1 focus:ring-teal-500/40"/>
                <span class="text-[12px] text-white/25">/ {{ $goal->target_value }}</span>
                <button @click="fetch('/api/opp/goals/{{ $goal->id }}/progress', { method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content}, body:JSON.stringify({current_value:currentValue}) }).then(r=>r.json()).then(d=>{ if(d.goal) location.reload(); })"
                    class="px-3 py-1 rounded-lg bg-teal-500/20 text-teal-400 text-[12px] font-medium hover:bg-teal-500/30">Update</button>
            </div>
        </div>

        @if($goal->description)
            <div class="mb-6"><p class="text-[13px] text-white/50 leading-relaxed">{{ $goal->description }}</p></div>
        @endif

        {{-- Sub-goals --}}
        @if($goal->children->count() > 0)
        <div class="mb-6">
            <h3 class="text-[13px] font-semibold text-white/50 mb-3">Sub-goals</h3>
            @foreach($goal->children as $child)
                <a href="{{ route('opportunity.goals.show', $child) }}" class="flex items-center gap-3 py-2 hover:bg-white/[0.02] -mx-2 px-2 rounded-lg">
                    <span class="text-[13px] text-white/65 flex-1">{{ $child->title }}</span>
                    <div class="w-20 h-1.5 bg-white/[0.06] rounded-full overflow-hidden"><div class="h-full bg-teal-500 rounded-full" style="width:{{ $child->progress }}%"></div></div>
                    <span class="text-[11px] text-white/30">{{ round($child->progress) }}%</span>
                </a>
            @endforeach
        </div>
        @endif

        {{-- Linked projects/tasks --}}
        <div>
            <h3 class="text-[13px] font-semibold text-white/50 mb-3">Linked work</h3>
            @forelse($goal->links as $link)
                <div class="flex items-center gap-2 py-2 text-[13px] text-white/55">
                    <svg class="w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101"/></svg>
                    {{ class_basename($link->linkable_type) }}: {{ $link->linkable?->title ?? $link->linkable?->name ?? 'Unknown' }}
                </div>
            @empty
                <p class="text-[12px] text-white/20 py-3">No linked projects or tasks</p>
            @endforelse
        </div>
    </div>
</div>

</x-layouts.opportunity>
