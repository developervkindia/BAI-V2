<x-layouts.opportunity title="Goals" currentView="goals">

<div class="px-6 py-6 max-w-5xl mx-auto" x-data="{ showCreate: false, tab: 'all' }">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-[20px] font-bold text-white/90">Goals</h1>
        <button @click="showCreate = true"
            class="flex items-center gap-2 px-4 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold hover:bg-teal-400 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add goal
        </button>
    </div>

    {{-- Type tabs --}}
    <div class="flex items-center gap-0 mb-6 border-b border-white/[0.06]">
        @foreach(['all' => 'All goals', 'company' => 'Company', 'team' => 'Team', 'individual' => 'My goals'] as $key => $label)
            <button @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'border-teal-500 text-white/80' : 'border-transparent text-white/35 hover:text-white/55'"
                class="px-4 py-2.5 text-[13px] font-medium border-b-2 transition-colors">{{ $label }}</button>
        @endforeach
    </div>

    {{-- Goals list --}}
    @forelse($goals ?? [] as $type => $typeGoals)
        <div class="mb-6" x-show="tab === 'all' || tab === '{{ $type }}'">
            <h3 class="text-[13px] font-semibold text-white/40 uppercase tracking-wider mb-3">{{ ucfirst($type) }} Goals</h3>
            <div class="space-y-2">
                @foreach($typeGoals as $goal)
                <a href="{{ route('opportunity.goals.show', $goal) }}"
                   class="block bg-[#111122] border border-white/[0.07] rounded-xl p-4 hover:border-teal-500/20 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="text-[14px] font-medium text-white/80">{{ $goal->name ?? $goal->title }}</div>
                            <div class="flex items-center gap-3 mt-1.5">
                                <div class="flex-1 h-1.5 bg-white/[0.06] rounded-full overflow-hidden max-w-[200px]">
                                    <div class="h-full rounded-full bg-teal-500" style="width: {{ $goal->progress ?? 0 }}%"></div>
                                </div>
                                <span class="text-[11px] text-white/35">{{ round($goal->progress ?? 0) }}%</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full
                                    {{ ($goal->status ?? 'on_track') === 'on_track' ? 'bg-green-500/15 text-green-400' :
                                       (($goal->status ?? '') === 'at_risk' ? 'bg-amber-500/15 text-amber-400' :
                                       (($goal->status ?? '') === 'achieved' ? 'bg-teal-500/15 text-teal-400' : 'bg-red-500/15 text-red-400')) }}">
                                    {{ str_replace('_', ' ', ucfirst($goal->status ?? 'on_track')) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="w-6 h-6 rounded-full bg-teal-500/20 text-teal-400 text-[8px] font-bold flex items-center justify-center"
                                 title="{{ $goal->owner->name ?? '' }}">
                                {{ strtoupper(substr($goal->owner->name ?? '?', 0, 2)) }}
                            </div>
                            @if($goal->due_date)
                                <span class="text-[11px] text-white/30">{{ $goal->due_date->format('M j') }}</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-16">
            <h3 class="text-[15px] text-white/50 mb-2">No goals yet</h3>
            <p class="text-[13px] text-white/25 mb-4">Set goals to track progress across your organization</p>
            <button @click="showCreate = true" class="px-4 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold">Create a goal</button>
        </div>
    @endforelse

    {{-- Create Goal Modal --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showCreate = false">
        <div class="absolute inset-0 bg-black/70"></div>
        <div class="relative bg-[#1A1A2E] border border-white/[0.1] rounded-2xl w-full max-w-lg shadow-2xl" x-transition>
            <div class="px-6 pt-5 pb-3 flex items-center justify-between">
                <h2 class="text-[18px] font-bold text-white/90">New goal</h2>
                <button @click="showCreate = false" class="p-1.5 text-white/30 hover:text-white/60"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form method="POST" action="{{ route('opportunity.goals.store') }}" class="px-6 pb-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] text-white/40 mb-1.5">Goal title</label>
                    <input type="text" name="title" required class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/85 text-[14px] focus:ring-1 focus:ring-teal-500/40 focus:outline-none placeholder-white/20" placeholder="e.g., Increase revenue by 20%"/>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] text-white/40 mb-1.5">Type</label>
                        <select name="goal_type" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none">
                            <option value="individual">Individual</option>
                            <option value="team">Team</option>
                            <option value="company">Company</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] text-white/40 mb-1.5">Metric</label>
                        <select name="metric_type" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none">
                            <option value="percentage">Percentage</option>
                            <option value="number">Number</option>
                            <option value="currency">Currency</option>
                            <option value="boolean">Yes/No</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-[12px] text-white/40 mb-1.5">Target</label><input type="number" name="target_value" step="0.01" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none" placeholder="100"/></div>
                    <div><label class="block text-[12px] text-white/40 mb-1.5">Due date</label><input type="date" name="due_date" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/55 text-[13px] focus:outline-none"/></div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreate = false" class="px-4 py-2 text-[13px] text-white/50 hover:text-white/70">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-teal-500 text-white text-[13px] font-semibold hover:bg-teal-400">Create goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

</x-layouts.opportunity>
