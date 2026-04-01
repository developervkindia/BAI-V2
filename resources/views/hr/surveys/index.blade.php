<x-layouts.hr title="Surveys" currentView="surveys">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    activeTab: 'all',
    publishing: null,
    closing: null,

    publishSurvey(id) {
        this.$dispatch('confirm-modal', {
            title: 'Publish Survey',
            message: 'Are you sure you want to publish this survey?',
            confirmLabel: 'Publish',
            variant: 'info',
            onConfirm: async () => {
                this.publishing = id;
                try {
                    const res = await fetch('/api/hr/surveys/' + id + '/publish', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to publish survey');
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                } finally {
                    this.publishing = null;
                }
            }
        });
    },

    closeSurvey(id) {
        this.$dispatch('confirm-modal', {
            title: 'Close Survey',
            message: 'Are you sure you want to close this survey?',
            confirmLabel: 'Close Survey',
            variant: 'warning',
            onConfirm: async () => {
                this.closing = id;
                try {
                    const res = await fetch('/api/hr/surveys/' + id + '/close', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('Failed to close survey');
                    window.location.reload();
                } catch (e) {
                    alert(e.message);
                } finally {
                    this.closing = null;
                }
            }
        });
    }
}">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Surveys</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Create and manage employee surveys</p>
        </div>
        <a href="{{ route('hr.surveys.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/20 w-fit">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Survey
        </a>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-1 bg-white/[0.04] rounded-lg p-1 w-fit border border-white/[0.06]">
        @foreach(['all' => 'All', 'draft' => 'Draft', 'active' => 'Active', 'closed' => 'Closed'] as $tabKey => $tabLabel)
            <button @click="activeTab = '{{ $tabKey }}'"
                    :class="activeTab === '{{ $tabKey }}' ? 'bg-white/[0.10] text-white/85 shadow-sm' : 'text-white/40 hover:text-white/60'"
                    class="px-4 py-1.5 rounded-md text-[12px] font-semibold transition-all">
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    {{-- Surveys Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($surveys as $survey)
            @php
                $statusColors = [
                    'draft' => 'text-white/45 bg-white/[0.06]',
                    'active' => 'text-emerald-400 bg-emerald-500/10',
                    'closed' => 'text-red-400/70 bg-red-500/10',
                ];
                $typeColors = [
                    'engagement' => 'text-cyan-400 bg-cyan-500/10',
                    'pulse' => 'text-violet-400 bg-violet-500/10',
                    'feedback' => 'text-amber-400 bg-amber-500/10',
                    'custom' => 'text-white/50 bg-white/[0.06]',
                ];
                $sc = $statusColors[$survey->status] ?? 'text-white/45 bg-white/[0.06]';
                $tc = $typeColors[$survey->type] ?? 'text-white/50 bg-white/[0.06]';
            @endphp
            <div x-show="activeTab === 'all' || activeTab === '{{ $survey->status }}'"
                 class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden hover:border-white/[0.12] hover:bg-[#1D1D35] transition-all group">
                <a href="{{ route('hr.surveys.show', $survey) }}" class="block p-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-[10px] font-semibold {{ $tc }} px-2 py-0.5 rounded-full uppercase">{{ $survey->type }}</span>
                            <span class="text-[10px] font-semibold {{ $sc }} px-2 py-0.5 rounded-full uppercase">{{ $survey->status }}</span>
                        </div>
                        @if($survey->is_anonymous)
                            <div class="flex items-center gap-1 text-[10px] text-white/30" title="Anonymous Survey">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                <span>Anonymous</span>
                            </div>
                        @endif
                    </div>

                    <h3 class="text-[15px] font-semibold text-white/85 group-hover:text-white transition-colors">{{ $survey->title }}</h3>

                    <div class="mt-3 flex items-center gap-4 text-[11px] text-white/30">
                        @if($survey->start_date)
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ \Carbon\Carbon::parse($survey->start_date)->format('M d') }} - {{ $survey->end_date ? \Carbon\Carbon::parse($survey->end_date)->format('M d, Y') : 'Ongoing' }}
                            </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $survey->responses_count ?? 0 }} {{ Str::plural('response', $survey->responses_count ?? 0) }}
                        </span>
                    </div>
                </a>

                {{-- Action Buttons --}}
                @if($survey->status === 'draft' || $survey->status === 'active')
                    <div class="px-5 pb-4 flex items-center gap-2">
                        @if($survey->status === 'draft')
                            <button @click.prevent="publishSurvey({{ $survey->id }})"
                                    :disabled="publishing === {{ $survey->id }}"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-semibold hover:bg-emerald-500/20 transition-colors disabled:opacity-50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                <span x-text="publishing === {{ $survey->id }} ? 'Publishing...' : 'Publish'"></span>
                            </button>
                        @endif
                        @if($survey->status === 'active')
                            <button @click.prevent="closeSurvey({{ $survey->id }})"
                                    :disabled="closing === {{ $survey->id }}"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-[11px] font-semibold hover:bg-red-500/20 transition-colors disabled:opacity-50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                                <span x-text="closing === {{ $survey->id }} ? 'Closing...' : 'Close'"></span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="col-span-full bg-[#17172A] border border-white/[0.07] rounded-xl p-16 text-center">
                <svg class="w-12 h-12 text-white/10 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
                <p class="text-[15px] text-white/35 font-medium">No surveys yet</p>
                <p class="text-[12px] text-white/20 mt-1 mb-4">Create your first survey to gather employee feedback</p>
                <a href="{{ route('hr.surveys.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Survey
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($surveys->hasPages())
        <div class="flex justify-center pt-2">
            {{ $surveys->links() }}
        </div>
    @endif

</div>

</x-layouts.hr>