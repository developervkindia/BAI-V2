<x-layouts.hr title="{{ $survey->title }}" currentView="surveys">

<div class="p-5 lg:p-7 space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('hr.surveys.index') }}" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/60 transition-colors mt-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-[22px] font-bold text-white/85 tracking-tight">{{ $survey->title }}</h1>
                <div class="flex items-center gap-3 mt-2 flex-wrap">
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
                    <span class="text-[10px] font-semibold {{ $tc }} px-2 py-0.5 rounded-full uppercase">{{ $survey->type }}</span>
                    <span class="text-[10px] font-semibold {{ $sc }} px-2 py-0.5 rounded-full uppercase">{{ $survey->status }}</span>
                    @if($survey->is_anonymous)
                        <span class="text-[10px] font-medium text-white/30 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            Anonymous
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @if($survey->status === 'active')
                <a href="{{ route('hr.surveys.respond', $survey) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-lg prod-bg text-white text-[13px] font-semibold hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Take Survey
                </a>
            @endif
        </div>
    </div>

    {{-- Survey Info Bar --}}
    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Date Range</p>
                <p class="text-[13px] text-white/65 mt-1">
                    @if($survey->start_date)
                        {{ \Carbon\Carbon::parse($survey->start_date)->format('M d') }} - {{ $survey->end_date ? \Carbon\Carbon::parse($survey->end_date)->format('M d, Y') : 'Ongoing' }}
                    @else
                        Not set
                    @endif
                </p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Responses</p>
                <p class="text-[13px] text-white/65 mt-1">{{ $survey->responses->unique('employee_profile_id')->count() }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Questions</p>
                <p class="text-[13px] text-white/65 mt-1">{{ $survey->questions->count() }}</p>
            </div>
            <div>
                <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">Created</p>
                <p class="text-[13px] text-white/65 mt-1">{{ \Carbon\Carbon::parse($survey->created_at)->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Questions with Results --}}
    <div class="space-y-4">
        <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest">Questions & Results</h2>

        @forelse($survey->questions->sortBy('sort_order') as $question)
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-semibold text-white/20 uppercase">Q{{ $loop->iteration }}</span>
                            @if($question->is_required)
                                <span class="text-[9px] font-semibold text-red-400/60 bg-red-500/10 px-1.5 py-0.5 rounded">Required</span>
                            @endif
                            <span class="text-[9px] font-semibold text-white/20 bg-white/[0.04] px-1.5 py-0.5 rounded capitalize">{{ str_replace('_', ' ', $question->type) }}</span>
                        </div>
                        <h3 class="text-[14px] font-semibold text-white/85">{{ $question->question }}</h3>
                    </div>
                </div>

                @php
                    $qResponses = $survey->responses->where('hr_survey_question_id', $question->id);
                    $responseCount = $qResponses->count();
                @endphp

                @if($responseCount > 0)
                    {{-- Text Responses --}}
                    @if($question->type === 'text')
                        <div class="space-y-2">
                            <p class="text-[11px] text-white/30 font-medium">{{ $responseCount }} {{ Str::plural('response', $responseCount) }}</p>
                            <div class="space-y-2 max-h-48 overflow-y-auto scrollbar-none">
                                @foreach($qResponses->take(5) as $resp)
                                    <div class="px-3.5 py-2.5 rounded-lg bg-white/[0.03] border border-white/[0.04]">
                                        <p class="text-[13px] text-white/60">{{ $resp->answer ?? '(empty)' }}</p>
                                    </div>
                                @endforeach
                                @if($responseCount > 5)
                                    <p class="text-[11px] text-white/25 text-center pt-1">+{{ $responseCount - 5 }} more responses</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Rating Responses --}}
                    @if($question->type === 'rating')
                        @php
                            $ratings = $qResponses->pluck('rating')->filter();
                            $avgRating = $ratings->count() > 0 ? round($ratings->avg(), 1) : 0;
                            $ratingDist = [];
                            for ($i = 5; $i >= 1; $i--) {
                                $count = $ratings->filter(fn($r) => intval($r) === $i)->count();
                                $ratingDist[$i] = $count;
                            }
                            $maxRating = max($ratingDist) ?: 1;
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="text-center py-4">
                                <p class="text-[40px] font-bold text-cyan-400 leading-none">{{ $avgRating }}</p>
                                <div class="flex items-center justify-center gap-0.5 mt-2">
                                    @for($s = 1; $s <= 5; $s++)
                                        <svg class="w-5 h-5 {{ $s <= round($avgRating) ? 'text-amber-400' : 'text-white/10' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                                <p class="text-[11px] text-white/30 mt-1">{{ $ratings->count() }} {{ Str::plural('rating', $ratings->count()) }}</p>
                            </div>
                            <div class="space-y-2">
                                @for($i = 5; $i >= 1; $i--)
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-[11px] text-white/40 w-3 text-right shrink-0">{{ $i }}</span>
                                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        <div class="flex-1 h-[6px] bg-white/[0.06] rounded-full overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-500/80 to-cyan-400/60"
                                                 style="width: {{ round(($ratingDist[$i] / $maxRating) * 100) }}%"></div>
                                        </div>
                                        <span class="text-[11px] text-white/30 w-6 text-right shrink-0">{{ $ratingDist[$i] }}</span>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endif

                    {{-- Multiple Choice / Single Choice --}}
                    @if($question->type === 'multiple_choice' || $question->type === 'single_choice')
                        @php
                            $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                            $optionCounts = [];
                            foreach ($options as $opt) {
                                $optionCounts[$opt] = 0;
                            }
                            foreach ($qResponses as $resp) {
                                $answer = $resp->answer;
                                if (is_string($answer)) {
                                    $decoded = json_decode($answer, true);
                                    if (is_array($decoded)) {
                                        foreach ($decoded as $a) {
                                            if (isset($optionCounts[$a])) $optionCounts[$a]++;
                                        }
                                    } else {
                                        if (isset($optionCounts[$answer])) $optionCounts[$answer]++;
                                    }
                                }
                            }
                            $maxOpt = max($optionCounts) ?: 1;
                            $totalOpt = array_sum($optionCounts) ?: 1;
                        @endphp
                        <div class="space-y-2.5">
                            <p class="text-[11px] text-white/30 font-medium">{{ $responseCount }} {{ Str::plural('response', $responseCount) }}</p>
                            @foreach($optionCounts as $optLabel => $optCount)
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[12px] text-white/60">{{ $optLabel }}</span>
                                        <span class="text-[11px] text-white/35 font-medium">{{ $optCount }} ({{ round(($optCount / $totalOpt) * 100) }}%)</span>
                                    </div>
                                    <div class="h-[6px] bg-white/[0.06] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-cyan-500/80 to-cyan-400/60 transition-all"
                                             style="width: {{ round(($optCount / $maxOpt) * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Yes/No Responses --}}
                    @if($question->type === 'yes_no')
                        @php
                            $yesCount = $qResponses->filter(fn($r) => strtolower($r->answer) === 'yes')->count();
                            $noCount = $qResponses->filter(fn($r) => strtolower($r->answer) === 'no')->count();
                            $totalYN = $yesCount + $noCount;
                            $yesPct = $totalYN > 0 ? round(($yesCount / $totalYN) * 100) : 0;
                            $noPct = $totalYN > 0 ? round(($noCount / $totalYN) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-6">
                            <div class="flex-1 space-y-3">
                                {{-- Yes Bar --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[12px] text-emerald-400/80 font-medium">Yes</span>
                                        <span class="text-[11px] text-white/35">{{ $yesCount }} ({{ $yesPct }}%)</span>
                                    </div>
                                    <div class="h-2 bg-white/[0.06] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-emerald-500/70" style="width: {{ $yesPct }}%"></div>
                                    </div>
                                </div>
                                {{-- No Bar --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[12px] text-red-400/80 font-medium">No</span>
                                        <span class="text-[11px] text-white/35">{{ $noCount }} ({{ $noPct }}%)</span>
                                    </div>
                                    <div class="h-2 bg-white/[0.06] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-red-500/70" style="width: {{ $noPct }}%"></div>
                                    </div>
                                </div>
                            </div>
                            {{-- Simple Pie Visualization --}}
                            <div class="w-20 h-20 shrink-0 relative">
                                <svg viewBox="0 0 36 36" class="w-full h-full -rotate-90">
                                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="currentColor" stroke-width="3" class="text-white/[0.06]"/>
                                    <circle cx="18" cy="18" r="15.5" fill="none" stroke="currentColor" stroke-width="3"
                                            stroke-dasharray="{{ $yesPct }} {{ 100 - $yesPct }}"
                                            class="text-emerald-500/70"/>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-[11px] font-bold text-white/60">{{ $yesPct }}%</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="py-4 text-center">
                        <p class="text-[12px] text-white/20">No responses yet</p>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-12 text-center">
                <p class="text-[14px] text-white/30">No questions in this survey</p>
            </div>
        @endforelse
    </div>

</div>

</x-layouts.hr>