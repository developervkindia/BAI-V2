<x-layouts.hr title="Respond: {{ $survey->title }}" currentView="surveys">

<div class="p-5 lg:p-7 space-y-6" x-data="surveyRespond()">

    {{-- Success State --}}
    <template x-if="submitted">
        <div class="max-w-lg mx-auto py-20 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-500/15 flex items-center justify-center mx-auto mb-5">
                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="text-[22px] font-bold text-white/85 tracking-tight">Thank You!</h2>
            <p class="text-[14px] text-white/45 mt-2">Your response has been recorded successfully.</p>
            @if($survey->is_anonymous)
                <p class="text-[12px] text-white/30 mt-1">Your response is anonymous.</p>
            @endif
            <a href="{{ route('hr.surveys.index') }}"
               class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Surveys
            </a>
        </div>
    </template>

    {{-- Survey Form --}}
    <template x-if="!submitted">
        <div class="max-w-3xl mx-auto space-y-6">

            {{-- Header --}}
            <div class="flex items-start gap-3">
                <a href="{{ route('hr.surveys.show', $survey) }}" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/60 transition-colors mt-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-[22px] font-bold text-white/85 tracking-tight">{{ $survey->title }}</h1>
                    @if($survey->description)
                        <p class="text-[14px] text-white/45 mt-1.5">{{ $survey->description }}</p>
                    @endif
                    @if($survey->is_anonymous)
                        <div class="flex items-center gap-1.5 mt-2 text-[11px] text-white/30">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            This survey is anonymous. Your identity will not be recorded.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Error --}}
            <template x-if="errorMsg">
                <div class="flex items-center gap-2 p-3 rounded-lg bg-red-500/10 border border-red-500/20">
                    <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-[13px] text-red-400" x-text="errorMsg"></span>
                </div>
            </template>

            {{-- Questions --}}
            @foreach($survey->questions->sortBy('sort_order') as $question)
                @php
                    $qId = $question->id;
                    $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                @endphp
                <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-6 space-y-4">
                    <div>
                        <div class="flex items-start gap-1">
                            <h3 class="text-[14px] font-semibold text-white/85">{{ $question->question }}</h3>
                            @if($question->is_required)
                                <span class="text-red-400 text-sm mt-0.5">*</span>
                            @endif
                        </div>
                    </div>

                    {{-- Text --}}
                    @if($question->type === 'text')
                        <textarea x-model="responses[{{ $qId }}]" rows="3" placeholder="Type your answer here..."
                                  class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] resize-none"></textarea>
                    @endif

                    {{-- Rating --}}
                    @if($question->type === 'rating')
                        <div class="flex items-center gap-2">
                            @for($star = 1; $star <= 5; $star++)
                                <button type="button" @click="setRating({{ $qId }}, {{ $star }})"
                                        class="p-1 rounded-lg transition-all hover:scale-110"
                                        :class="(responses[{{ $qId }}] || 0) >= {{ $star }} ? 'text-amber-400' : 'text-white/15 hover:text-white/30'">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </button>
                            @endfor
                            <span class="text-[13px] text-white/35 ml-2" x-show="responses[{{ $qId }}]" x-text="responses[{{ $qId }}] + ' / 5'"></span>
                        </div>
                    @endif

                    {{-- Multiple Choice --}}
                    @if($question->type === 'multiple_choice')
                        <div class="space-y-2">
                            @foreach($options as $opt)
                                <label class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg border cursor-pointer transition-colors"
                                       :class="isMultiSelected({{ $qId }}, '{{ addslashes($opt) }}') ? 'bg-cyan-500/10 border-cyan-500/30' : 'bg-white/[0.03] border-white/[0.06] hover:bg-white/[0.05]'">
                                    <div class="w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-colors"
                                         :class="isMultiSelected({{ $qId }}, '{{ addslashes($opt) }}') ? 'border-cyan-400 bg-cyan-500' : 'border-white/20'">
                                        <svg x-show="isMultiSelected({{ $qId }}, '{{ addslashes($opt) }}')" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <button type="button" @click="toggleMultiChoice({{ $qId }}, '{{ addslashes($opt) }}')" class="text-[13px] text-white/70 text-left flex-1">{{ $opt }}</button>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    {{-- Single Choice --}}
                    @if($question->type === 'single_choice')
                        <div class="space-y-2">
                            @foreach($options as $opt)
                                <label class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg border cursor-pointer transition-colors"
                                       :class="responses[{{ $qId }}] === '{{ addslashes($opt) }}' ? 'bg-cyan-500/10 border-cyan-500/30' : 'bg-white/[0.03] border-white/[0.06] hover:bg-white/[0.05]'">
                                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors"
                                         :class="responses[{{ $qId }}] === '{{ addslashes($opt) }}' ? 'border-cyan-400' : 'border-white/20'">
                                        <div x-show="responses[{{ $qId }}] === '{{ addslashes($opt) }}'" class="w-2 h-2 rounded-full bg-cyan-400"></div>
                                    </div>
                                    <button type="button" @click="responses[{{ $qId }}] = '{{ addslashes($opt) }}'" class="text-[13px] text-white/70 text-left flex-1">{{ $opt }}</button>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    {{-- Yes/No --}}
                    @if($question->type === 'yes_no')
                        <div class="flex items-center gap-3">
                            <button type="button" @click="responses[{{ $qId }}] = 'yes'"
                                    :class="responses[{{ $qId }}] === 'yes' ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400' : 'bg-white/[0.04] border-white/[0.07] text-white/50 hover:bg-white/[0.07]'"
                                    class="flex-1 py-3 rounded-lg border text-[13px] font-semibold transition-colors text-center">
                                Yes
                            </button>
                            <button type="button" @click="responses[{{ $qId }}] = 'no'"
                                    :class="responses[{{ $qId }}] === 'no' ? 'bg-red-500/15 border-red-500/30 text-red-400' : 'bg-white/[0.04] border-white/[0.07] text-white/50 hover:bg-white/[0.07]'"
                                    class="flex-1 py-3 rounded-lg border text-[13px] font-semibold transition-colors text-center">
                                No
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('hr.surveys.show', $survey) }}"
                   class="px-5 py-2.5 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/70 hover:bg-white/[0.06] transition-colors">
                    Cancel
                </a>
                <button @click="submitSurvey()"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                        class="flex items-center gap-2 px-6 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-40 shadow-lg shadow-cyan-500/20">
                    <span x-show="!submitting">Submit Response</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Submitting...
                    </span>
                </button>
            </div>

        </div>
    </template>

