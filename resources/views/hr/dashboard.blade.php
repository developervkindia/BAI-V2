<x-layouts.hr title="Dashboard" currentView="dashboard">

<div class="p-5 lg:p-7 space-y-6" x-data="{
    activeTab: 'joiners',
}">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-[22px] font-bold text-white/85 tracking-tight">Dashboard</h1>
            <p class="text-[13px] text-white/40 mt-0.5">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[12px] text-white/30 font-medium">Welcome back,</span>
            <span class="text-[13px] text-white/70 font-semibold">{{ auth()->user()->name ?? 'Admin' }}</span>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total Employees --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Total Employees</p>
                    <p class="text-[32px] font-bold text-white/85 leading-tight mt-1">{{ number_format($totalEmployees) }}</p>
                    <p class="text-[12px] text-white/35 mt-1">All registered employees</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center shrink-0 group-hover:bg-cyan-500/15 transition-colors">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Active --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Active</p>
                    <p class="text-[32px] font-bold text-emerald-400/90 leading-tight mt-1">{{ number_format($activeCount) }}</p>
                    <p class="text-[12px] text-white/35 mt-1">Currently active</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0 group-hover:bg-emerald-500/15 transition-colors">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- On Leave --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">On Leave</p>
                    <p class="text-[32px] font-bold text-amber-400/90 leading-tight mt-1">{{ number_format($onLeaveCount) }}</p>
                    <p class="text-[12px] text-white/35 mt-1">Away today</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center shrink-0 group-hover:bg-amber-500/15 transition-colors">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Departments --}}
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:bg-[#1D1D35] hover:border-white/[0.13] transition-all duration-200 group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Departments</p>
                    <p class="text-[32px] font-bold text-violet-400/90 leading-tight mt-1">{{ $departmentBreakdown->count() }}</p>
                    <p class="text-[12px] text-white/35 mt-1">Active departments</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center shrink-0 group-hover:bg-violet-500/15 transition-colors">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    {{-- Two Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- LEFT COLUMN --}}
        <div class="space-y-5">

            {{-- Department Breakdown --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.06]">
                    <h2 class="text-[14px] font-semibold text-white/85">Department Breakdown</h2>
                    <p class="text-[12px] text-white/35 mt-0.5">Employees per department</p>
                </div>
                <div class="p-5 space-y-3">
                    @php
                        $maxCount = $departmentBreakdown->max('employees_count') ?: 1;
                    @endphp
                    @forelse($departmentBreakdown as $dept)
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[13px] text-white/65 font-medium">{{ $dept->name }}</span>
                                <span class="text-[12px] text-white/45 font-semibold tabular-nums">{{ $dept->employees_count }}</span>
                            </div>
                            <div class="h-[6px] bg-white/[0.06] rounded-full overflow-hidden">
                                <div class="h-full rounded-full bg-gradient-to-r from-cyan-500/80 to-cyan-400/60 transition-all duration-500"
                                     style="width: {{ round(($dept->employees_count / $maxCount) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-[13px] text-white/30 text-center py-6">No departments found</p>
                    @endforelse
                </div>
            </div>

            {{-- New Joiners --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                    <div>
                        <h2 class="text-[14px] font-semibold text-white/85">New Joiners</h2>
                        <p class="text-[12px] text-white/35 mt-0.5">Recent additions to the team</p>
                    </div>
                    <span class="text-[11px] font-semibold text-cyan-400/70 bg-cyan-500/10 px-2.5 py-1 rounded-full">{{ $newJoiners->count() }} new</span>
                </div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse($newJoiners->take(5) as $joiner)
                        @php
                            $name = $joiner->user->name ?? ($joiner->first_name . ' ' . $joiner->last_name);
                            $initials = strtoupper(collect(explode(' ', $name))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                            $deptName = $joiner->department->name ?? 'Unassigned';
                            $joinDate = $joiner->joined_at ?? $joiner->created_at;
                        @endphp
                        <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-white/[0.02] transition-colors">
                            <div class="w-9 h-9 rounded-full bg-cyan-500/15 text-cyan-400 text-[11px] font-bold flex items-center justify-center shrink-0">
                                {{ $initials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-medium text-white/80 truncate">{{ $name }}</p>
                                <p class="text-[11px] text-white/35 truncate">{{ $deptName }}</p>
                            </div>
                            <span class="text-[11px] text-white/30 font-medium tabular-nums shrink-0">
                                {{ $joinDate ? \Carbon\Carbon::parse($joinDate)->format('M d, Y') : '—' }}
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <svg class="w-8 h-8 text-white/15 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <p class="text-[13px] text-white/30">No recent joiners</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="space-y-5">

            {{-- Upcoming Birthdays --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-pink-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.75 1.75 0 003 15.546m18 0v2.704A1.75 1.75 0 0119.25 20H4.75A1.75 1.75 0 013 18.25v-2.704m18 0A1.75 1.75 0 0019.25 14H4.75A1.75 1.75 0 003 15.546M12 4v3m-2-1h4m-6 4h8l1 3H5l1-3z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[14px] font-semibold text-white/85">Upcoming Birthdays</h2>
                            <p class="text-[12px] text-white/35 mt-0.5">Next 7 days</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-pink-400/70 bg-pink-500/10 px-2.5 py-1 rounded-full">{{ $upcomingBirthdays->count() }}</span>
                </div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse($upcomingBirthdays as $birthday)
                        @php
                            $bName = $birthday->user->name ?? ($birthday->first_name . ' ' . $birthday->last_name);
                            $bInitials = strtoupper(collect(explode(' ', $bName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                            $bDate = \Carbon\Carbon::parse($birthday->date_of_birth);
                            $nextBirthday = $bDate->copy()->year(now()->year);
                            if ($nextBirthday->isPast() && !$nextBirthday->isToday()) {
                                $nextBirthday->addYear();
                            }
                            $daysUntil = now()->startOfDay()->diffInDays($nextBirthday->startOfDay(), false);
                        @endphp
                        <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-white/[0.02] transition-colors">
                            <div class="w-9 h-9 rounded-full bg-pink-500/15 text-pink-400 text-[11px] font-bold flex items-center justify-center shrink-0">
                                {{ $bInitials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-medium text-white/80 truncate">{{ $bName }}</p>
                                <p class="text-[11px] text-white/35">{{ $bDate->format('M d') }}</p>
                            </div>
                            @if($daysUntil === 0)
                                <span class="text-[10px] font-bold text-pink-400 bg-pink-500/15 px-2 py-0.5 rounded-full shrink-0">TODAY</span>
                            @else
                                <span class="text-[11px] text-white/30 font-medium shrink-0">in {{ $daysUntil }} {{ Str::plural('day', $daysUntil) }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <svg class="w-8 h-8 text-white/15 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.75 1.75 0 003 15.546"/>
                            </svg>
                            <p class="text-[13px] text-white/30">No birthdays this week</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Upcoming Anniversaries --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[14px] font-semibold text-white/85">Upcoming Anniversaries</h2>
                            <p class="text-[12px] text-white/35 mt-0.5">Next 7 days</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-amber-400/70 bg-amber-500/10 px-2.5 py-1 rounded-full">{{ $upcomingAnniversaries->count() }}</span>
                </div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse($upcomingAnniversaries as $anniversary)
                        @php
                            $aName = $anniversary->user->name ?? ($anniversary->first_name . ' ' . $anniversary->last_name);
                            $aInitials = strtoupper(collect(explode(' ', $aName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                            $joinDate = \Carbon\Carbon::parse($anniversary->joining_date);
                            $years = $joinDate->diffInYears(now());
                            $nextAnniversary = $joinDate->copy()->year(now()->year);
                            if ($nextAnniversary->isPast() && !$nextAnniversary->isToday()) {
                                $nextAnniversary->addYear();
                                $years = $joinDate->diffInYears($nextAnniversary);
                            }
                            $daysUntilAnniv = now()->startOfDay()->diffInDays($nextAnniversary->startOfDay(), false);
                        @endphp
                        <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-white/[0.02] transition-colors">
                            <div class="w-9 h-9 rounded-full bg-amber-500/15 text-amber-400 text-[11px] font-bold flex items-center justify-center shrink-0">
                                {{ $aInitials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-medium text-white/80 truncate">{{ $aName }}</p>
                                <p class="text-[11px] text-white/35">{{ $years }} {{ Str::plural('year', $years) }} &middot; {{ $joinDate->format('M d, Y') }}</p>
                            </div>
                            @if($daysUntilAnniv === 0)
                                <span class="text-[10px] font-bold text-amber-400 bg-amber-500/15 px-2 py-0.5 rounded-full shrink-0">TODAY</span>
                            @else
                                <span class="text-[11px] text-white/30 font-medium shrink-0">in {{ $daysUntilAnniv }} {{ Str::plural('day', $daysUntilAnniv) }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <svg class="w-8 h-8 text-white/15 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                            <p class="text-[13px] text-white/30">No anniversaries this week</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Exits --}}
            <div class="bg-[#17172A] border border-white/[0.07] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-[14px] font-semibold text-white/85">Recent Exits</h2>
                            <p class="text-[12px] text-white/35 mt-0.5">Last 5 separations</p>
                        </div>
                    </div>
                    <span class="text-[11px] font-semibold text-red-400/70 bg-red-500/10 px-2.5 py-1 rounded-full">{{ $recentExits->count() }}</span>
                </div>
                <div class="divide-y divide-white/[0.05]">
                    @forelse($recentExits->take(5) as $exit)
                        @php
                            $eName = $exit->employeeProfile->user->name ?? ($exit->employeeProfile->first_name . ' ' . $exit->employeeProfile->last_name);
                            $eInitials = strtoupper(collect(explode(' ', $eName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                            $exitType = ucfirst($exit->type ?? 'Resignation');
                            $lastDay = $exit->last_working_date ? \Carbon\Carbon::parse($exit->last_working_date)->format('M d, Y') : '—';
                            $exitStatus = $exit->status ?? 'pending';
                        @endphp
                        <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-white/[0.02] transition-colors">
                            <div class="w-9 h-9 rounded-full bg-red-500/10 text-red-400/70 text-[11px] font-bold flex items-center justify-center shrink-0">
                                {{ $eInitials }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-medium text-white/80 truncate">{{ $eName }}</p>
                                <p class="text-[11px] text-white/35">{{ $exitType }} &middot; LWD: {{ $lastDay }}</p>
                            </div>
                            @php
                                $statusColors = [
                                    'pending'   => 'text-amber-400/80 bg-amber-500/10',
                                    'approved'  => 'text-emerald-400/80 bg-emerald-500/10',
                                    'completed' => 'text-white/45 bg-white/[0.06]',
                                    'rejected'  => 'text-red-400/80 bg-red-500/10',
                                    'withdrawn' => 'text-white/35 bg-white/[0.04]',
                                ];
                                $statusClass = $statusColors[strtolower($exitStatus)] ?? 'text-white/45 bg-white/[0.06]';
                            @endphp
                            <span class="text-[10px] font-semibold {{ $statusClass }} px-2 py-0.5 rounded-full shrink-0 capitalize">
                                {{ $exitStatus }}
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center">
                            <svg class="w-8 h-8 text-white/15 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <p class="text-[13px] text-white/30">No recent exits</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

</x-layouts.hr>
