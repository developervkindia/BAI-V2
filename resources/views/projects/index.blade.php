<x-layouts.smartprojects title="Dashboard">

@php
    $total      = $projects->count();
    $completed  = $projects->where('status', 'completed')->count();
    $ongoing    = $projects->whereIn('status', ['in_progress'])->count();
    $delayed    = $projects->filter(fn($p) => $p->end_date && $p->end_date < now() && $p->status !== 'completed')->count();
    $overallPct = $total > 0 ? round($completed / $total * 100) : 0;
    $gaugeOffset = round(267 * (1 - $overallPct / 100), 1);
@endphp

<div x-data="{ showCreate: false, filterStatus: 'all', taskFilter: 'all' }">

    {{-- ============================================================ --}}
    {{-- PAGE HEADER                                                   --}}
    {{-- ============================================================ --}}
    <div class="px-6 lg:px-8 pt-6 pb-5 flex items-start justify-between">
        <div>
            <h1 class="text-[20px] font-bold text-white/88 leading-tight">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ explode(' ', auth()->user()->name ?? 'there')[0] }}!
            </h1>
            <p class="text-[13px] text-white/30 mt-0.5">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <button @click="showCreate = true"
                class="flex items-center gap-2 px-4 py-2 rounded-xl text-[13px] font-semibold text-white transition-colors shrink-0"
                style="background: #F97316;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="hidden sm:inline">New Project</span>
        </button>
    </div>

    {{-- ============================================================ --}}
    {{-- STAT CARDS                                                    --}}
    {{-- ============================================================ --}}
    <div class="px-6 lg:px-8 grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-emerald-400" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-medium text-white/30 bg-white/[0.05] px-2 py-0.5 rounded-full">Billed</span>
            </div>
            <p class="text-[26px] font-bold text-white/88 leading-none">${{ number_format($totalRevenue, 0) }}</p>
            <p class="text-[12px] text-white/35 mt-1.5">Total Revenue</p>
        </div>

        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-orange-500/10 flex items-center justify-center">
                    <svg style="width:18px;height:18px" class="text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                @if($ongoing > 0)
                    <span class="text-[10px] font-medium text-orange-400 bg-orange-500/10 px-2 py-0.5 rounded-full">{{ $ongoing }} active</span>
                @else
                    <span class="text-[10px] font-medium text-white/30 bg-white/[0.05] px-2 py-0.5 rounded-full">Total</span>
                @endif
            </div>
            <p class="text-[26px] font-bold text-white/88 leading-none">{{ $total }}</p>
            <p class="text-[12px] text-white/35 mt-1.5">Projects</p>
        </div>

        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-blue-500/10 flex items-center justify-center">
                    <svg style="width:18px;height:18px" class="text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-medium text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full">Logged</span>
            </div>
            <p class="text-[26px] font-bold text-white/88 leading-none">
                {{ number_format($totalHours, 1) }}<span class="text-[14px] font-medium text-white/35 ml-1">hrs</span>
            </p>
            <p class="text-[12px] text-white/35 mt-1.5">Time Spent</p>
        </div>

        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="w-9 h-9 rounded-xl bg-violet-500/10 flex items-center justify-center">
                    <svg style="width:18px;height:18px" class="text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] font-medium text-violet-400 bg-violet-500/10 px-2 py-0.5 rounded-full">Team</span>
            </div>
            <p class="text-[26px] font-bold text-white/88 leading-none">{{ $memberCount }}</p>
            <p class="text-[12px] text-white/35 mt-1.5">Resources</p>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- MIDDLE ROW: PROJECT TABLE + GAUGE                            --}}
    {{-- ============================================================ --}}
    <div class="px-6 lg:px-8 grid grid-cols-1 xl:grid-cols-3 gap-5 mb-5">

        {{-- Project Summary Table (2/3) --}}
        <div class="xl:col-span-2 bg-[#111120] border border-white/[0.07] rounded-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 pt-5 pb-3.5 border-b border-white/[0.05]">
                <h2 class="text-[14px] font-bold text-white/82">Project Summary</h2>
                <div class="flex items-center gap-1">
                    @foreach(['all' => 'All', 'in_progress' => 'Active', 'completed' => 'Done', 'on_hold' => 'On Hold'] as $val => $label)
                        <button @click="filterStatus = '{{ $val }}'"
                                :class="filterStatus === '{{ $val }}'
                                    ? 'bg-orange-500/15 text-orange-300 border-orange-500/20'
                                    : 'text-white/38 border-transparent hover:text-white/60 hover:bg-white/[0.04]'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if($projects->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/[0.04]">
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider">Project</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider hidden md:table-cell">Manager</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider hidden sm:table-cell">Due Date</th>
                            <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-2.5 text-left text-[10px] font-semibold text-white/25 uppercase tracking-wider">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.03]">
                        @foreach($projects as $project)
                        @php
                            $progress = $project->total_tasks > 0
                                ? (int) round(($project->completed_tasks / $project->total_tasks) * 100) : 0;
                            $scMap = [
                                'not_started' => ['label' => 'Not Started', 'class' => 'bg-white/[0.07] text-white/40'],
                                'in_progress' => ['label' => 'In Progress', 'class' => 'bg-orange-500/15 text-orange-400'],
                                'on_hold'     => ['label' => 'On Hold',     'class' => 'bg-amber-500/15 text-amber-400'],
                                'completed'   => ['label' => 'Completed',   'class' => 'bg-emerald-500/15 text-emerald-400'],
                                'cancelled'   => ['label' => 'Cancelled',   'class' => 'bg-red-500/15 text-red-400'],
                            ];
                            $sc = $scMap[$project->status] ?? ['label' => $project->status, 'class' => 'bg-white/[0.07] text-white/40'];
                            $isOverdue = $project->end_date && $project->end_date < now() && $project->status !== 'completed';
                            $projectColor = $project->color ?? '#F97316';
                        @endphp
                        <tr x-show="filterStatus === 'all' || filterStatus === '{{ $project->status }}'"
                            class="hover:bg-white/[0.02] transition-colors cursor-pointer group"
                            onclick="window.location='{{ route('projects.overview', $project) }}'">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-black/20"
                                         style="background: {{ $projectColor }};"></div>
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-semibold text-white/75 group-hover:text-white/90 truncate transition-colors">{{ $project->name }}</p>
                                        @if($project->client)
                                            <p class="text-[11px] text-white/30">{{ $project->client->name }}</p>
                                        @elseif($project->description)
                                            <p class="text-[11px] text-white/28 truncate max-w-[180px]">{{ $project->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 hidden md:table-cell">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-orange-500/15 text-orange-300 text-[9px] font-bold flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($project->owner->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <span class="text-[12px] text-white/45 truncate max-w-[100px]">{{ $project->owner->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5 hidden sm:table-cell">
                                @if($project->end_date)
                                    <span class="text-[12px] {{ $isOverdue ? 'text-red-400 font-medium' : 'text-white/40' }}">
                                        {{ $project->end_date->format('M j, Y') }}
                                        @if($isOverdue)<span class="ml-1 text-[9px] bg-red-500/10 text-red-400/80 px-1.5 py-0.5 rounded">Late</span>@endif
                                    </span>
                                @else
                                    <span class="text-[12px] text-white/18">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $sc['class'] }}">
                                    {{ $sc['label'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5" style="min-width:100px">
                                    <div class="flex-1 h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all"
                                             style="width: {{ $progress }}%; background: {{ $progress >= 100 ? '#10b981' : $projectColor }};"></div>
                                    </div>
                                    <span class="text-[11px] font-semibold text-white/45 w-7 text-right shrink-0">{{ $progress }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 px-6">
                <div class="w-14 h-14 rounded-2xl bg-orange-500/[0.08] flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-orange-400/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-[14px] font-semibold text-white/50 mb-1">No projects yet</p>
                <p class="text-[12px] text-white/28 text-center mb-4">Create your first project to start tracking work</p>
                <button @click="showCreate = true"
                        class="px-4 py-2 text-white text-[12px] font-semibold rounded-lg transition-colors"
                        style="background:#F97316">
                    Create Project
                </button>
            </div>
            @endif
        </div>

        {{-- Overall Progress Gauge (1/3) --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6 flex flex-col">
            <h2 class="text-[14px] font-bold text-white/82 mb-0.5">Overall Progress</h2>
            <p class="text-[12px] text-white/30 mb-5">Across all {{ $total }} project{{ $total !== 1 ? 's' : '' }}</p>

            <div class="flex justify-center">
                <svg viewBox="0 0 200 108" class="w-full max-w-[200px]">
                    <path d="M 15 100 A 85 85 0 0 0 185 100"
                          stroke="rgba(255,255,255,0.07)" stroke-width="14" fill="none" stroke-linecap="round"/>
                    <path d="M 15 100 A 85 85 0 0 0 185 100"
                          stroke="#F97316" stroke-width="14" fill="none" stroke-linecap="round"
                          stroke-dasharray="267"
                          stroke-dashoffset="{{ $gaugeOffset }}"/>
                    <text x="100" y="78" text-anchor="middle"
                          font-family="ui-sans-serif,system-ui,-apple-system,sans-serif"
                          font-size="34" font-weight="700" fill="rgba(255,255,255,0.88)">{{ $overallPct }}</text>
                    <text x="100" y="96" text-anchor="middle"
                          font-family="ui-sans-serif,system-ui,-apple-system,sans-serif"
                          font-size="11" fill="rgba(255,255,255,0.3)">% complete</text>
                </svg>
            </div>

            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-white/[0.06]">
                <div class="text-center">
                    <p class="text-[22px] font-bold text-white/85">{{ $completed }}</p>
                    <div class="w-5 h-1 bg-emerald-400 rounded-full mx-auto my-1.5"></div>
                    <p class="text-[11px] text-white/35">Done</p>
                </div>
                <div class="text-center">
                    <p class="text-[22px] font-bold text-white/85">{{ $delayed }}</p>
                    <div class="w-5 h-1 bg-red-400 rounded-full mx-auto my-1.5"></div>
                    <p class="text-[11px] text-white/35">Delayed</p>
                </div>
                <div class="text-center">
                    <p class="text-[22px] font-bold text-white/85">{{ $ongoing }}</p>
                    <div class="w-5 h-1 bg-orange-400 rounded-full mx-auto my-1.5"></div>
                    <p class="text-[11px] text-white/35">Active</p>
                </div>
            </div>

            @if($total > 0)
            <div class="mt-4">
                <div class="h-1.5 bg-white/[0.06] rounded-full overflow-hidden flex">
                    @if($completed > 0)
                        <div class="h-full bg-emerald-400 transition-all" style="width: {{ round($completed/$total*100) }}%"></div>
                    @endif
                    @if($ongoing > 0)
                        <div class="h-full bg-orange-400 transition-all" style="width: {{ round($ongoing/$total*100) }}%"></div>
                    @endif
                    @if($delayed > 0)
                        <div class="h-full bg-red-400 transition-all" style="width: {{ round($delayed/$total*100) }}%"></div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- BOTTOM ROW: TODAY'S TASKS + WORKLOAD                         --}}
    {{-- ============================================================ --}}
    <div class="px-6 lg:px-8 grid grid-cols-1 xl:grid-cols-2 gap-5 pb-8">

        {{-- Today's Tasks --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl">
            <div class="flex items-center justify-between px-5 pt-5 pb-3.5 border-b border-white/[0.05]">
                <div>
                    <h2 class="text-[14px] font-bold text-white/82">Today's Tasks</h2>
                    <p class="text-[11px] text-white/30 mt-0.5">Due today or overdue, assigned to you</p>
                </div>
                <div class="flex items-center gap-1">
                    @foreach(['all' => 'All', 'today' => 'Today', 'overdue' => 'Late'] as $val => $label)
                        <button @click="taskFilter = '{{ $val }}'"
                                :class="taskFilter === '{{ $val }}'
                                    ? 'bg-orange-500/15 text-orange-300 border-orange-500/20'
                                    : 'text-white/35 border-transparent hover:text-white/60 hover:bg-white/[0.04]'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-medium border transition-all">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            @if($todaysTasks->isNotEmpty())
            <div class="px-3 py-3 space-y-1 max-h-72 overflow-y-auto">
                @foreach($todaysTasks as $task)
                @php
                    $dueDate   = \Carbon\Carbon::parse($task->due_date);
                    $isToday   = $dueDate->isToday();
                    $isOverdue = $dueDate->isPast() && !$isToday;
                    $pColors   = [
                        'critical' => 'bg-red-500',
                        'high'     => 'bg-orange-500',
                        'medium'   => 'bg-yellow-400',
                        'low'      => 'bg-blue-400',
                        'none'     => 'bg-white/20',
                    ];
                    $pBar = $pColors[$task->priority ?? 'none'] ?? 'bg-white/20';
                    $taskColor = $task->project_color ?? '#F97316';
                @endphp
                <div class="flex items-center gap-3 px-2 py-2.5 rounded-xl hover:bg-white/[0.03] transition-colors"
                     x-show="taskFilter === 'all'
                        || (taskFilter === 'today' && {{ $isToday ? 'true' : 'false' }})
                        || (taskFilter === 'overdue' && {{ $isOverdue ? 'true' : 'false' }})">
                    <div class="w-1 h-9 rounded-full shrink-0 {{ $pBar }}"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-medium text-white/75 truncate leading-tight">{{ $task->title }}</p>
                        <p class="text-[11px] text-white/30 mt-0.5 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 inline-block" style="background: {{ $taskColor }}"></span>
                            {{ $task->project_name }}
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="block text-[11px] font-semibold {{ $isOverdue ? 'text-red-400' : 'text-white/40' }}">
                            {{ $isToday ? 'Today' : $dueDate->format('M j') }}
                        </span>
                        @if($isOverdue)
                            <span class="block text-[9px] text-red-400/70 font-medium uppercase tracking-wide mt-0.5">Overdue</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center py-14 px-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-[13px] font-semibold text-white/45 mb-1">All caught up!</p>
                <p class="text-[12px] text-white/25 text-center">No tasks due today or overdue.</p>
            </div>
            @endif
        </div>

        {{-- Projects Workload --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl">
            <div class="flex items-center justify-between px-5 pt-5 pb-3.5 border-b border-white/[0.05]">
                <div>
                    <h2 class="text-[14px] font-bold text-white/82">Projects Workload</h2>
                    <p class="text-[11px] text-white/30 mt-0.5">Open tasks per team member</p>
                </div>
            </div>

            @if($workload->isNotEmpty())
            <div class="px-5 py-4 space-y-4">
                @php
                    $maxTasks  = $workload->max('task_count') ?: 1;
                    $avatarBgs = ['bg-orange-500/20 text-orange-300', 'bg-blue-500/20 text-blue-300', 'bg-violet-500/20 text-violet-300', 'bg-teal-500/20 text-teal-300', 'bg-pink-500/20 text-pink-300', 'bg-indigo-500/20 text-indigo-300'];
                    $barColors = ['bg-orange-500', 'bg-blue-500', 'bg-violet-500', 'bg-teal-500', 'bg-pink-500', 'bg-indigo-500'];
                @endphp
                @foreach($workload as $i => $member)
                @php
                    $avatarBg = $avatarBgs[$i % count($avatarBgs)];
                    $barColor = $barColors[$i % count($barColors)];
                    $pct      = round($member->task_count / $maxTasks * 100);
                @endphp
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full {{ $avatarBg }} text-[10px] font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($member->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[12px] font-medium text-white/65 truncate">{{ $member->name }}</span>
                            <span class="text-[12px] font-bold text-white/80 ml-3 shrink-0">{{ $member->task_count }}<span class="font-normal text-white/30 ml-0.5">tasks</span></span>
                        </div>
                        <div class="h-1.5 bg-white/[0.06] rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $barColor }} transition-all opacity-70"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="flex flex-col items-center py-14 px-4">
                <p class="text-[12px] text-white/28 text-center">No open tasks assigned to team members yet.</p>
            </div>
            @endif
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- CREATE PROJECT MODAL                                         --}}
    {{-- ============================================================ --}}
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="showCreate = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div x-data="createProjectModal()"
             class="relative bg-[#16162A] border border-white/[0.12] rounded-2xl w-full max-w-lg p-6 shadow-2xl overflow-y-auto max-h-[90vh]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between mb-5">
                <h2 class="text-[16px] font-bold text-white/85">New Project</h2>
                <button @click="showCreate = false"
                        class="w-7 h-7 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/40 hover:text-white/70 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('projects.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Project Name <span class="text-red-400/80">*</span></label>
                    <input type="text" name="name" required autofocus placeholder="e.g. Website Redesign"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Project Type</label>
                    <div class="flex gap-2">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="project_type" value="fixed" x-model="projectType" class="sr-only">
                            <div :class="projectType === 'fixed' ? 'border-orange-500/50 bg-orange-500/10 text-orange-300' : 'border-white/[0.1] bg-white/[0.03] text-white/45 hover:border-white/20'"
                                 class="px-3 py-2.5 rounded-xl border text-[13px] font-medium text-center transition-all">Fixed / Milestone</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="project_type" value="billing" x-model="projectType" class="sr-only">
                            <div :class="projectType === 'billing' ? 'border-orange-500/50 bg-orange-500/10 text-orange-300' : 'border-white/[0.1] bg-white/[0.03] text-white/45 hover:border-white/20'"
                                 class="px-3 py-2.5 rounded-xl border text-[13px] font-medium text-center transition-all">Billing / Hourly</div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Client</label>
                        <select name="client_id" x-ref="clientSelect"
                                class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none appearance-none">
                            <option value="">No client</option>
                        </select>
                    </div>
                    <div x-show="projectType === 'fixed'">
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Budget ($)</label>
                        <input type="number" name="budget" min="0" step="0.01" placeholder="0.00"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                    </div>
                    <div x-show="projectType === 'billing'">
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Hourly Rate ($/hr)</label>
                        <input type="number" name="hourly_rate" min="0" step="0.01" placeholder="0.00"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18"/>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Description</label>
                    <textarea name="description" rows="2" placeholder="What is this project about?"
                              class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none placeholder-white/18 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Status</label>
                        <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none appearance-none">
                            <option value="not_started">Not Started</option>
                            <option value="in_progress">In Progress</option>
                            <option value="on_hold">On Hold</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Priority</label>
                        <select name="priority" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none appearance-none">
                            <option value="none">None</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Start Date</label>
                        <input type="date" name="start_date" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none"/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Due Date</label>
                        <input type="date" name="end_date" class="w-full px-3 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/65 text-[13px] focus:outline-none"/>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Color</label>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['#6366f1','#F97316','#10b981','#3b82f6','#ec4899','#8b5cf6','#ef4444','#06b6d4'] as $i => $clr)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $clr }}" class="sr-only" {{ $i === 1 ? 'checked' : '' }}>
                                <div class="w-7 h-7 rounded-full ring-2 ring-transparent hover:ring-white/30 transition-all" style="background: {{ $clr }};"></div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showCreate = false"
                            class="flex-1 py-2.5 rounded-xl border border-white/[0.1] text-white/40 text-[13px] hover:border-white/20 hover:text-white/60 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl text-[13px] font-semibold text-white bg-orange-500 hover:bg-orange-400 transition-colors">
                        Create Project
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function createProjectModal() {
    return {
        projectType: 'fixed',
        async init() {
            try {
                const res = await fetch('/api/clients');
                if (res.ok) {
                    const clients = await res.json();
                    const sel = this.$refs.clientSelect;
                    clients.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.company ? c.name + ' (' + c.company + ')' : c.name;
                        sel.appendChild(opt);
                    });
                }
            } catch(e) {}
        },
    };
}
</script>

</x-layouts.smartprojects>
