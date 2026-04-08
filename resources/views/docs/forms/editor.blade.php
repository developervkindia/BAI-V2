<x-layouts.docs-editor
    :title="$document->title"
    :document="$document"
    :canEdit="$canEdit"
>

@push('editor-head')
<style>
    /* Toggle switch */
    .toggle-switch {
        position: relative;
        width: 36px;
        height: 20px;
        flex-shrink: 0;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: rgba(255,255,255,0.1);
        border-radius: 999px;
        transition: background 0.2s;
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        width: 14px;
        height: 14px;
        left: 3px;
        top: 3px;
        background: rgba(255,255,255,0.5);
        border-radius: 999px;
        transition: transform 0.2s, background 0.2s;
    }
    .toggle-switch input:checked + .toggle-slider {
        background: #7c3aed;
    }
    .toggle-switch input:checked + .toggle-slider::before {
        transform: translateX(16px);
        background: #fff;
    }

    /* Question card active state */
    .question-card.active {
        border-color: rgba(139, 92, 246, 0.4) !important;
        box-shadow: 0 0 0 1px rgba(139, 92, 246, 0.15);
    }

    /* Type selector dropdown */
    .type-menu {
        max-height: 320px;
        overflow-y: auto;
    }
    .type-menu::-webkit-scrollbar { width: 4px; }
    .type-menu::-webkit-scrollbar-track { background: transparent; }
    .type-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }

    /* Drag handle styling */
    .drag-handle {
        cursor: grab;
        opacity: 0;
        transition: opacity 0.15s;
    }
    .question-card:hover .drag-handle {
        opacity: 1;
    }
    .drag-handle:active {
        cursor: grabbing;
    }

    /* Star rating */
    .star-btn { transition: color 0.1s; }
    .star-btn:hover ~ .star-btn { color: rgba(255,255,255,0.15) !important; }

    /* Smooth placeholder inputs */
    .preview-input {
        border-bottom: 1px solid rgba(255,255,255,0.1);
        background: transparent;
        color: rgba(255,255,255,0.3);
        padding: 6px 0;
        width: 100%;
        outline: none;
        font-size: 13px;
        pointer-events: none;
    }

    /* Add question floating button */
    .add-q-btn {
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .add-q-btn:hover {
        transform: scale(1.08);
        box-shadow: 0 6px 20px rgba(124, 58, 237, 0.35);
    }
</style>
@endpush

<div class="flex flex-col h-[calc(100vh-3rem)]"
     x-data="formBuilder"
     x-on:title-changed.window="titleVal = $event.detail.title; scheduleAutoSave();">

    {{-- ============================================================ --}}
    {{-- TAB BAR + PUBLISH BUTTON                                      --}}
    {{-- ============================================================ --}}
    <div class="shrink-0 bg-[#0D0D16] border-b border-white/[0.06] px-6">
        <div class="max-w-[740px] mx-auto flex items-center justify-between h-11">
            {{-- Tabs --}}
            <div class="flex items-center gap-1">
                <button @click="activeTab = 'questions'"
                        :class="activeTab === 'questions'
                            ? 'text-violet-400 border-violet-400'
                            : 'text-white/40 border-transparent hover:text-white/60'"
                        class="px-3.5 py-2.5 text-[13px] font-medium border-b-2 transition-colors">
                    Questions
                </button>
                <a href="{{ route('docs.forms.responses', $document) }}"
                   class="px-3.5 py-2.5 text-[13px] font-medium text-white/40 border-b-2 border-transparent hover:text-white/60 transition-colors flex items-center gap-1.5">
                    Responses
                    @if($responseCount > 0)
                        <span class="text-[10px] bg-violet-500/20 text-violet-400 px-1.5 py-0.5 rounded-full font-semibold">{{ $responseCount }}</span>
                    @endif
                </a>
                <button @click="activeTab = 'settings'"
                        :class="activeTab === 'settings'
                            ? 'text-violet-400 border-violet-400'
                            : 'text-white/40 border-transparent hover:text-white/60'"
                        class="px-3.5 py-2.5 text-[13px] font-medium border-b-2 transition-colors">
                    Settings
                </button>
            </div>

            {{-- Right: Status + Publish --}}
            <div class="flex items-center gap-3">
                {{-- Status badge --}}
                <span x-show="formStatus === 'draft'" class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-400">
                    Draft
                </span>
                <span x-show="formStatus === 'published'" x-cloak class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400">
                    Published
                </span>

                @if($canEdit)
                <button @click="publish()"
                        :disabled="publishing"
                        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-[12px] font-semibold transition-colors disabled:opacity-50"
                        :class="formStatus === 'published'
                            ? 'bg-amber-500/15 text-amber-400 hover:bg-amber-500/25'
                            : 'bg-violet-500 text-white hover:bg-violet-600'">
                    <svg x-show="!publishing" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <svg x-show="publishing" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="formStatus === 'published' ? 'Unpublish' : 'Publish'"></span>
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MAIN CONTENT AREA                                             --}}
    {{-- ============================================================ --}}
    <div class="flex-1 overflow-auto bg-[#0B0B14]">

        {{-- ======== QUESTIONS TAB ======== --}}
        <div x-show="activeTab === 'questions'" class="py-8">
            <div class="max-w-[640px] mx-auto space-y-4">

                {{-- Public form link --}}
                @if($document->slug)
                <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#151520] border border-white/[0.06] mb-6">
                    <svg class="w-4 h-4 text-white/30 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span class="text-[12px] text-white/30">Public link:</span>
                    <code class="text-[12px] text-violet-400 flex-1 truncate">{{ url('/doc-forms/' . $document->slug) }}</code>
                    <button @click="navigator.clipboard.writeText('{{ url('/doc-forms/' . $document->slug) }}'); $el.textContent = 'Copied!'; setTimeout(() => $el.textContent = 'Copy', 1500)"
                            class="text-[11px] font-medium text-white/40 hover:text-white/70 transition-colors px-2 py-1 rounded hover:bg-white/[0.05]">
                        Copy
                    </button>
                </div>
                @endif

                {{-- Title card --}}
                <div class="bg-[#151520] rounded-2xl border border-white/[0.06] p-6 border-t-4 border-t-violet-500">
                    <input type="text"
                           x-model="titleVal"
                           @input="scheduleAutoSave()"
                           class="w-full bg-transparent border-0 outline-none text-white text-2xl font-semibold placeholder-white/20 pb-2"
                           placeholder="Untitled form"
                           @if(!$canEdit) readonly @endif
                    />
                    <input type="text"
                           x-model="descriptionVal"
                           @input="scheduleAutoSave()"
                           class="w-full bg-transparent border-0 outline-none text-white/40 text-sm placeholder-white/15 mt-1"
                           placeholder="Form description (optional)"
                           @if(!$canEdit) readonly @endif
                    />
                </div>

                {{-- Questions list --}}
                <div x-ref="questionList" class="space-y-3">
                    <template x-for="(question, qIndex) in questions" :key="question.id">
                        <div class="question-card group relative bg-[#151520] rounded-2xl border border-white/[0.06] hover:border-violet-500/20 transition-all"
                             :class="{ 'active': activeQuestion === question.id }"
                             @click="activeQuestion = question.id">

                            <div class="flex">
                                {{-- Drag handle --}}
                                @if($canEdit)
                                <div class="drag-handle flex flex-col items-center justify-center w-8 shrink-0 py-6 text-white/20 hover:text-white/40">
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <circle cx="7" cy="4" r="1.5"/>
                                        <circle cx="13" cy="4" r="1.5"/>
                                        <circle cx="7" cy="10" r="1.5"/>
                                        <circle cx="13" cy="10" r="1.5"/>
                                        <circle cx="7" cy="16" r="1.5"/>
                                        <circle cx="13" cy="16" r="1.5"/>
                                    </svg>
                                </div>
                                @endif

                                {{-- Question content --}}
                                <div class="flex-1 p-6 @if($canEdit) pl-1 @endif">
                                    {{-- Top row: title input + type selector --}}
                                    <div class="flex items-start gap-3 mb-4">
                                        <div class="flex-1">
                                            <input type="text"
                                                   x-model="question.title"
                                                   @input="scheduleAutoSave()"
                                                   class="w-full bg-transparent border-0 border-b border-white/[0.08] outline-none text-white text-[15px] font-medium placeholder-white/20 pb-2 focus:border-violet-500/50 transition-colors"
                                                   placeholder="Question title"
                                                   @if(!$canEdit) readonly @endif
                                            />
                                            <input type="text"
                                                   x-model="question.description"
                                                   @input="scheduleAutoSave()"
                                                   class="w-full bg-transparent border-0 outline-none text-white/30 text-[12px] placeholder-white/15 mt-2"
                                                   placeholder="Description (optional)"
                                                   @if(!$canEdit) readonly @endif
                                            />
                                        </div>

                                        {{-- Type selector --}}
                                        @if($canEdit)
                                        <div class="relative shrink-0" x-data="{ typeOpen: false }" @click.away="typeOpen = false">
                                            <button @click.stop="typeOpen = !typeOpen"
                                                    class="flex items-center gap-2 px-3 py-2 rounded-lg bg-white/[0.05] border border-white/[0.08] hover:border-white/[0.15] text-white/60 text-[12px] transition-colors min-w-[150px]">
                                                <span x-html="getTypeIcon(question.type)" class="w-4 h-4 shrink-0 flex items-center"></span>
                                                <span x-text="getTypeLabel(question.type)" class="flex-1 text-left"></span>
                                                <svg class="w-3.5 h-3.5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>
                                            <div x-show="typeOpen" x-cloak
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 class="type-menu absolute right-0 top-full mt-1 w-56 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl z-50 py-1.5 overflow-hidden">
                                                <template x-for="typeOpt in questionTypes" :key="typeOpt.value">
                                                    <button @click.stop="changeQuestionType(question, typeOpt.value); typeOpen = false"
                                                            class="w-full flex items-center gap-2.5 px-3.5 py-2 text-[12px] hover:bg-white/[0.05] transition-colors"
                                                            :class="question.type === typeOpt.value ? 'text-violet-400' : 'text-white/55'">
                                                        <span x-html="typeOpt.icon" class="w-4 h-4 shrink-0 flex items-center"></span>
                                                        <span x-text="typeOpt.label"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Type-specific content --}}
                                    <div class="mt-4">

                                        {{-- Short text --}}
                                        <template x-if="question.type === 'short_text'">
                                            <div class="preview-input max-w-[60%]">Short answer text</div>
                                        </template>

                                        {{-- Long text --}}
                                        <template x-if="question.type === 'long_text'">
                                            <div class="preview-input w-full">Long answer text</div>
                                        </template>

                                        {{-- Multiple choice --}}
                                        <template x-if="question.type === 'multiple_choice'">
                                            <div class="space-y-2.5">
                                                <template x-for="(opt, oIdx) in question.options" :key="opt.id">
                                                    <div class="flex items-center gap-3 group/opt">
                                                        <div class="w-4 h-4 rounded-full border-2 border-white/20 shrink-0"></div>
                                                        <input type="text"
                                                               x-model="opt.label"
                                                               @input="scheduleAutoSave()"
                                                               class="flex-1 bg-transparent border-0 border-b border-transparent focus:border-white/[0.1] outline-none text-white/60 text-[13px] py-1 transition-colors"
                                                               @if(!$canEdit) readonly @endif
                                                        />
                                                        @if($canEdit)
                                                        <button @click.stop="removeOption(question, oIdx)"
                                                                class="opacity-0 group-hover/opt:opacity-100 p-1 text-white/20 hover:text-red-400 transition-all">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                        @endif
                                                    </div>
                                                </template>
                                                <template x-if="question.other_option">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-4 h-4 rounded-full border-2 border-white/20 shrink-0"></div>
                                                        <span class="text-white/30 text-[13px] italic">Other...</span>
                                                    </div>
                                                </template>
                                                @if($canEdit)
                                                <div class="flex items-center gap-4 mt-2 ml-7">
                                                    <button @click.stop="addOption(question)" class="text-[12px] text-violet-400 hover:text-violet-300 font-medium transition-colors">
                                                        + Add option
                                                    </button>
                                                    <template x-if="!question.other_option">
                                                        <button @click.stop="question.other_option = true; scheduleAutoSave()" class="text-[12px] text-white/30 hover:text-white/50 transition-colors">
                                                            or add "Other"
                                                        </button>
                                                    </template>
                                                </div>
                                                @endif
                                            </div>
                                        </template>

                                        {{-- Checkboxes --}}
                                        <template x-if="question.type === 'checkboxes'">
                                            <div class="space-y-2.5">
                                                <template x-for="(opt, oIdx) in question.options" :key="opt.id">
                                                    <div class="flex items-center gap-3 group/opt">
                                                        <div class="w-4 h-4 rounded border-2 border-white/20 shrink-0"></div>
                                                        <input type="text"
                                                               x-model="opt.label"
                                                               @input="scheduleAutoSave()"
                                                               class="flex-1 bg-transparent border-0 border-b border-transparent focus:border-white/[0.1] outline-none text-white/60 text-[13px] py-1 transition-colors"
                                                               @if(!$canEdit) readonly @endif
                                                        />
                                                        @if($canEdit)
                                                        <button @click.stop="removeOption(question, oIdx)"
                                                                class="opacity-0 group-hover/opt:opacity-100 p-1 text-white/20 hover:text-red-400 transition-all">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                        @endif
                                                    </div>
                                                </template>
                                                <template x-if="question.other_option">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-4 h-4 rounded border-2 border-white/20 shrink-0"></div>
                                                        <span class="text-white/30 text-[13px] italic">Other...</span>
                                                    </div>
                                                </template>
                                                @if($canEdit)
                                                <div class="flex items-center gap-4 mt-2 ml-7">
                                                    <button @click.stop="addOption(question)" class="text-[12px] text-violet-400 hover:text-violet-300 font-medium transition-colors">
                                                        + Add option
                                                    </button>
                                                    <template x-if="!question.other_option">
                                                        <button @click.stop="question.other_option = true; scheduleAutoSave()" class="text-[12px] text-white/30 hover:text-white/50 transition-colors">
                                                            or add "Other"
                                                        </button>
                                                    </template>
                                                </div>
                                                @endif
                                            </div>
                                        </template>

                                        {{-- Dropdown --}}
                                        <template x-if="question.type === 'dropdown'">
                                            <div class="space-y-2">
                                                <template x-for="(opt, oIdx) in question.options" :key="opt.id">
                                                    <div class="flex items-center gap-3 group/opt">
                                                        <span class="text-white/25 text-[12px] w-5 text-right shrink-0" x-text="(oIdx + 1) + '.'"></span>
                                                        <input type="text"
                                                               x-model="opt.label"
                                                               @input="scheduleAutoSave()"
                                                               class="flex-1 bg-transparent border-0 border-b border-transparent focus:border-white/[0.1] outline-none text-white/60 text-[13px] py-1 transition-colors"
                                                               @if(!$canEdit) readonly @endif
                                                        />
                                                        @if($canEdit)
                                                        <button @click.stop="removeOption(question, oIdx)"
                                                                class="opacity-0 group-hover/opt:opacity-100 p-1 text-white/20 hover:text-red-400 transition-all">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                        @endif
                                                    </div>
                                                </template>
                                                @if($canEdit)
                                                <div class="ml-8 mt-2">
                                                    <button @click.stop="addOption(question)" class="text-[12px] text-violet-400 hover:text-violet-300 font-medium transition-colors">
                                                        + Add option
                                                    </button>
                                                </div>
                                                @endif
                                            </div>
                                        </template>

                                        {{-- Date --}}
                                        <template x-if="question.type === 'date'">
                                            <div class="flex items-center gap-2 text-white/25 max-w-[200px]">
                                                <div class="flex-1 border-b border-white/[0.1] pb-1 text-[13px]">Month, Day, Year</div>
                                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        </template>

                                        {{-- Time --}}
                                        <template x-if="question.type === 'time'">
                                            <div class="flex items-center gap-2 text-white/25 max-w-[160px]">
                                                <div class="flex-1 border-b border-white/[0.1] pb-1 text-[13px]">Hour : Minutes</div>
                                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                        </template>

                                        {{-- File upload --}}
                                        <template x-if="question.type === 'file_upload'">
                                            <div class="border-2 border-dashed border-white/[0.08] rounded-xl p-6 flex flex-col items-center gap-2 text-white/20">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                                <span class="text-[12px]">File upload</span>
                                            </div>
                                        </template>

                                        {{-- Rating (stars) --}}
                                        <template x-if="question.type === 'rating'">
                                            <div class="flex items-center gap-1.5 py-1">
                                                <template x-for="star in 5" :key="star">
                                                    <svg class="w-7 h-7 text-white/15" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                                    </svg>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Linear scale --}}
                                        <template x-if="question.type === 'linear_scale'">
                                            <div class="space-y-3">
                                                @if($canEdit)
                                                <div class="flex items-center gap-4">
                                                    <label class="text-[11px] text-white/30 w-8">Min</label>
                                                    <select x-model.number="question.scale.min"
                                                            @change="scheduleAutoSave()"
                                                            class="bg-white/[0.05] border border-white/[0.1] rounded-lg text-white/60 text-[12px] px-2 py-1.5 outline-none focus:border-violet-500/40">
                                                        <option value="0">0</option>
                                                        <option value="1">1</option>
                                                    </select>
                                                    <label class="text-[11px] text-white/30 w-8">Max</label>
                                                    <select x-model.number="question.scale.max"
                                                            @change="scheduleAutoSave()"
                                                            class="bg-white/[0.05] border border-white/[0.1] rounded-lg text-white/60 text-[12px] px-2 py-1.5 outline-none focus:border-violet-500/40">
                                                        <template x-for="n in 10" :key="n">
                                                            <option :value="n + 1" x-text="n + 1" :selected="question.scale.max === n + 1"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                                @endif
                                                <div class="flex items-end gap-3">
                                                    <div class="flex-1">
                                                        <input type="text"
                                                               x-model="question.scale.min_label"
                                                               @input="scheduleAutoSave()"
                                                               class="w-full bg-transparent border-0 border-b border-white/[0.08] outline-none text-white/50 text-[12px] py-1 placeholder-white/15 focus:border-violet-500/40 transition-colors"
                                                               placeholder="Min label (optional)"
                                                               @if(!$canEdit) readonly @endif
                                                        />
                                                    </div>
                                                    <div class="flex items-center gap-2 px-3">
                                                        <template x-for="n in ((question.scale?.max || 5) - (question.scale?.min || 1) + 1)" :key="n">
                                                            <div class="flex flex-col items-center gap-1">
                                                                <span class="text-[11px] text-white/30" x-text="(question.scale?.min || 1) + n - 1"></span>
                                                                <div class="w-4 h-4 rounded-full border-2 border-white/15"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <div class="flex-1">
                                                        <input type="text"
                                                               x-model="question.scale.max_label"
                                                               @input="scheduleAutoSave()"
                                                               class="w-full bg-transparent border-0 border-b border-white/[0.08] outline-none text-white/50 text-[12px] py-1 placeholder-white/15 text-right focus:border-violet-500/40 transition-colors"
                                                               placeholder="Max label (optional)"
                                                               @if(!$canEdit) readonly @endif
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Section header --}}
                                        <template x-if="question.type === 'section_header'">
                                            <div class="border-t border-white/[0.08] mt-2 pt-1">
                                                <span class="text-[11px] text-white/20">Section divider</span>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Bottom bar: required toggle + delete --}}
                                    @if($canEdit)
                                    <div class="flex items-center justify-end gap-4 mt-5 pt-4 border-t border-white/[0.04]"
                                         x-show="question.type !== 'section_header'">
                                        {{-- Duplicate --}}
                                        <button @click.stop="duplicateQuestion(qIndex)"
                                                class="p-1.5 text-white/20 hover:text-white/50 transition-colors rounded-lg hover:bg-white/[0.05]"
                                                title="Duplicate">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete --}}
                                        <button @click.stop="removeQuestion(qIndex)"
                                                class="p-1.5 text-white/20 hover:text-red-400 transition-colors rounded-lg hover:bg-red-500/10"
                                                title="Delete question">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>

                                        {{-- Separator --}}
                                        <div class="w-px h-5 bg-white/[0.06]"></div>

                                        {{-- Required toggle --}}
                                        <div class="flex items-center gap-2">
                                            <span class="text-[12px] text-white/30">Required</span>
                                            <label class="toggle-switch">
                                                <input type="checkbox"
                                                       x-model="question.required"
                                                       @change="scheduleAutoSave()" />
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Section header bottom bar (only delete) --}}
                                    <div class="flex items-center justify-end gap-4 mt-5 pt-4 border-t border-white/[0.04]"
                                         x-show="question.type === 'section_header'">
                                        <button @click.stop="duplicateQuestion(qIndex)"
                                                class="p-1.5 text-white/20 hover:text-white/50 transition-colors rounded-lg hover:bg-white/[0.05]"
                                                title="Duplicate">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                        <button @click.stop="removeQuestion(qIndex)"
                                                class="p-1.5 text-white/20 hover:text-red-400 transition-colors rounded-lg hover:bg-red-500/10"
                                                title="Delete section">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Empty state --}}
                <div x-show="questions.length === 0" class="text-center py-16">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-violet-500/10 flex items-center justify-center">
                        <svg class="w-8 h-8 text-violet-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-white/30 text-sm mb-1">No questions yet</p>
                    <p class="text-white/15 text-xs mb-6">Click the button below to add your first question</p>
                    @if($canEdit)
                    <button @click="addQuestion('short_text')"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-500/15 text-violet-400 text-[13px] font-medium hover:bg-violet-500/25 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add first question
                    </button>
                    @endif
                </div>

                {{-- Add question button --}}
                @if($canEdit)
                <div x-show="questions.length > 0" class="flex justify-center pt-2 pb-8">
                    <div class="relative" x-data="{ addMenuOpen: false }" @click.away="addMenuOpen = false">
                        <button @click="addMenuOpen = !addMenuOpen"
                                class="add-q-btn w-12 h-12 rounded-full bg-violet-500 text-white shadow-lg shadow-violet-500/25 flex items-center justify-center hover:bg-violet-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </button>

                        {{-- Add question type menu --}}
                        <div x-show="addMenuOpen" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 w-56 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl z-50 py-1.5 type-menu">
                            <template x-for="typeOpt in questionTypes" :key="typeOpt.value">
                                <button @click="addQuestion(typeOpt.value); addMenuOpen = false"
                                        class="w-full flex items-center gap-2.5 px-3.5 py-2 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                                    <span x-html="typeOpt.icon" class="w-4 h-4 shrink-0 flex items-center"></span>
                                    <span x-text="typeOpt.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- ======== SETTINGS TAB ======== --}}
        <div x-show="activeTab === 'settings'" x-cloak class="py-8">
            <div class="max-w-[640px] mx-auto space-y-4">

                {{-- General settings card --}}
                <div class="bg-[#151520] rounded-2xl border border-white/[0.06] p-6">
                    <h3 class="text-white/80 text-[15px] font-semibold mb-6">Form Settings</h3>

                    <div class="space-y-6">
                        {{-- Collect email --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/70 text-[13px] font-medium">Collect email addresses</p>
                                <p class="text-white/25 text-[11px] mt-0.5">Require respondents to provide their email</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" x-model="settings.collect_email" @change="scheduleAutoSave()" @if(!$canEdit) disabled @endif />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        {{-- Allow edit after submit --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/70 text-[13px] font-medium">Allow edit after submit</p>
                                <p class="text-white/25 text-[11px] mt-0.5">Respondents can modify their responses after submitting</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" x-model="settings.allow_edit_after_submit" @change="scheduleAutoSave()" @if(!$canEdit) disabled @endif />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        {{-- Shuffle questions --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-white/70 text-[13px] font-medium">Shuffle question order</p>
                                <p class="text-white/25 text-[11px] mt-0.5">Randomize question order for each respondent</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" x-model="settings.shuffle_questions" @change="scheduleAutoSave()" @if(!$canEdit) disabled @endif />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Response limits card --}}
                <div class="bg-[#151520] rounded-2xl border border-white/[0.06] p-6">
                    <h3 class="text-white/80 text-[15px] font-semibold mb-6">Response Limits</h3>

                    <div class="space-y-6">
                        {{-- Limit responses --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-white/70 text-[13px] font-medium">Limit number of responses</p>
                                    <p class="text-white/25 text-[11px] mt-0.5">Stop accepting responses after reaching the limit</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                           :checked="settings.limit_responses !== null && settings.limit_responses !== ''"
                                           @change="settings.limit_responses = $el.checked ? 100 : null; scheduleAutoSave()"
                                           @if(!$canEdit) disabled @endif />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            <div x-show="settings.limit_responses !== null && settings.limit_responses !== ''" class="ml-0 mt-3">
                                <div class="flex items-center gap-3">
                                    <label class="text-[12px] text-white/30">Max responses:</label>
                                    <input type="number"
                                           x-model.number="settings.limit_responses"
                                           @input="scheduleAutoSave()"
                                           min="1"
                                           class="w-24 bg-white/[0.05] border border-white/[0.1] rounded-lg text-white/70 text-[13px] px-3 py-1.5 outline-none focus:border-violet-500/40 transition-colors"
                                           @if(!$canEdit) readonly @endif
                                    />
                                    @if($responseCount > 0)
                                    <span class="text-[11px] text-white/25">({{ $responseCount }} collected so far)</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Confirmation message card --}}
                <div class="bg-[#151520] rounded-2xl border border-white/[0.06] p-6">
                    <h3 class="text-white/80 text-[15px] font-semibold mb-4">Confirmation Message</h3>
                    <p class="text-white/25 text-[11px] mb-3">Shown to respondents after they submit the form</p>
                    <textarea x-model="settings.confirmation_message"
                              @input="scheduleAutoSave()"
                              rows="3"
                              class="w-full bg-white/[0.04] border border-white/[0.08] rounded-xl text-white/60 text-[13px] px-4 py-3 outline-none focus:border-violet-500/40 transition-colors resize-none placeholder-white/15"
                              placeholder="Your response has been recorded."
                              @if(!$canEdit) readonly @endif
                    ></textarea>
                </div>

            </div>
        </div>

    </div>

    {{-- View only badge --}}
    @if(!$canEdit)
    <div class="fixed bottom-6 right-6 z-40">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium bg-amber-500/15 text-amber-400 border border-amber-500/20 shadow-lg">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            View only
        </span>
    </div>
    @endif

</div>

@push('editor-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('formBuilder', () => ({
        activeTab: 'questions',
        activeQuestion: null,
        titleVal: @js($document->title ?? 'Untitled form'),
        descriptionVal: @js(($document->body_json ?? [])['description'] ?? ''),
        questions: @js(($document->body_json ?? [])['questions'] ?? []),
        settings: Object.assign({
            collect_email: false,
            limit_responses: null,
            shuffle_questions: false,
            confirmation_message: 'Your response has been recorded.',
            allow_edit_after_submit: false,
        }, @js(($document->body_json ?? [])['settings'] ?? [])),
        formStatus: @js($document->status ?? 'draft'),
        saveTimer: null,
        saving: false,
        publishing: false,
        currentVersion: {{ $document->version ?? 1 }},

        questionTypes: [
            { value: 'short_text', label: 'Short answer', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10"/></svg>' },
            { value: 'long_text', label: 'Paragraph', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h12M4 18h8"/></svg>' },
            { value: 'multiple_choice', label: 'Multiple choice', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="7" r="2.5" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M11 7h9M11 17h9"/><circle cx="5" cy="17" r="2.5" stroke-width="2"/></svg>' },
            { value: 'checkboxes', label: 'Checkboxes', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7l1.5 1.5L9 5"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M14 7h7M14 17h7"/></svg>' },
            { value: 'dropdown', label: 'Dropdown', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10l4 4 4-4"/></svg>' },
            { value: 'date', label: 'Date', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' },
            { value: 'time', label: 'Time', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
            { value: 'file_upload', label: 'File upload', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>' },
            { value: 'rating', label: 'Rating', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>' },
            { value: 'linear_scale', label: 'Linear scale', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M3 12h18"/><circle cx="5" cy="12" r="2" fill="currentColor"/><circle cx="12" cy="12" r="2" fill="currentColor"/><circle cx="19" cy="12" r="2" fill="currentColor"/></svg>' },
            { value: 'section_header', label: 'Section header', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16"/></svg>' },
        ],

        init() {
            this.$nextTick(() => {
                this.initSortable();
            });
        },

        initSortable() {
            if (typeof Sortable !== 'undefined' && this.$refs.questionList) {
                new Sortable(this.$refs.questionList, {
                    handle: '.drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-30',
                    onEnd: (evt) => {
                        const item = this.questions.splice(evt.oldIndex, 1)[0];
                        this.questions.splice(evt.newIndex, 0, item);
                        this.scheduleAutoSave();
                    },
                });
            }
        },

        getTypeLabel(type) {
            const found = this.questionTypes.find(t => t.value === type);
            return found ? found.label : type;
        },

        getTypeIcon(type) {
            const found = this.questionTypes.find(t => t.value === type);
            return found ? found.icon : '';
        },

        addQuestion(type = 'short_text') {
            const needsOptions = ['multiple_choice', 'checkboxes', 'dropdown'].includes(type);
            const q = {
                id: 'q_' + Date.now() + Math.random().toString(36).substr(2, 5),
                type: type,
                title: '',
                description: '',
                required: false,
                options: needsOptions
                    ? [{ id: 'o_' + Date.now(), label: 'Option 1' }]
                    : [],
                other_option: false,
                validation: {},
                scale: type === 'linear_scale' ? { min: 1, max: 5, min_label: '', max_label: '' } : null,
            };
            this.questions.push(q);
            this.activeQuestion = q.id;
            this.scheduleAutoSave();

            // Scroll to the new question
            this.$nextTick(() => {
                const container = this.$el.querySelector('.overflow-auto');
                if (container) {
                    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
                }
            });
        },

        removeQuestion(index) {
            if (this.questions.length > 0) {
                this.questions.splice(index, 1);
                this.scheduleAutoSave();
            }
        },

        duplicateQuestion(index) {
            const original = this.questions[index];
            const duplicate = JSON.parse(JSON.stringify(original));
            duplicate.id = 'q_' + Date.now() + Math.random().toString(36).substr(2, 5);
            // Give new IDs to options
            if (duplicate.options) {
                duplicate.options = duplicate.options.map(opt => ({
                    ...opt,
                    id: 'o_' + Date.now() + Math.random().toString(36).substr(2, 3),
                }));
            }
            this.questions.splice(index + 1, 0, duplicate);
            this.activeQuestion = duplicate.id;
            this.scheduleAutoSave();
        },

        changeQuestionType(question, newType) {
            const oldType = question.type;
            question.type = newType;

            const needsOptions = ['multiple_choice', 'checkboxes', 'dropdown'].includes(newType);
            const hadOptions = ['multiple_choice', 'checkboxes', 'dropdown'].includes(oldType);

            if (needsOptions && !hadOptions) {
                question.options = [{ id: 'o_' + Date.now(), label: 'Option 1' }];
            } else if (!needsOptions) {
                question.options = [];
                question.other_option = false;
            }

            if (newType === 'linear_scale' && !question.scale) {
                question.scale = { min: 1, max: 5, min_label: '', max_label: '' };
            }

            this.scheduleAutoSave();
        },

        addOption(question) {
            question.options.push({
                id: 'o_' + Date.now() + Math.random().toString(36).substr(2, 3),
                label: 'Option ' + (question.options.length + 1),
            });
            this.scheduleAutoSave();
        },

        removeOption(question, optIndex) {
            question.options.splice(optIndex, 1);
            this.scheduleAutoSave();
        },

        scheduleAutoSave() {
            clearTimeout(this.saveTimer);
            this.saveTimer = setTimeout(() => this.autoSave(), 1500);
            this.updateSaveStatus('unsaved');
        },

        updateSaveStatus(status) {
            const el = document.getElementById('save-status');
            if (!el) return;
            switch (status) {
                case 'saving':
                    el.textContent = 'Saving...';
                    el.className = 'text-yellow-400 text-xs';
                    break;
                case 'saved':
                    el.textContent = 'All changes saved';
                    el.className = 'text-emerald-400 text-xs';
                    break;
                case 'unsaved':
                    el.textContent = 'Unsaved changes';
                    el.className = 'text-white/40 text-xs';
                    break;
                case 'error':
                    el.textContent = 'Save failed — retrying...';
                    el.className = 'text-red-400 text-xs';
                    break;
            }
        },

        async autoSave() {
            if (this.saving) return;
            this.saving = true;
            this.updateSaveStatus('saving');

            try {
                const res = await fetch(`/api/docs/documents/{{ $document->id }}/auto-save`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title: this.titleVal,
                        body_json: {
                            description: this.descriptionVal,
                            questions: this.questions,
                            settings: this.settings,
                        },
                        version: this.currentVersion,
                    }),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    this.currentVersion = data.version;
                    this.updateSaveStatus('saved');
                } else if (res.status === 409) {
                    this.updateSaveStatus('error');
                    alert('This form was modified by someone else. Please reload to get the latest version.');
                } else {
                    this.updateSaveStatus('error');
                    setTimeout(() => this.autoSave(), 5000);
                }
            } catch (e) {
                console.error('Auto-save failed:', e);
                this.updateSaveStatus('error');
                setTimeout(() => this.autoSave(), 5000);
            } finally {
                this.saving = false;
            }
        },

        async publish() {
            if (this.publishing) return;
            this.publishing = true;

            const newStatus = this.formStatus === 'published' ? 'draft' : 'published';

            try {
                // First save any pending changes
                clearTimeout(this.saveTimer);
                await this.autoSave();

                const res = await fetch(`/api/docs/documents/{{ $document->id }}/auto-save`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title: this.titleVal,
                        body_json: {
                            description: this.descriptionVal,
                            questions: this.questions,
                            settings: this.settings,
                        },
                        settings: { status: newStatus },
                        version: this.currentVersion,
                    }),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    this.formStatus = newStatus;
                    this.currentVersion = data.version;
                    this.updateSaveStatus('saved');
                } else {
                    alert('Failed to update form status. Please try again.');
                }
            } catch (e) {
                console.error('Publish failed:', e);
                alert('Failed to update form status. Please try again.');
            } finally {
                this.publishing = false;
            }
        },
    }));
});

// Save before leaving
window.addEventListener('beforeunload', (e) => {
    const status = document.getElementById('save-status')?.textContent;
    if (status === 'Unsaved changes') {
        e.preventDefault();
    }
});
</script>
@endpush

</x-layouts.docs-editor>
