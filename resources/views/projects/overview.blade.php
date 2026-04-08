<x-layouts.smartprojects :project="$project" currentView="overview" :canEdit="$canEdit">

@php
    $totalTasks      = $taskStats->sum('cnt');
    $completedCount  = $taskStats->filter(fn($s) => ($s->status ?? '') === 'completed')->sum('cnt');
    $inProgressCount = $taskStats->filter(fn($s) => ($s->status ?? '') === 'in_progress')->sum('cnt');
    $overdueCount    = $overdue->count();
    $overallPct      = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100) : 0;

    // SVG ring (r=40, cx/cy=48)
    $radius       = 40;
    $circumference = round(2 * M_PI * $radius, 2);
    $dashOffset   = round($circumference - ($overallPct / 100) * $circumference, 2);
    $projectColor = $project->color ?? '#F97316';

    $roleLabels = ['owner' => 'Owner', 'manager' => 'Manager', 'member' => 'Member', 'viewer' => 'Viewer'];
    $roleColors = [
        'owner'   => 'bg-orange-500/15 text-orange-400',
        'manager' => 'bg-sky-500/15 text-sky-400',
        'member'  => 'bg-white/[0.06] text-white/40',
        'viewer'  => 'bg-white/[0.04] text-white/28',
    ];
@endphp

