<x-layouts.hr title="Work Anniversaries" currentView="engagement">

<div class="p-5 lg:p-7 space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('hr.engagement.index') }}" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/35 hover:text-white/60 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-[22px] font-bold text-white/85 tracking-tight flex items-center gap-2.5">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        Work Anniversaries
                    </h1>
                    <p class="text-[13px] text-white/40 mt-0.5">Celebrate milestones and tenure</p>
                </div>
            </div>
        </div>
    </div>

    @php
        $now = now();
        $thisWeek = collect();
        $thisMonth = collect();

        foreach ($upcomingAnniversaries as $person) {
            $joinDate = \Carbon\Carbon::parse($person->joining_date);
            $nextAnniv = $joinDate->copy()->year($now->year);
            if ($nextAnniv->isPast() && !$nextAnniv->isToday()) {
                $nextAnniv->addYear();
            }
            $years = $joinDate->diffInYears($nextAnniv);
            $daysUntil = $now->startOfDay()->diffInDays($nextAnniv->startOfDay(), false);

            $person->_next_anniversary = $nextAnniv;
            $person->_years_of_service = $years;
            $person->_days_until = $daysUntil;

            if ($daysUntil >= 0 && $daysUntil <= 7) {
                $thisWeek->push($person);
            } elseif ($nextAnniv->month === $now->month && $nextAnniv->year === $now->year) {
                $thisMonth->push($person);
            } elseif ($daysUntil > 7 && $daysUntil <= 60) {
                $thisMonth->push($person);
            }
        }

        $thisWeek = $thisWeek->sortBy('_days_until');
        $thisMonth = $thisMonth->sortBy('_days_until');
    @endphp

    {{-- This Week --}}
    @if($thisWeek->count() > 0)
        <div class="space-y-3">
            <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                This Week
                <span class="text-amber-400/70 bg-amber-500/10 px-2 py-0.5 rounded-full text-[10px] ml-1">{{ $thisWeek->count() }}</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($thisWeek as $person)
                    @php
                        $pName = $person->user->name ?? ($person->first_name . ' ' . $person->last_name);
                        $pInitials = strtoupper(collect(explode(' ', $pName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                        $deptName = $person->department->name ?? 'Unassigned';
                        $joinDate = \Carbon\Carbon::parse($person->joining_date);
                    @endphp
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-amber-500/20 hover:bg-[#1D1D35] transition-all group">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-3">
                                <div class="w-14 h-14 rounded-full bg-amber-500/15 text-amber-400 text-lg font-bold flex items-center justify-center group-hover:bg-amber-500/25 transition-colors">
                                    {{ $pInitials }}
                                </div>
                                <div class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-amber-500/20 border-2 border-[#17172A] flex items-center justify-center">
                                    <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </div>
                            </div>
                            <h3 class="text-[14px] font-semibold text-white/85">{{ $pName }}</h3>
                            <p class="text-[12px] text-white/40 mt-0.5">{{ $deptName }}</p>
                            <div class="mt-2 flex items-center gap-1.5">
                                <span class="text-[16px] font-bold text-amber-400">{{ $person->_years_of_service }}</span>
                                <span class="text-[11px] text-amber-400/60 font-medium">{{ Str::plural('year', $person->_years_of_service) }}</span>
                            </div>
                            <p class="text-[11px] text-white/30 mt-1">{{ $joinDate->format('M d, Y') }}</p>
                            @if($person->_days_until === 0)
                                <span class="mt-2 text-[10px] font-bold text-amber-400 bg-amber-500/15 px-2.5 py-1 rounded-full">
                                    TODAY
                                </span>
                            @else
                                <span class="mt-2 text-[11px] text-white/30 font-medium">
                                    in {{ $person->_days_until }} {{ Str::plural('day', $person->_days_until) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- This Month --}}
    @if($thisMonth->count() > 0)
        <div class="space-y-3">
            <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest flex items-center gap-2">
                This Month
                <span class="text-white/25 bg-white/[0.06] px-2 py-0.5 rounded-full text-[10px] ml-1">{{ $thisMonth->count() }}</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($thisMonth as $person)
                    @php
                        $pName = $person->user->name ?? ($person->first_name . ' ' . $person->last_name);
                        $pInitials = strtoupper(collect(explode(' ', $pName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                        $deptName = $person->department->name ?? 'Unassigned';
                        $joinDate = \Carbon\Carbon::parse($person->joining_date);
                    @endphp
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-white/[0.12] hover:bg-[#1D1D35] transition-all group">
                        <div class="flex flex-col items-center text-center">
                            <div class="relative mb-3">
                                <div class="w-14 h-14 rounded-full bg-amber-500/10 text-amber-400/70 text-lg font-bold flex items-center justify-center group-hover:bg-amber-500/20 transition-colors">
                                    {{ $pInitials }}
                                </div>
                            </div>
                            <h3 class="text-[14px] font-semibold text-white/85">{{ $pName }}</h3>
                            <p class="text-[12px] text-white/40 mt-0.5">{{ $deptName }}</p>
                            <div class="mt-2 flex items-center gap-1.5">
                                <span class="text-[16px] font-bold text-amber-400/70">{{ $person->_years_of_service }}</span>
                                <span class="text-[11px] text-amber-400/50 font-medium">{{ Str::plural('year', $person->_years_of_service) }}</span>
                            </div>
                            <p class="text-[11px] text-white/30 mt-1">{{ $joinDate->format('M d, Y') }}</p>
                            <span class="mt-2 text-[11px] text-white/30 font-medium">
                                in {{ $person->_days_until }} {{ Str::plural('day', $person->_days_until) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if($thisWeek->count() === 0 && $thisMonth->count() === 0)
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-16 text-center">
            <svg class="w-12 h-12 text-white/10 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            <p class="text-[15px] text-white/35 font-medium">No upcoming work anniversaries</p>
            <p class="text-[12px] text-white/20 mt-1">Employee joining dates will appear here once added to profiles</p>
        </div>
    @endif

</div>

</x-layouts.hr>