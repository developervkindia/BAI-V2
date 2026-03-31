<x-layouts.smartprojects :project="$project" currentView="reports" :canEdit="$canEdit">

<div class="px-6 py-5 max-w-screen-xl mx-auto space-y-5">

    {{-- Report type selector + Filters --}}
    <div class="flex items-center gap-3 flex-wrap">
        @foreach([
            'task-progress'  => 'Task Progress',
            'time-tracking'  => 'Time Tracking',
            'milestones'     => 'Milestones',
            'burndown'       => 'Burn-down',
        ] as $key => $label)
            <a href="{{ route('projects.reports', [$project, 'report' => $key, 'date_from' => $from->format('Y-m-d'), 'date_to' => $to->format('Y-m-d')]) }}"
               class="px-3.5 py-1.5 rounded-lg text-[12px] font-medium border transition-all
                {{ $reportType === $key ? 'bg-orange-500/15 text-orange-300 border-orange-500/20' : 'text-white/38 border-transparent hover:text-white/60 hover:bg-white/[0.04]' }}">
                {{ $label }}
            </a>
        @endforeach

        <div class="flex-1"></div>

        <form method="GET" action="{{ route('projects.reports', $project) }}" class="flex items-center gap-2">
            <input type="hidden" name="report" value="{{ $reportType }}">
            <input type="date" name="date_from" value="{{ $from->format('Y-m-d') }}"
                class="px-2.5 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-[11px] text-white/55 focus:outline-none focus:ring-1 focus:ring-orange-500/40"/>
            <span class="text-[11px] text-white/25">to</span>
            <input type="date" name="date_to" value="{{ $to->format('Y-m-d') }}"
                class="px-2.5 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-[11px] text-white/55 focus:outline-none focus:ring-1 focus:ring-orange-500/40"/>
            <button type="submit" class="px-3 py-1.5 rounded-lg text-[11px] bg-orange-500/20 text-orange-400 hover:bg-orange-500/30 transition-colors">Apply</button>
        </form>
    </div>

    {{-- ================================================================ --}}
    {{-- TASK PROGRESS REPORT                                              --}}
    {{-- ================================================================ --}}
    @if($reportType === 'task-progress')
        {{-- By Assignee --}}
        @if(!empty($reportData['byAssignee']))
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <h3 class="text-[13px] font-semibold text-white/65 mb-4">Tasks by Assignee</h3>
            <div class="space-y-2.5">
                @foreach($reportData['byAssignee'] as $row)
                @php
                    $pct = $row->total > 0 ? round($row->completed / $row->total * 100) : 0;
                @endphp
                <div class="flex items-center gap-3">
                    <span class="text-[12px] text-white/60 w-32 truncate">{{ $row->name ?? 'Unassigned' }}</span>
                    <div class="flex-1 h-2 bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full bg-green-500/60 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-[11px] text-white/40 shrink-0 w-20 text-right">{{ $row->completed }}/{{ $row->total }} ({{ $pct }}%)</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- By Milestone --}}
        @if(!empty($reportData['byMilestone']))
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <h3 class="text-[13px] font-semibold text-white/65 mb-4">Tasks by Milestone</h3>
            <div class="space-y-2.5">
                @foreach($reportData['byMilestone'] as $row)
                @php $pct = $row->total > 0 ? round($row->completed / $row->total * 100) : 0; @endphp
                <div class="flex items-center gap-3">
                    <span class="text-[12px] text-white/60 w-32 truncate">{{ $row->name ?? 'No Milestone' }}</span>
                    <div class="flex-1 h-2 bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full bg-orange-500/60 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-[11px] text-white/40 shrink-0 w-20 text-right">{{ $row->completed }}/{{ $row->total }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Completion over time --}}
        @if(!empty($reportData['completion']))
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <h3 class="text-[13px] font-semibold text-white/65 mb-4">Task Completion Over Time</h3>
            @php
                $createdData = $reportData['completion']['created'] ?? collect();
                $completedData = $reportData['completion']['completed'] ?? collect();
            @endphp
            @if($completedData->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead><tr class="border-b border-white/[0.06]">
                        <th class="text-left py-2 text-[10px] text-white/30 font-medium">Date</th>
                        <th class="text-right py-2 text-[10px] text-white/30 font-medium">Created</th>
                        <th class="text-right py-2 text-[10px] text-white/30 font-medium">Completed</th>
                    </tr></thead>
                    <tbody>
                    @foreach($completedData as $row)
                        <tr class="border-b border-white/[0.03]">
                            <td class="py-1.5 text-[12px] text-white/50">{{ $row->date }}</td>
                            <td class="py-1.5 text-[12px] text-blue-400 text-right">{{ $createdData->firstWhere('date', $row->date)?->count ?? 0 }}</td>
                            <td class="py-1.5 text-[12px] text-green-400 text-right">{{ $row->count }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p class="text-[12px] text-white/25 text-center py-4">No completed tasks in this period</p>
            @endif
        </div>
        @endif
    @endif

    {{-- ================================================================ --}}
    {{-- TIME TRACKING REPORT                                              --}}
    {{-- ================================================================ --}}
    @if($reportType === 'time-tracking')
        @if(!empty($reportData['byUser']))
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <h3 class="text-[13px] font-semibold text-white/65 mb-4">Hours by User</h3>
            @php $maxHours = $reportData['byUser']->max('total_hours') ?: 1; @endphp
            <div class="space-y-2.5">
                @foreach($reportData['byUser'] as $row)
                <div class="flex items-center gap-3">
                    <span class="text-[12px] text-white/60 w-32 truncate">{{ $row->name }}</span>
                    <div class="flex-1 h-2 bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full bg-orange-500/60 rounded-full" style="width: {{ round($row->total_hours / $maxHours * 100) }}%"></div>
                    </div>
                    <span class="text-[11px] text-white/45 shrink-0 w-16 text-right font-medium">{{ round($row->total_hours, 1) }}h</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($reportData['byTask']))
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <h3 class="text-[13px] font-semibold text-white/65 mb-4">Hours by Task (Top 20)</h3>
            <table class="w-full">
                <thead><tr class="border-b border-white/[0.06]">
                    <th class="text-left py-2 text-[10px] text-white/30 font-medium">Task</th>
                    <th class="text-right py-2 text-[10px] text-white/30 font-medium">Estimated</th>
                    <th class="text-right py-2 text-[10px] text-white/30 font-medium">Actual</th>
                    <th class="text-right py-2 text-[10px] text-white/30 font-medium">Variance</th>
                </tr></thead>
                <tbody>
                @foreach($reportData['byTask'] as $row)
                    @php $variance = ($row->estimated_hours ?? 0) - $row->total_hours; @endphp
                    <tr class="border-b border-white/[0.03]">
                        <td class="py-1.5 text-[12px] text-white/60 max-w-[300px] truncate">{{ $row->title }}</td>
                        <td class="py-1.5 text-[12px] text-white/35 text-right">{{ $row->estimated_hours ?? '—' }}h</td>
                        <td class="py-1.5 text-[12px] text-white/55 text-right font-medium">{{ round($row->total_hours, 1) }}h</td>
                        <td class="py-1.5 text-[12px] text-right font-medium {{ $variance >= 0 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $variance >= 0 ? '+' : '' }}{{ round($variance, 1) }}h
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    @endif

    {{-- ================================================================ --}}
    {{-- MILESTONES REPORT                                                 --}}
    {{-- ================================================================ --}}
    @if($reportType === 'milestones')
        @if(!empty($reportData['milestones']))
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <h3 class="text-[13px] font-semibold text-white/65 mb-4">Milestone Progress</h3>
            <div class="space-y-3">
                @foreach($reportData['milestones'] as $m)
                @php
                    $isOverdue = $m->due_date && \Illuminate\Support\Carbon::parse($m->due_date)->isPast() && $m->progress < 100;
                    $statusColor = $m->progress >= 100 ? '#22C55E' : ($isOverdue ? '#EF4444' : ($m->progress > 50 ? '#F59E0B' : '#94A3B8'));
                @endphp
                <div class="bg-white/[0.03] rounded-xl p-4">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-[13px] font-medium text-white/70 flex-1">{{ $m->name }}</span>
                        @if($m->due_date)
                            <span class="text-[10px] {{ $isOverdue ? 'text-red-400' : 'text-white/30' }}">
                                Due {{ \Illuminate\Support\Carbon::parse($m->due_date)->format('M j, Y') }}
                            </span>
                        @endif
                        <span class="text-[11px] font-bold" style="color: {{ $statusColor }}">{{ $m->progress }}%</span>
                    </div>
                    <div class="h-2 bg-white/[0.06] rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width: {{ $m->progress }}%; background: {{ $statusColor }}"></div>
                    </div>
                    <div class="text-[10px] text-white/25 mt-1.5">{{ $m->completed_tasks }}/{{ $m->total_tasks }} tasks completed</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif

    {{-- ================================================================ --}}
    {{-- BURNDOWN REPORT                                                   --}}
    {{-- ================================================================ --}}
    @if($reportType === 'burndown')
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-4">
                <h3 class="text-[13px] font-semibold text-white/65">Sprint Burn-down</h3>
                <form method="GET" action="{{ route('projects.reports', $project) }}" class="flex items-center gap-2 ml-auto">
                    <input type="hidden" name="report" value="burndown">
                    <select name="sprint_id" onchange="this.form.submit()"
                        class="px-2.5 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-[11px] text-white/55 focus:outline-none">
                        <option value="">Select Sprint</option>
                        @foreach($project->sprints as $s)
                            <option value="{{ $s->id }}" {{ request('sprint_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if(!empty($reportData['burndown']))
                @php $bd = $reportData['burndown']; @endphp
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="text-center">
                        <div class="text-[20px] font-bold text-white/70">{{ $bd['total'] }}</div>
                        <div class="text-[10px] text-white/30">Total Points</div>
                    </div>
                    <div class="text-center">
                        <div class="text-[20px] font-bold text-green-400">{{ $bd['daily_completion']->sum('points') }}</div>
                        <div class="text-[10px] text-white/30">Completed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-[20px] font-bold text-orange-400">{{ $bd['total'] - $bd['daily_completion']->sum('points') }}</div>
                        <div class="text-[10px] text-white/30">Remaining</div>
                    </div>
                </div>
                @if($bd['daily_completion']->count() > 0)
                <table class="w-full">
                    <thead><tr class="border-b border-white/[0.06]">
                        <th class="text-left py-2 text-[10px] text-white/30">Date</th>
                        <th class="text-right py-2 text-[10px] text-white/30">Points Completed</th>
                    </tr></thead>
                    <tbody>
                    @foreach($bd['daily_completion'] as $row)
                        <tr class="border-b border-white/[0.03]">
                            <td class="py-1.5 text-[12px] text-white/50">{{ $row->date }}</td>
                            <td class="py-1.5 text-[12px] text-green-400 text-right">{{ $row->points }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            @else
                <p class="text-[12px] text-white/25 text-center py-6">Select a sprint to view burn-down data</p>
            @endif
        </div>
    @endif
</div>

</x-layouts.smartprojects>
