<x-layouts.docs :title="$document->title . ' — Responses'" :currentView="'forms'">

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('docs.forms.show', $document) }}" class="p-2 rounded-lg hover:bg-white/[0.06] text-white/40 hover:text-white/70">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-white/90">{{ $document->title }}</h1>
                <p class="text-[12px] text-white/40">{{ $responses->total() }} response(s)</p>
            </div>
        </div>
    </div>

    @if($responses->isEmpty())
        <div class="text-center py-20">
            <svg class="w-16 h-16 mx-auto mb-4 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-white/40 text-sm">No responses yet.</p>
            <p class="text-white/25 text-xs mt-1">Share your form to start collecting responses.</p>
        </div>
    @else
        {{-- Summary --}}
        @php
            $questions = ($document->body_json ?? [])['questions'] ?? [];
        @endphp

        @if(!empty($questions))
            <div class="space-y-4">
                @foreach($questions as $question)
                    <div class="bg-[#151520] rounded-2xl border border-white/[0.06] p-6">
                        <h3 class="text-sm font-semibold text-white/80 mb-4">{{ $question['title'] ?? 'Untitled question' }}</h3>
                        @php
                            $qId = $question['id'] ?? '';
                            $allAnswers = $responses->pluck('data')->map(fn($d) => $d[$qId] ?? null)->filter()->values();
                        @endphp

                        @if(in_array($question['type'] ?? '', ['multiple_choice', 'checkboxes', 'dropdown', 'rating']))
                            {{-- Bar chart for choice questions --}}
                            @php $counts = $allAnswers->countBy()->sortByDesc(fn($v) => $v); @endphp
                            <div class="space-y-2">
                                @foreach($counts as $answer => $count)
                                    @php $pct = $responses->total() > 0 ? round($count / $responses->total() * 100) : 0; @endphp
                                    <div class="flex items-center gap-3">
                                        <span class="text-[12px] text-white/60 w-32 truncate shrink-0">{{ $answer }}</span>
                                        <div class="flex-1 h-6 bg-white/[0.04] rounded-full overflow-hidden">
                                            <div class="h-full bg-violet-500/40 rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-[11px] text-white/40 w-12 text-right">{{ $count }} ({{ $pct }}%)</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{-- Text responses --}}
                            <div class="space-y-1.5 max-h-40 overflow-y-auto">
                                @foreach($allAnswers->take(10) as $answer)
                                    <p class="text-[12px] text-white/50 bg-white/[0.03] rounded-lg px-3 py-2">{{ is_array($answer) ? json_encode($answer) : $answer }}</p>
                                @endforeach
                                @if($allAnswers->count() > 10)
                                    <p class="text-[11px] text-white/25">+ {{ $allAnswers->count() - 10 }} more</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Individual responses table --}}
        <div class="mt-8">
            <h3 class="text-[11px] font-semibold text-white/30 uppercase tracking-wider mb-3">Individual Responses</h3>
            <div class="bg-[#151520] rounded-2xl border border-white/[0.06] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-[12px]">
                        <thead>
                            <tr class="border-b border-white/[0.06]">
                                <th class="text-left px-4 py-3 text-white/40 font-medium">#</th>
                                <th class="text-left px-4 py-3 text-white/40 font-medium">Respondent</th>
                                <th class="text-left px-4 py-3 text-white/40 font-medium">Submitted</th>
                                @foreach(array_slice($questions, 0, 5) as $q)
                                    <th class="text-left px-4 py-3 text-white/40 font-medium truncate max-w-[150px]">{{ Str::limit($q['title'] ?? '', 30) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($responses as $i => $response)
                                <tr class="border-b border-white/[0.04] hover:bg-white/[0.02]">
                                    <td class="px-4 py-3 text-white/30">{{ $responses->firstItem() + $i }}</td>
                                    <td class="px-4 py-3 text-white/60">{{ $response->respondent_email ?? $response->respondent_name ?? 'Anonymous' }}</td>
                                    <td class="px-4 py-3 text-white/40">{{ $response->submitted_at->diffForHumans() }}</td>
                                    @foreach(array_slice($questions, 0, 5) as $q)
                                        @php $val = ($response->data ?? [])[$q['id'] ?? ''] ?? '—'; @endphp
                                        <td class="px-4 py-3 text-white/50 truncate max-w-[150px]">{{ is_array($val) ? implode(', ', $val) : $val }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">{{ $responses->links() }}</div>
        </div>
    @endif
</div>

</x-layouts.docs>
