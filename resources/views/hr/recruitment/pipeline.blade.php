<x-layouts.hr title="Recruitment Pipeline" currentView="recruitment">

@php
    $stages = ['applied', 'screening', 'interview', 'assessment', 'offer', 'hired', 'rejected'];
    $stageLabels = [
        'applied' => 'Applied',
        'screening' => 'Screening',
        'interview' => 'Interview',
        'assessment' => 'Assessment',
        'offer' => 'Offer',
        'hired' => 'Hired',
        'rejected' => 'Rejected',
    ];
    $stageColors = [
        'applied' => ['text' => 'text-white/60', 'bg' => 'bg-white/[0.06]', 'border' => 'border-white/[0.08]', 'dot' => 'bg-white/30', 'header' => 'bg-white/[0.03]'],
        'screening' => ['text' => 'text-amber-400', 'bg' => 'bg-amber-500/10', 'border' => 'border-amber-500/20', 'dot' => 'bg-amber-400', 'header' => 'bg-amber-500/5'],
        'interview' => ['text' => 'text-blue-400', 'bg' => 'bg-blue-500/10', 'border' => 'border-blue-500/20', 'dot' => 'bg-blue-400', 'header' => 'bg-blue-500/5'],
        'assessment' => ['text' => 'text-purple-400', 'bg' => 'bg-purple-500/10', 'border' => 'border-purple-500/20', 'dot' => 'bg-purple-400', 'header' => 'bg-purple-500/5'],
        'offer' => ['text' => 'text-cyan-400', 'bg' => 'bg-cyan-500/10', 'border' => 'border-cyan-500/20', 'dot' => 'bg-cyan-400', 'header' => 'bg-cyan-500/5'],
        'hired' => ['text' => 'text-green-400', 'bg' => 'bg-green-500/10', 'border' => 'border-green-500/20', 'dot' => 'bg-green-400', 'header' => 'bg-green-500/5'],
        'rejected' => ['text' => 'text-red-400', 'bg' => 'bg-red-500/10', 'border' => 'border-red-500/20', 'dot' => 'bg-red-400', 'header' => 'bg-red-500/5'],
    ];
@endphp