</div>

<script>
function surveyRespond() {
    return {
        responses: {},
        submitting: false,
        submitted: false,
        errorMsg: '',

        async submitSurvey() {
            this.submitting = true;
            this.errorMsg = '';

            const responsesArray = Object.entries(this.responses).map(([questionId, answer]) => ({
                question_id: parseInt(questionId),
                answer: answer
            }));

            try {
                const res = await fetch('/api/hr/surveys/{{ $survey->id }}/respond', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']").content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ responses: responsesArray })
                });

                if (!res.ok) {
                    const data = await res.json();
                    throw new Error(data.message || 'Failed to submit survey response');
                }

                this.submitted = true;
            } catch (e) {
                this.errorMsg = e.message;
            } finally {
                this.submitting = false;
            }
        },

        setRating(questionId, rating) {
            this.responses[questionId] = rating;
        },

        toggleMultiChoice(questionId, option) {
            if (!this.responses[questionId]) this.responses[questionId] = [];
            const idx = this.responses[questionId].indexOf(option);
            if (idx > -1) {
                this.responses[questionId].splice(idx, 1);
            } else {
                this.responses[questionId].push(option);
            }
        },

        isMultiSelected(questionId, option) {
            return this.responses[questionId] && this.responses[questionId].includes(option);
        }
    };
}
</script>

</x-layouts.hr>