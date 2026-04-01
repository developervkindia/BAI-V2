<x-layouts.hr title="Create Survey" currentView="surveys">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    title: '',
    description: '',
    type: 'engagement',
    is_anonymous: false,
    start_date: '',
    end_date: '',
    questions: [],
    submitting: false,
    errorMsg: '',

    addQuestion() {
        this.questions.push({
            id: Date.now(),
            question: '',
            type: 'text',
            is_required: true,
            options: ['']
        });
    },

    removeQuestion(index) {
        this.questions.splice(index, 1);
    },

    moveUp(index) {
        if (index === 0) return;
        const temp = this.questions[index];
        this.questions[index] = this.questions[index - 1];
        this.questions[index - 1] = temp;
    },

    moveDown(index) {
        if (index >= this.questions.length - 1) return;
        const temp = this.questions[index];
        this.questions[index] = this.questions[index + 1];
        this.questions[index + 1] = temp;
    },

    addOption(qIndex) {
        this.questions[qIndex].options.push('');
    },

    removeOption(qIndex, oIndex) {
        this.questions[qIndex].options.splice(oIndex, 1);
    },

    async submitSurvey() {
        this.submitting = true;
        this.errorMsg = '';

        const payload = {
            title: this.title,
            description: this.description,
            type: this.type,
            is_anonymous: this.is_anonymous,
            start_date: this.start_date || null,
            end_date: this.end_date || null,
            questions: this.questions.map((q, i) => ({
                question: q.question,
                type: q.type,
                is_required: q.is_required,
                sort_order: i,
                options: (q.type === 'multiple_choice' || q.type === 'single_choice') ? q.options.filter(o => o.trim()) : null
            }))
        };

        try {
            const res = await fetch('/api/hr/surveys', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=&quot;csrf-token&quot;]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.message || 'Failed to create survey');
            }

            if (data.id) {
                window.location.href = '/hr/surveys/' + data.id;
            } else if (data.survey && data.survey.id) {
                window.location.href = '/hr/surveys/' + data.survey.id;
            } else {
                window.location.href = '{{ route('hr.surveys.index') }}';
            }
        } catch (e) {
            this.errorMsg = e.message;
        } finally {
            this.submitting = false;
        }
    }
}">

    {{-- Page Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('hr.surveys.index') }}" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/60 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Create Survey</h1>
            <p class="text-[13px] text-white/40 mt-0.5">Build a new survey for your employees</p>
        </div>
    </div>

    {{-- Error Message --}}
    <template x-if="errorMsg">
        <div class="flex items-center gap-2 p-3 rounded-lg bg-red-500/10 border border-red-500/20">
            <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-[13px] text-red-400" x-text="errorMsg"></span>
        </div>
    </template>

    {{-- Survey Details Card --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-6 space-y-5">
        <h2 class="text-[14px] font-semibold text-white/85 flex items-center gap-2">
            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Survey Details
        </h2>

        {{-- Title --}}
        <div>
            <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Title <span class="text-red-400">*</span></label>
            <input type="text" x-model="title" placeholder="Enter survey title"
                   class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08]"/>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Description</label>
            <textarea x-model="description" rows="3" placeholder="Describe the purpose of this survey..."
                      class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] resize-none"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Type --}}
            <div>
                <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Type</label>
                <select x-model="type"
                        class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none cursor-pointer">
                    <option value="engagement">Engagement</option>
                    <option value="pulse">Pulse</option>
                    <option value="feedback">Feedback</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            {{-- Anonymous Toggle --}}
            <div>
                <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Anonymous</label>
                <button @click="is_anonymous = !is_anonymous" type="button"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] w-full">
                    <div class="relative w-9 h-5 rounded-full transition-colors"
                         :class="is_anonymous ? 'bg-cyan-500' : 'bg-white/[0.12]'">
                        <div class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                             :class="is_anonymous ? 'translate-x-4' : 'translate-x-0.5'"></div>
                    </div>
                    <span class="text-[13px] text-white/60" x-text="is_anonymous ? 'Yes' : 'No'"></span>
                </button>
            </div>

            {{-- Start Date --}}
            <div>
                <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Start Date</label>
                <input type="date" x-model="start_date"
                       class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40"/>
            </div>

            {{-- End Date --}}
            <div>
                <label class="block text-[12px] font-semibold text-white/50 mb-1.5">End Date</label>
                <input type="date" x-model="end_date"
                       class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40"/>
            </div>
        </div>
    </div>

    {{-- Questions Section --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-6 space-y-5">
        <div class="flex items-center justify-between">
            <h2 class="text-[14px] font-semibold text-white/85 flex items-center gap-2">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Questions
                <span class="text-[11px] text-white/30 font-normal" x-text="'(' + questions.length + ')'"></span>
            </h2>
            <button @click="addQuestion()" type="button"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-[12px] font-semibold hover:bg-cyan-500/20 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Question
            </button>
        </div>

        {{-- Empty State --}}
        <template x-if="questions.length === 0">
            <div class="py-10 text-center border-2 border-dashed border-white/[0.06] rounded-xl">
                <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-[13px] text-white/30">No questions added yet</p>
                <p class="text-[11px] text-white/20 mt-1">Click "Add Question" to get started</p>
            </div>
        </template>

        {{-- Questions List --}}
        <template x-for="(q, index) in questions" :key="q.id">
            <div class="border border-white/[0.06] rounded-xl p-5 space-y-4 bg-white/[0.02]">
                <div class="flex items-center justify-between">
                    <span class="text-[12px] font-semibold text-white/30 uppercase tracking-widest" x-text="'Question ' + (index + 1)"></span>
                    <div class="flex items-center gap-1">
                        <button @click="moveUp(index)" :disabled="index === 0" type="button"
                                class="p-1 rounded hover:bg-white/[0.06] text-white/25 hover:text-white/50 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button @click="moveDown(index)" :disabled="index >= questions.length - 1" type="button"
                                class="p-1 rounded hover:bg-white/[0.06] text-white/25 hover:text-white/50 transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <button @click="removeQuestion(index)" type="button"
                                class="p-1 rounded hover:bg-red-500/10 text-white/25 hover:text-red-400 transition-colors ml-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Question Text --}}
                <div>
                    <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Question Text <span class="text-red-400">*</span></label>
                    <input type="text" x-model="q.question" placeholder="Enter your question..."
                           class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08]"/>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Question Type --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Answer Type</label>
                        <select x-model="q.type"
                                class="w-full px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 appearance-none cursor-pointer">
                            <option value="text">Text</option>
                            <option value="rating">Rating (1-5)</option>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="single_choice">Single Choice</option>
                            <option value="yes_no">Yes / No</option>
                        </select>
                    </div>

                    {{-- Required Toggle --}}
                    <div>
                        <label class="block text-[12px] font-semibold text-white/50 mb-1.5">Required</label>
                        <button @click="q.is_required = !q.is_required" type="button"
                                class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg bg-white/[0.06] border border-white/[0.08] w-full">
                            <div class="relative w-9 h-5 rounded-full transition-colors"
                                 :class="q.is_required ? 'bg-cyan-500' : 'bg-white/[0.12]'">
                                <div class="absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform"
                                     :class="q.is_required ? 'translate-x-4' : 'translate-x-0.5'"></div>
                            </div>
                            <span class="text-[13px] text-white/60" x-text="q.is_required ? 'Required' : 'Optional'"></span>
                        </button>
                    </div>
                </div>

                {{-- Options (for multiple_choice / single_choice) --}}
                <template x-if="q.type === 'multiple_choice' || q.type === 'single_choice'">
                    <div class="space-y-2.5">
                        <label class="block text-[12px] font-semibold text-white/50">Options</label>
                        <template x-for="(opt, oIndex) in q.options" :key="oIndex">
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-white/20 w-5 text-center shrink-0" x-text="oIndex + 1"></span>
                                <input type="text" x-model="q.options[oIndex]" :placeholder="'Option ' + (oIndex + 1)"
                                       class="flex-1 px-3 py-2 rounded-lg bg-white/[0.04] border border-white/[0.06] text-[13px] text-white/70 placeholder-white/20 focus:outline-none focus:ring-1 focus:ring-cyan-500/40"/>
                                <button @click="removeOption(index, oIndex)" type="button"
                                        :disabled="q.options.length <= 1"
                                        class="p-1.5 rounded hover:bg-red-500/10 text-white/20 hover:text-red-400 transition-colors disabled:opacity-30">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <button @click="addOption(index)" type="button"
                                class="flex items-center gap-1.5 text-[12px] text-cyan-400/70 hover:text-cyan-400 font-medium transition-colors mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Option
                        </button>
                    </div>
                </template>
            </div>
        </template>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('hr.surveys.index') }}"
           class="px-5 py-2.5 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/70 hover:bg-white/[0.06] transition-colors">
            Cancel
        </a>
        <button @click="submitSurvey()"
                :disabled="submitting || !title"
                :class="submitting ? 'opacity-50 cursor-not-allowed' : ''"
                class="flex items-center gap-2 px-6 py-2.5 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-40 shadow-lg shadow-cyan-500/20">
            <span x-show="!submitting">Create Survey</span>
            <span x-show="submitting" class="flex items-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Creating...
            </span>
        </button>
    </div>

</div>

</x-layouts.hr>