<div class="p-5 lg:p-7 space-y-6" x-data="{
    moving: {},
    moveError: '',
    showInterviewModal: false,
    interviewForm: {
        hr_candidate_id: null,
        candidateName: '',
        round: 1,
        scheduled_at: '',
        duration_minutes: 60,
        mode: 'video',
    },
    interviewSubmitting: false,
    interviewErrors: {},

    async moveCandidate(candidateId, newStage) {
        this.moving[candidateId] = true;
        this.moveError = '';

        try {
            const response = await fetch('/api/hr/candidates/' + candidateId + '/move', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ stage: newStage }),
            });

            const data = await response.json();

            if (!response.ok) {
                this.moveError = data.message || 'Failed to move candidate';
                return;
            }

            window.location.reload();
        } catch (err) {
            this.moveError = 'Network error. Please try again.';
        } finally {
            delete this.moving[candidateId];
        }
    },

    openInterviewModal(candidateId, candidateName) {
        this.interviewForm = {
            hr_candidate_id: candidateId,
            candidateName: candidateName,
            round: 1,
            scheduled_at: '',
            duration_minutes: 60,
            mode: 'video',
        };
        this.interviewErrors = {};
        this.showInterviewModal = true;
    },

    async scheduleInterview() {
        this.interviewSubmitting = true;
        this.interviewErrors = {};

        try {
            const response = await fetch('/api/hr/interviews', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    hr_candidate_id: this.interviewForm.hr_candidate_id,
                    round: this.interviewForm.round,
                    scheduled_at: this.interviewForm.scheduled_at,
                    duration_minutes: this.interviewForm.duration_minutes,
                    mode: this.interviewForm.mode,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.errors) {
                    this.interviewErrors = data.errors;
                } else {
                    this.interviewErrors = { general: [data.message || 'Failed to schedule interview'] };
                }
                return;
            }

            this.showInterviewModal = false;
            window.location.reload();
        } catch (err) {
            this.interviewErrors = { general: ['Network error. Please try again.'] };
        } finally {
            this.interviewSubmitting = false;
        }
    },

    getNextStages(currentStage) {
        const order = ['applied', 'screening', 'interview', 'assessment', 'offer', 'hired'];
        const idx = order.indexOf(currentStage);
        const next = [];
        if (idx >= 0 && idx < order.length - 1) {
            next.push(order[idx + 1]);
        }
        if (currentStage !== 'rejected' && currentStage !== 'hired') {
            next.push('rejected');
        }
        return next;
    },

    stageLabel(stage) {
        const labels = { applied: 'Applied', screening: 'Screening', interview: 'Interview', assessment: 'Assessment', offer: 'Offer', hired: 'Hired', rejected: 'Rejected' };
        return labels[stage] || stage;
    },
}">

    {{-- Page Header --}}
    <div class="flex items-start gap-4">
        <a href="{{ route('hr.recruitment.show-posting', $jobPosting) }}"
           class="w-9 h-9 rounded-lg bg-white/[0.04] hover:bg-white/[0.08] border border-white/[0.06] flex items-center justify-center transition-colors mt-1">
            <svg class="w-4 h-4 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="flex-1">
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Recruitment Pipeline</h1>
            <p class="text-[13px] text-white/40 mt-0.5">{{ $jobPosting->title }} &mdash; {{ $jobPosting->department->name ?? '' }}</p>
        </div>
    </div>

    {{-- Move Error --}}
    <template x-if="moveError">
        <div class="bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-3.5">
            <p class="text-[13px] text-red-400" x-text="moveError"></p>
        </div>
    </template>

    {{-- Pipeline Board --}}
    <div class="flex gap-4 overflow-x-auto pb-4 -mx-5 lg:-mx-7 px-5 lg:px-7">
        @foreach($stages as $stage)
            @php
                $candidates = $pipeline[$stage] ?? collect();
                $colors = $stageColors[$stage];
            @endphp
            <div class="flex-shrink-0 w-[280px]">
                {{-- Column Header --}}
                <div class="bg-[#17172A] border border-white/[0.07] rounded-t-xl px-4 py-3 {{ $colors['header'] }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $colors['dot'] }}"></span>
                            <h3 class="text-[13px] font-semibold {{ $colors['text'] }}">{{ $stageLabels[$stage] }}</h3>
                        </div>
                        <span class="text-[11px] font-bold {{ $colors['text'] }} {{ $colors['bg'] }} px-2 py-0.5 rounded-full">
                            {{ $candidates->count() }}
                        </span>
                    </div>
                </div>

                {{-- Column Body --}}
                <div class="bg-[#12121E] border-x border-b border-white/[0.07] rounded-b-xl p-2.5 space-y-2.5 min-h-[200px]">
                    @forelse($candidates as $candidate)
                        @php
                            $initials = strtoupper(collect(explode(' ', $candidate->name))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                        @endphp
                        <div class="bg-[#17172A] border border-white/[0.07] rounded-lg p-3.5 hover:border-white/[0.12] transition-all duration-200 group">
                            <div class="flex items-start gap-2.5 mb-2.5">
                                <div class="w-8 h-8 rounded-lg {{ $colors['bg'] }} {{ $colors['text'] }} text-[10px] font-bold flex items-center justify-center shrink-0">
                                    {{ $initials }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-semibold text-white/80 truncate">{{ $candidate->name }}</p>
                                    @if($candidate->current_designation)
                                        <p class="text-[11px] text-white/35 truncate">{{ $candidate->current_designation }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-3 text-[11px] text-white/35 mb-3">
                                @if($candidate->experience_years)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $candidate->experience_years }}y
                                    </span>
                                @endif
                                @if($candidate->source)
                                    <span class="capitalize">{{ $candidate->source }}</span>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-1.5 flex-wrap opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                 x-data="{ nextStages: getNextStages('{{ $stage }}') }">
                                <template x-for="ns in nextStages" :key="ns">
                                    <button @click="moveCandidate({{ $candidate->id }}, ns)"
                                            :disabled="moving[{{ $candidate->id }}]"
                                            class="px-2 py-1 text-[10px] font-semibold rounded-md border transition-colors disabled:opacity-40"
                                            :class="ns === 'rejected'
                                                ? 'text-red-400 bg-red-500/10 border-red-500/20 hover:bg-red-500/20'
                                                : 'text-cyan-400 bg-cyan-500/10 border-cyan-500/20 hover:bg-cyan-500/20'"
                                            x-text="ns === 'rejected' ? 'Reject' : 'Move to ' + stageLabel(ns)">
                                    </button>
                                </template>

                                @if($stage === 'interview')
                                    <button @click="openInterviewModal({{ $candidate->id }}, '{{ addslashes($candidate->name) }}')"
                                            class="px-2 py-1 text-[10px] font-semibold text-blue-400 bg-blue-500/10 border border-blue-500/20 hover:bg-blue-500/20 rounded-md transition-colors">
                                        Schedule
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center justify-center py-8">
                            <p class="text-[11px] text-white/20">No candidates</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Schedule Interview Modal --}}
    <template x-if="showInterviewModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="showInterviewModal = false">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showInterviewModal = false"></div>
            <div class="relative bg-[#17172A] border border-white/[0.10] rounded-2xl w-full max-w-md shadow-2xl" @click.stop>

                <div class="px-6 py-5 border-b border-white/[0.06]">
                    <h3 class="text-[16px] font-semibold text-white/85">Schedule Interview</h3>
                    <p class="text-[13px] text-white/40 mt-0.5">For <span class="text-white/65" x-text="interviewForm.candidateName"></span></p>
                </div>

                <div class="px-6 py-5 space-y-5">
                    {{-- Error --}}
                    <template x-if="interviewErrors.general">
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
                            <template x-for="err in interviewErrors.general" :key="err">
                                <p class="text-[12px] text-red-400" x-text="err"></p>
                            </template>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Round</label>
                            <input type="number" x-model.number="interviewForm.round" min="1" max="10"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Duration (min)</label>
                            <input type="number" x-model.number="interviewForm.duration_minutes" min="15" step="15"
                                   class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Scheduled At</label>
                        <input type="datetime-local" x-model="interviewForm.scheduled_at"
                               class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/85 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors">
                        <template x-if="interviewErrors.scheduled_at">
                            <p class="text-[12px] text-red-400 mt-1" x-text="interviewErrors.scheduled_at[0]"></p>
                        </template>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-white/35 uppercase tracking-widest mb-2">Mode</label>
                        <select x-model="interviewForm.mode"
                                class="w-full bg-white/[0.04] border border-white/[0.08] rounded-lg px-4 py-3 text-[14px] text-white/80 focus:outline-none focus:border-cyan-500/40 focus:ring-1 focus:ring-cyan-500/20 transition-colors appearance-none">
                            <option value="video" class="bg-[#17172A]">Video Call</option>
                            <option value="phone" class="bg-[#17172A]">Phone</option>
                            <option value="in_person" class="bg-[#17172A]">In Person</option>
                        </select>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-white/[0.06] flex items-center justify-end gap-3">
                    <button @click="showInterviewModal = false"
                            class="px-4 py-2.5 text-[13px] font-medium text-white/45 hover:text-white/65 bg-white/[0.04] hover:bg-white/[0.06] rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button @click="scheduleInterview()" :disabled="interviewSubmitting"
                            class="px-5 py-2.5 text-[13px] font-semibold text-white bg-blue-500/80 hover:bg-blue-500/90 disabled:opacity-40 disabled:cursor-not-allowed rounded-lg transition-colors flex items-center gap-2">
                        <svg x-show="interviewSubmitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span x-text="interviewSubmitting ? 'Scheduling...' : 'Schedule Interview'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>

</x-layouts.hr>