<div class="px-6 py-5 max-w-screen-xl mx-auto space-y-5">

    {{-- ================================================================ --}}
    {{-- STAT CARDS                                                        --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- Total Tasks --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="text-[11px] font-medium text-white/35 mb-1.5">Total Tasks</div>
            <div class="text-[28px] font-bold text-white/82 leading-none">{{ $totalTasks }}</div>
            <div class="text-[11px] text-white/25 mt-1">across all sections</div>
        </div>

        {{-- Completed --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="flex items-center justify-between mb-1.5">
                <div class="text-[11px] font-medium text-white/35">Completed</div>
                <div class="w-6 h-6 rounded-lg bg-green-500/15 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <div class="text-[28px] font-bold text-green-400 leading-none">{{ $completedCount }}</div>
            <div class="text-[11px] text-white/25 mt-1">{{ $totalTasks > 0 ? round($completedCount / $totalTasks * 100) : 0 }}% of total</div>
        </div>

        {{-- In Progress --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="flex items-center justify-between mb-1.5">
                <div class="text-[11px] font-medium text-white/35">In Progress</div>
                <div class="w-6 h-6 rounded-lg bg-orange-500/15 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="text-[28px] font-bold text-orange-400 leading-none">{{ $inProgressCount }}</div>
            <div class="text-[11px] text-white/25 mt-1">tasks active now</div>
        </div>

        {{-- Overdue --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4">
            <div class="flex items-center justify-between mb-1.5">
                <div class="text-[11px] font-medium text-white/35">Overdue</div>
                <div class="w-6 h-6 rounded-lg {{ $overdueCount > 0 ? 'bg-red-500/15' : 'bg-white/[0.05]' }} flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 {{ $overdueCount > 0 ? 'text-red-400' : 'text-white/25' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-[28px] font-bold {{ $overdueCount > 0 ? 'text-red-400' : 'text-white/82' }} leading-none">{{ $overdueCount }}</div>
            <div class="text-[11px] text-white/25 mt-1">{{ $overdueCount > 0 ? 'need attention' : 'all on track' }}</div>
        </div>

    </div>

    {{-- ================================================================ --}}
    {{-- TWO-COLUMN LAYOUT                                                 --}}
    {{-- ================================================================ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── LEFT PANEL (1/3) ─────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Progress ring --}}
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <div class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-4">Overall Progress</div>
                <div class="flex items-center gap-5">
                    <svg width="96" height="96" viewBox="0 0 96 96" class="shrink-0 -rotate-90">
                        <circle cx="48" cy="48" r="{{ $radius }}" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="9"/>
                        <circle
                            cx="48" cy="48" r="{{ $radius }}"
                            fill="none"
                            stroke="{{ $projectColor }}"
                            stroke-width="9"
                            stroke-linecap="round"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $dashOffset }}"
                            style="transition: stroke-dashoffset 0.6s ease; opacity: 0.8;"
                        />
                    </svg>
                    <div>
                        <div class="text-[32px] font-bold text-white/85 leading-none">{{ $overallPct }}%</div>
                        <div class="text-[12px] text-white/35 mt-1">complete</div>
                        <div class="text-[11px] text-white/25 mt-2">{{ $completedCount }} of {{ $totalTasks }} tasks done</div>
                        @if($project->end_date)
                            <div class="text-[11px] mt-1.5 {{ $project->end_date->isPast() && $project->status !== 'completed' ? 'text-red-400' : 'text-white/28' }}">
                                Due {{ $project->end_date->format('M j, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Client card (if linked) --}}
            @if($project->client)
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <div class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-3">Client</div>
                <a href="{{ route('org.clients.show', [$project->organization, $project->client]) }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-orange-500/15 text-orange-400 text-sm font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($project->client->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-semibold text-white/75 group-hover:text-white/90 truncate transition-colors">{{ $project->client->name }}</p>
                        @if($project->client->company)
                            <p class="text-[11px] text-white/35 truncate">{{ $project->client->company }}</p>
                        @endif
                        @if($project->client->email)
                            <p class="text-[11px] text-white/25 truncate">{{ $project->client->email }}</p>
                        @endif
                    </div>
                </a>
                @if($project->project_type === 'billing' && $project->hourly_rate)
                    <div class="mt-3 pt-3 border-t border-white/[0.05] text-[11px] text-white/38">
                        Rate: <span class="text-orange-400 font-medium">${{ number_format($project->hourly_rate, 2) }}/hr</span>
                    </div>
                @endif
                @if($project->project_type === 'fixed' && $project->budget)
                    <div class="mt-3 pt-3 border-t border-white/[0.05] text-[11px] text-white/38">
                        Budget: <span class="text-green-400 font-medium">${{ number_format($project->budget, 2) }}</span>
                    </div>
                @endif
            </div>
            @endif

            {{-- Team members --}}
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <div class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-3">Team</div>
                @if($project->members->isNotEmpty())
                    <div class="space-y-2.5">
                        @foreach($project->members as $member)
                            @php
                                $pivot = $member->pivot;
                                $role  = $pivot->role ?? 'member';
                            @endphp
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-orange-500/15 text-orange-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($member->name, 0, 2)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[12px] font-medium text-white/72 truncate">{{ $member->name }}</p>
                                    <p class="text-[10px] text-white/28 truncate">{{ $member->email }}</p>
                                </div>
                                <span class="shrink-0 text-[9px] px-2 py-0.5 rounded-full font-semibold {{ $roleColors[$role] ?? 'bg-white/[0.06] text-white/35' }}">
                                    {{ $roleLabels[$role] ?? ucfirst($role) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[12px] text-white/25">No team members yet.</p>
                @endif
            </div>

            {{-- Milestones (if any) --}}
            @if($project->milestones->isNotEmpty())
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <div class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-3">Milestones</div>
                <div class="space-y-3">
                    @foreach($project->milestones->take(4) as $ms)
                        @php
                            $mTotal = $ms->tasks->count();
                            $mDone  = $ms->tasks->where('is_completed', true)->count();
                            $mPct   = $mTotal > 0 ? round($mDone / $mTotal * 100) : 0;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[12px] font-medium text-white/60 truncate max-w-[140px]">{{ $ms->name }}</span>
                                <span class="text-[10px] text-white/30">{{ $mPct }}%</span>
                            </div>
                            <div class="h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ $mPct }}%; background: {{ $projectColor }};opacity:0.7;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Design Approval (fixed projects with design_url set) --}}
            @if($project->project_type === 'fixed' && $project->design_url)
            @php
                $designStatusConfig = [
                    'none'     => ['label' => 'Not Started',  'class' => 'bg-white/[0.07] text-white/40'],
                    'pending'  => ['label' => 'Pending',      'class' => 'bg-amber-500/15 text-amber-400'],
                    'approved' => ['label' => 'Approved',     'class' => 'bg-green-500/15 text-green-400'],
                    'rejected' => ['label' => 'Rejected',     'class' => 'bg-red-500/15 text-red-400'],
                ];
                $dsc = $designStatusConfig[$project->design_status ?? 'none'];
            @endphp
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5" x-data="designApproval()">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[11px] font-semibold text-white/25 uppercase tracking-widest">Design Approval</div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $dsc['class'] }}">{{ $dsc['label'] }}</span>
                </div>
                <a href="{{ $project->design_url }}" target="_blank"
                   class="flex items-center gap-2 text-[12px] text-orange-400/70 hover:text-orange-400 transition-colors mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    View Figma Design
                </a>
                @if($canEdit && in_array($project->design_status, ['none','pending','rejected']))
                <div class="space-y-2">
                    <textarea x-model="feedback" rows="2" placeholder="Feedback (optional)…"
                              class="w-full px-3 py-2 rounded-xl bg-white/[0.05] border border-white/[0.08] text-white/65 text-[12px] focus:outline-none placeholder-white/18 resize-none"></textarea>
                    <div class="flex gap-2">
                        <button @click="approve()"
                                class="flex-1 py-2 rounded-lg text-[12px] font-medium bg-green-500/15 text-green-400 hover:bg-green-500/25 transition-colors">
                            Approve Design
                        </button>
                        <button @click="reject()"
                                class="flex-1 py-2 rounded-lg text-[12px] font-medium bg-red-500/15 text-red-400 hover:bg-red-500/25 transition-colors">
                            Reject
                        </button>
                    </div>
                </div>
                @endif
                @if($project->design_feedback)
                    <p class="text-[12px] text-white/38 leading-relaxed mt-2 italic">"{{ $project->design_feedback }}"</p>
                @endif
            </div>
            @endif

        </div>

        {{-- ── RIGHT PANEL (2/3) ────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Overdue tasks (if any) --}}
            @if($overdue->isNotEmpty())
            <div class="bg-red-500/[0.05] border border-red-500/[0.15] rounded-2xl p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-[12px] font-semibold text-red-400">{{ $overdue->count() }} Overdue Task{{ $overdue->count() !== 1 ? 's' : '' }}</div>
                </div>
                <div class="space-y-1.5">
                    @foreach($overdue->take(5) as $task)
                    <div class="flex items-center gap-3 py-1.5 px-3 rounded-lg bg-red-500/[0.06]">
                        <div class="w-1.5 h-1.5 rounded-full bg-red-400 shrink-0"></div>
                        <span class="text-[12px] text-white/65 flex-1 truncate">{{ $task->title }}</span>
                        <span class="text-[10px] text-red-400/70 shrink-0">{{ $task->due_date->diffForHumans() }}</span>
                    </div>
                    @endforeach
                    @if($overdue->count() > 5)
                        <p class="text-[11px] text-red-400/50 pl-3 mt-1">+ {{ $overdue->count() - 5 }} more overdue</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Upcoming tasks --}}
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[11px] font-semibold text-white/25 uppercase tracking-widest">Upcoming — Next 7 Days</div>
                    <a href="{{ route('projects.show', $project) }}" class="text-[11px] text-white/30 hover:text-orange-400 transition-colors">View all tasks →</a>
                </div>

                @if($upcoming->isNotEmpty())
                    <div class="space-y-1">
                        @foreach($upcoming as $task)
                            @php
                                $daysLeft = now()->diffInDays($task->due_date, false);
                                $dueCls   = $daysLeft <= 1 ? 'text-orange-400' : 'text-white/30';
                            @endphp
                            <div class="flex items-center gap-3 py-2 px-3 rounded-xl hover:bg-white/[0.03] transition-colors group">
                                {{-- Checkbox shape --}}
                                <div class="w-4 h-4 rounded border border-white/[0.15] group-hover:border-orange-500/40 shrink-0 transition-colors"></div>
                                {{-- Title --}}
                                <span class="text-[13px] text-white/65 flex-1 truncate group-hover:text-white/80 transition-colors">{{ $task->title }}</span>
                                {{-- Assignee --}}
                                @if($task->assignee)
                                    <div class="w-5 h-5 rounded-full bg-orange-500/15 text-orange-300 text-[8px] font-bold flex items-center justify-center shrink-0"
                                         title="{{ $task->assignee->name }}">
                                        {{ strtoupper(substr($task->assignee->name, 0, 2)) }}
                                    </div>
                                @endif
                                {{-- Due date --}}
                                <span class="text-[10px] {{ $dueCls }} shrink-0">
                                    {{ $task->due_date->format('M j') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <div class="w-10 h-10 rounded-xl bg-green-500/10 flex items-center justify-center mx-auto mb-2.5">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-[13px] text-white/40 font-medium">All clear!</p>
                        <p class="text-[11px] text-white/22 mt-0.5">No tasks due in the next 7 days</p>
                    </div>
                @endif
            </div>

            {{-- Recent activity --}}
            <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
                <div class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-3">Recent Activity</div>

                @if($activities->isNotEmpty())
                    <div class="space-y-0">
                        @foreach($activities->take(12) as $activity)
                        <div class="flex items-start gap-3 py-2.5 border-b border-white/[0.04] last:border-0">
                            {{-- User avatar --}}
                            <div class="w-6 h-6 rounded-full bg-orange-500/15 text-orange-300 text-[8px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                                {{ strtoupper(substr($activity->user->name ?? '?', 0, 2)) }}
                            </div>
                            {{-- Description --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-[12px] text-white/55 leading-snug">
                                    {!! $activity->description !!}
                                    @if($activity->task)
                                        <span class="text-white/30"> on </span>
                                        <span class="text-white/55 font-medium">{{ Str::limit($activity->task->title, 40) }}</span>
                                    @endif
                                </p>
                            </div>
                            {{-- Time --}}
                            <span class="text-[10px] text-white/22 shrink-0 mt-0.5">{{ $activity->created_at->diffForHumans(null, true) }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[12px] text-white/25 py-4 text-center">No activity yet. Start by creating tasks.</p>
                @endif
            </div>

        </div>
    </div>

    @if($canEdit)
    <div class="bg-[#111120] border border-red-500/15 rounded-2xl p-5 mt-5">
        <h3 class="text-[14px] font-semibold text-red-400/80 mb-1">Danger Zone</h3>
        <p class="text-[12px] text-white/35 mb-4">Permanently delete this project and all its tasks, milestones, and documents. This action cannot be undone.</p>
        <form method="POST" action="{{ route('projects.destroy', $project) }}" x-data x-on:submit.prevent="$dispatch('confirm-modal', { title: 'Delete Project', message: 'Permanently delete &quot;{{ $project->name }}&quot; and all its data? This action cannot be undone.', confirmLabel: 'Delete Project', variant: 'danger', onConfirm: () => $el.submit() })">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-xl text-[12px] font-medium text-red-400/80 border border-red-500/20 hover:bg-red-500/10 hover:text-red-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete Project
            </button>
        </form>
    </div>
    @endif

</div>

<script>
function designApproval() {
    return {
        feedback: '',
        async approve() {
            await fetch('/api/projects/{{ $project->slug }}', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ design_status: 'approved', design_feedback: this.feedback || null }),
            });
            location.reload();
        },
        async reject() {
            if (!this.feedback.trim()) { alert('Please add feedback before rejecting.'); return; }
            await fetch('/api/projects/{{ $project->slug }}', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ design_status: 'rejected', design_feedback: this.feedback }),
            });
            location.reload();
        },
    };
}
</script>

</x-layouts.smartprojects>
