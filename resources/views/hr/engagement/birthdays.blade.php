<x-layouts.hr title="Birthdays" currentView="engagement">

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
                        <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.75 1.75 0 003 15.546M12 4v3m-2-1h4m-6 4h8l1 3H5l1-3z"/></svg>
                        Birthdays
                    </h1>
                    <p class="text-[13px] text-white/40 mt-0.5">Celebrate your teammates</p>
                </div>
            </div>
        </div>
    </div>

    @php
        $now = now();
        $thisWeek = collect();
        $thisMonth = collect();
        $nextMonth = collect();

        foreach ($upcomingBirthdays as $person) {
            $dob = \Carbon\Carbon::parse($person->date_of_birth);
            $nextBd = $dob->copy()->year($now->year);
            if ($nextBd->isPast() && !$nextBd->isToday()) {
                $nextBd->addYear();
            }
            $daysUntil = $now->startOfDay()->diffInDays($nextBd->startOfDay(), false);
            $person->_next_birthday = $nextBd;
            $person->_days_until = $daysUntil;

            if ($daysUntil >= 0 && $daysUntil <= 7) {
                $thisWeek->push($person);
            } elseif ($nextBd->month === $now->month && $nextBd->year === $now->year) {
                $thisMonth->push($person);
            } elseif (($nextBd->month === $now->copy()->addMonth()->month && $nextBd->year === $now->copy()->addMonth()->year) || ($daysUntil > 7 && $daysUntil <= 60)) {
                $nextMonth->push($person);
            }
        }

        $thisWeek = $thisWeek->sortBy('_days_until');
        $thisMonth = $thisMonth->sortBy('_days_until');
        $nextMonth = $nextMonth->sortBy('_days_until');
    @endphp

    {{-- This Week --}}
    @if($thisWeek->count() > 0)
        <div class="space-y-3">
            <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-pink-400 animate-pulse"></span>
                This Week
                <span class="text-pink-400/70 bg-pink-500/10 px-2 py-0.5 rounded-full text-[10px] ml-1">{{ $thisWeek->count() }}</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($thisWeek as $person)
                    @php
                        $pName = $person->user->name ?? ($person->first_name . ' ' . $person->last_name);
                        $pInitials = strtoupper(collect(explode(' ', $pName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                        $deptName = $person->department->name ?? 'Unassigned';
                        $bdDate = \Carbon\Carbon::parse($person->date_of_birth);
                    @endphp
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-pink-500/20 hover:bg-[#1D1D35] transition-all group">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-14 h-14 rounded-full bg-pink-500/15 text-pink-400 text-lg font-bold flex items-center justify-center mb-3 group-hover:bg-pink-500/25 transition-colors">
                                {{ $pInitials }}
                            </div>
                            <h3 class="text-[14px] font-semibold text-white/85">{{ $pName }}</h3>
                            <p class="text-[12px] text-white/40 mt-0.5">{{ $deptName }}</p>
                            <p class="text-[12px] text-pink-400/70 mt-2 font-medium">{{ $bdDate->format('M d') }}</p>
                            @if($person->_days_until === 0)
                                <span class="mt-2 text-[10px] font-bold text-pink-400 bg-pink-500/15 px-2.5 py-1 rounded-full">
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
                        $bdDate = \Carbon\Carbon::parse($person->date_of_birth);
                    @endphp
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-white/[0.12] hover:bg-[#1D1D35] transition-all group">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-14 h-14 rounded-full bg-pink-500/10 text-pink-400/70 text-lg font-bold flex items-center justify-center mb-3 group-hover:bg-pink-500/20 transition-colors">
                                {{ $pInitials }}
                            </div>
                            <h3 class="text-[14px] font-semibold text-white/85">{{ $pName }}</h3>
                            <p class="text-[12px] text-white/40 mt-0.5">{{ $deptName }}</p>
                            <p class="text-[12px] text-pink-400/50 mt-2 font-medium">{{ $bdDate->format('M d') }}</p>
                            <span class="mt-2 text-[11px] text-white/30 font-medium">
                                in {{ $person->_days_until }} {{ Str::plural('day', $person->_days_until) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Next Month --}}
    @if($nextMonth->count() > 0)
        <div class="space-y-3">
            <h2 class="text-[12px] font-semibold text-white/30 uppercase tracking-widest flex items-center gap-2">
                Next Month
                <span class="text-white/25 bg-white/[0.06] px-2 py-0.5 rounded-full text-[10px] ml-1">{{ $nextMonth->count() }}</span>
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($nextMonth as $person)
                    @php
                        $pName = $person->user->name ?? ($person->first_name . ' ' . $person->last_name);
                        $pInitials = strtoupper(collect(explode(' ', $pName))->map(fn($w) => substr($w, 0, 1))->take(2)->join(''));
                        $deptName = $person->department->name ?? 'Unassigned';
                        $bdDate = \Carbon\Carbon::parse($person->date_of_birth);
                    @endphp
                    <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-white/[0.12] hover:bg-[#1D1D35] transition-all group">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-14 h-14 rounded-full bg-white/[0.06] text-white/35 text-lg font-bold flex items-center justify-center mb-3 group-hover:bg-white/[0.10] transition-colors">
                                {{ $pInitials }}
                            </div>
                            <h3 class="text-[14px] font-semibold text-white/85">{{ $pName }}</h3>
                            <p class="text-[12px] text-white/40 mt-0.5">{{ $deptName }}</p>
                            <p class="text-[12px] text-white/35 mt-2 font-medium">{{ $bdDate->format('M d') }}</p>
                            <span class="mt-2 text-[11px] text-white/25 font-medium">
                                in {{ $person->_days_until }} {{ Str::plural('day', $person->_days_until) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if($thisWeek->count() === 0 && $thisMonth->count() === 0 && $nextMonth->count() === 0)
        <div class="bg-[#17172A] border border-white/[0.07] rounded-xl p-16 text-center">
            <svg class="w-12 h-12 text-white/10 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0A1.75 1.75 0 003 15.546M12 4v3m-2-1h4m-6 4h8l1 3H5l1-3z"/></svg>
            <p class="text-[15px] text-white/35 font-medium">No upcoming birthdays</p>
            <p class="text-[12px] text-white/20 mt-1">Employee birth dates will appear here once added to profiles</p>
        </div>
    @endif

</div>

</x-layouts.hr>