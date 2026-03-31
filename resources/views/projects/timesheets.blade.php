<x-layouts.smartprojects :project="$project" currentView="timesheets" :canEdit="$canEdit">

@php
    $weekDays = [];
    $d = \Illuminate\Support\Carbon::parse($weekStart);
    while ($d->lte(\Illuminate\Support\Carbon::parse($weekEnd))) {
        $weekDays[] = $d->copy();
        $d->addDay();
    }
    $weekTotal = round(array_sum($dailyTotals), 2);
    $isManager = $project->isManager(auth()->user());
@endphp

<div class="px-6 py-5 max-w-screen-xl mx-auto" x-data="timesheetManager()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('projects.timesheets', [$project, 'week_start' => \Illuminate\Support\Carbon::parse($weekStart)->subWeek()->toDateString(), 'user_id' => $userId]) }}"
               class="p-2 rounded-lg hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-[15px] font-semibold text-white/85">
                    {{ \Illuminate\Support\Carbon::parse($weekStart)->format('M j') }} — {{ \Illuminate\Support\Carbon::parse($weekEnd)->format('M j, Y') }}
                </h2>
                <p class="text-[11px] text-white/35">Timesheet</p>
            </div>
            <a href="{{ route('projects.timesheets', [$project, 'week_start' => \Illuminate\Support\Carbon::parse($weekStart)->addWeek()->toDateString(), 'user_id' => $userId]) }}"
               class="p-2 rounded-lg hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="flex items-center gap-3">
            @if($isManager)
                <select onchange="window.location.href='{{ route('projects.timesheets', $project) }}?week_start={{ $weekStart }}&user_id=' + this.value"
                    class="px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-[12px] text-white/60 focus:outline-none">
                    @foreach($project->members as $m)
                        <option value="{{ $m->id }}" {{ (int)$userId === $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            @endif

            @if($submission)
                <span class="px-3 py-1 rounded-full text-[11px] font-medium
                    {{ $submission->status === 'approved' ? 'bg-green-500/15 text-green-400' :
                       ($submission->status === 'rejected' ? 'bg-red-500/15 text-red-400' :
                       ($submission->status === 'submitted' ? 'bg-blue-500/15 text-blue-400' : 'bg-white/[0.06] text-white/40')) }}">
                    {{ ucfirst($submission->status) }}
                </span>
            @endif

            @if(!$submission || $submission->status !== 'approved')
                <button @click="submitTimesheet()"
                    class="px-4 py-1.5 rounded-lg text-[12px] font-semibold bg-orange-500 text-white hover:bg-orange-400 transition-colors">
                    Submit for Approval
                </button>
            @endif
        </div>
    </div>

    @if($submission && $submission->status === 'rejected' && $submission->rejection_reason)
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-[12px] text-red-400">
            <strong>Rejected:</strong> {{ $submission->rejection_reason }}
        </div>
    @endif

    {{-- Timesheet Grid --}}
    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/[0.06]">
                        <th class="text-left py-3 px-4 text-[11px] text-white/35 font-medium w-48 sticky left-0 bg-[#111120] z-10">Task</th>
                        @foreach($weekDays as $day)
                            <th class="text-center py-3 px-3 text-[11px] font-medium min-w-[80px]
                                {{ $day->isToday() ? 'text-orange-400' : 'text-white/35' }}">
                                {{ $day->format('D') }}<br>
                                <span class="text-[10px] {{ $day->isToday() ? 'text-orange-400/70' : 'text-white/20' }}">{{ $day->format('M j') }}</span>
                            </th>
                        @endforeach
                        <th class="text-center py-3 px-3 text-[11px] text-white/45 font-medium min-w-[70px]">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $taskRow)
                        <tr class="border-b border-white/[0.04] hover:bg-white/[0.02]">
                            <td class="py-2.5 px-4 text-[12px] text-white/65 truncate max-w-[200px] sticky left-0 bg-[#111120] z-10">
                                {{ $taskRow['title'] }}
                            </td>
                            @foreach($weekDays as $day)
                                <td class="text-center py-2 px-1.5">
                                    <span class="text-[12px] {{ ($taskRow['days'][$day->format('Y-m-d')] ?? 0) > 0 ? 'text-white/70' : 'text-white/15' }}">
                                        {{ ($taskRow['days'][$day->format('Y-m-d')] ?? 0) > 0 ? number_format($taskRow['days'][$day->format('Y-m-d')], 1) : '—' }}
                                    </span>
                                </td>
                            @endforeach
                            <td class="text-center py-2 px-3 text-[12px] text-white/55 font-medium">
                                {{ $taskRow['total'] }}h
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($weekDays) + 2 }}" class="py-8 text-center text-[12px] text-white/25">
                                No time logged this week
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-white/[0.08] bg-white/[0.02]">
                        <td class="py-3 px-4 text-[11px] text-white/45 font-semibold uppercase sticky left-0 bg-[#12122A] z-10">Daily Total</td>
                        @foreach($weekDays as $day)
                            <td class="text-center py-3 px-3 text-[12px] font-medium
                                {{ ($dailyTotals[$day->format('Y-m-d')] ?? 0) > 8 ? 'text-amber-400' : 'text-white/50' }}">
                                {{ ($dailyTotals[$day->format('Y-m-d')] ?? 0) > 0 ? number_format($dailyTotals[$day->format('Y-m-d')], 1) : '—' }}
                            </td>
                        @endforeach
                        <td class="text-center py-3 px-3 text-[13px] text-orange-400 font-bold">
                            {{ $weekTotal }}h
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Manager: Review Submissions --}}
    @if($isManager)
    <div class="mt-6" x-data="{ showReview: false }">
        <button @click="showReview = !showReview"
            class="flex items-center gap-2 text-[13px] font-medium text-white/45 hover:text-white/70 transition-colors mb-3">
            <svg class="w-4 h-4 transition-transform" :class="showReview && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            Review Submissions
        </button>
        <div x-show="showReview" x-cloak class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4 space-y-2"
             x-data="reviewPanel({{ Js::from(['projectSlug' => $project->slug]) }})">
            <template x-if="submissions.length === 0">
                <p class="text-[12px] text-white/25 py-4 text-center">No pending submissions</p>
            </template>
            <template x-for="sub in submissions" :key="sub.id">
                <div class="flex items-center justify-between px-4 py-3 bg-white/[0.03] rounded-xl">
                    <div>
                        <span class="text-[13px] text-white/70 font-medium" x-text="sub.user?.name"></span>
                        <span class="text-[11px] text-white/30 ml-2" x-text="sub.week_start + ' — ' + sub.week_end"></span>
                        <span class="text-[11px] text-white/40 ml-2" x-text="sub.total_hours + 'h'"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="approveSubmission(sub.id)" class="px-3 py-1 rounded-lg text-[11px] bg-green-500/15 text-green-400 hover:bg-green-500/25 transition-colors">Approve</button>
                        <button @click="rejectPrompt = sub.id" class="px-3 py-1 rounded-lg text-[11px] bg-red-500/15 text-red-400 hover:bg-red-500/25 transition-colors">Reject</button>
                    </div>
                </div>
            </template>
        </div>
    </div>
    @endif
</div>

<script>
function timesheetManager() {
    return {
        async submitTimesheet() {
            const res = await fetch(`/api/projects/{{ $project->slug }}/timesheets/submit`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ week_start: '{{ $weekStart }}', week_end: '{{ $weekEnd }}' })
            });
            if (res.ok) location.reload();
        }
    };
}

function reviewPanel(config) {
    return {
        submissions: [],
        rejectPrompt: null,
        async init() {
            const res = await fetch(`/api/projects/${config.projectSlug}/timesheets/submissions?status=submitted`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            if (res.ok) {
                const data = await res.json();
                this.submissions = data.submissions || [];
            }
        },
        async approveSubmission(id) {
            const res = await fetch(`/api/timesheet-submissions/${id}/approve`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            if (res.ok) this.submissions = this.submissions.filter(s => s.id !== id);
        },
        async rejectSubmission(id, reason) {
            const res = await fetch(`/api/timesheet-submissions/${id}/reject`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ rejection_reason: reason })
            });
            if (res.ok) this.submissions = this.submissions.filter(s => s.id !== id);
        }
    };
}
</script>

</x-layouts.smartprojects>
