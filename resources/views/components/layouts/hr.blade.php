@props(['title' => 'BAI HR', 'currentView' => 'dashboard'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-product="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — BAI HR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { scrollbar-width: none; }
    </style>
</head>
<body class="h-full antialiased font-sans bg-[#0F0F18]" x-data="{ mobileSidebarOpen: false }">

{{-- SIDEBAR --}}
<aside class="fixed inset-y-0 left-0 w-[220px] bg-[#0B0B12] flex flex-col z-30 border-r border-white/[0.06] hidden lg:flex">

    {{-- Logo --}}
    <div class="px-4 pt-4 pb-3 flex items-center gap-2.5 border-b border-white/[0.06]">
        <div class="w-7 h-7 rounded-lg bg-cyan-500/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <span class="text-[14px] font-bold text-white/90 tracking-tight">BAI HR</span>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-2 pt-3 space-y-0.5 scrollbar-none">

        @php
            $navSections = [
                'main' => [
                    ['route' => 'hr.dashboard',       'view' => 'dashboard',    'label' => 'Dashboard',      'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm0 8a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zm12 0a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                ],
                'People' => [
                    ['route' => 'hr.people.index',    'view' => 'people',       'label' => 'Directory',      'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'],
                    ['route' => 'hr.people.org-chart','view' => 'org-chart',    'label' => 'Org Chart',      'icon' => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM9 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z'],
                    ['route' => 'hr.departments.index','view' => 'departments', 'label' => 'Departments',    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ],
                'Attendance' => [
                    ['route' => 'hr.attendance.my',    'view' => 'my-attendance','label' => 'My Attendance',  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'hr.attendance.index',  'view' => 'attendance',   'label' => 'All Attendance', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['route' => 'hr.attendance.team',   'view' => 'team-attendance','label' => 'Team View',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['route' => 'hr.attendance.reports','view' => 'attendance-reports','label' => 'Reports',  'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ],
                'Leave' => [
                    ['route' => 'hr.leave.index',      'view' => 'leave',        'label' => 'Overview',       'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['route' => 'hr.leave.apply',      'view' => 'leave-apply',  'label' => 'Apply Leave',    'icon' => 'M12 4v16m8-8H4'],
                    ['route' => 'hr.leave.my',         'view' => 'my-leaves',    'label' => 'My Leaves',      'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    ['route' => 'hr.leave.calendar',   'view' => 'leave-calendar','label' => 'Calendar',     'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['route' => 'hr.leave.approvals',  'view' => 'leave-approvals','label' => 'Approvals',   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ],
                'Payroll' => [
                    ['route' => 'hr.payroll.index',    'view' => 'payroll',      'label' => 'Dashboard',      'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'hr.payroll.run',      'view' => 'payroll-run',  'label' => 'Run Payroll',    'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z'],
                    ['route' => 'hr.payroll.my-payslips','view' => 'my-payslips','label' => 'My Payslips',   'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route' => 'hr.payroll.salary-structures','view' => 'salary-structures','label' => 'Salary Setup', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ],
                'Performance' => [
                    ['route' => 'hr.performance.index',   'view' => 'performance',    'label' => 'Overview',    'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                    ['route' => 'hr.performance.cycles',  'view' => 'perf-cycles',    'label' => 'Cycles',      'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                    ['route' => 'hr.performance.my-review','view' => 'my-review',     'label' => 'My Review',   'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                ],
                'More' => [
                    ['route' => 'hr.expenses.index',      'view' => 'expenses',       'label' => 'Expenses',     'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['route' => 'hr.recruitment.index',   'view' => 'recruitment',    'label' => 'Recruitment',  'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['route' => 'hr.engagement.index',    'view' => 'engagement',     'label' => 'Engagement',   'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                    ['route' => 'hr.surveys.index',       'view' => 'surveys',        'label' => 'Surveys',      'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01'],
                    ['route' => 'hr.announcements.index', 'view' => 'announcements',  'label' => 'Announcements','icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
                ],
            ];
        @endphp

        @foreach($navSections as $section => $items)
            @if($section !== 'main')
                <div class="pt-4 pb-1 px-3">
                    <span class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">{{ $section }}</span>
                </div>
            @endif

            @foreach($items as $nav)
                <a href="{{ route($nav['route']) }}"
                   class="flex items-center gap-2.5 px-3 py-[7px] rounded-lg text-[13px] font-medium transition-colors {{ $currentView === $nav['view'] ? 'nav-active' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/75' }}">
                    <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $nav['icon'] }}"/></svg>
                    {{ $nav['label'] }}
                </a>
            @endforeach
        @endforeach

    </nav>

    {{-- Footer --}}
    <div class="border-t border-white/[0.06] p-2 space-y-0.5 shrink-0">
        <x-product-switcher :currentProduct="'hr'" />
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open" class="flex items-center gap-2.5 w-full px-3 py-[7px] rounded-lg text-[12px] font-medium text-white/35 hover:text-white/60 hover:bg-white/[0.04] transition-colors">
                <div class="w-5 h-5 rounded-full prod-bg-muted prod-text text-[9px] font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <span class="truncate">{{ auth()->user()->name ?? 'User' }}</span>
            </button>
            <div x-show="open" x-cloak class="absolute bottom-full left-0 right-0 mb-1 bg-[#0F0F18] border border-white/[0.08] rounded-lg shadow-xl overflow-hidden">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/60 hover:bg-white/[0.06] hover:text-white/80 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                <div class="border-t border-white/[0.06]">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2.5 w-full px-3.5 py-2.5 text-[12px] text-red-400/80 hover:bg-red-500/10 hover:text-red-400 transition-colors text-left">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>

{{-- MAIN CONTENT --}}
<div class="lg:pl-[220px] min-h-full flex flex-col">

    {{-- Top bar --}}
    <header class="sticky top-0 z-20 h-14 bg-[#0D0D16]/95 backdrop-blur-md border-b border-white/[0.06] flex items-center gap-3 px-4 lg:px-5 shrink-0">
        <button @click="mobileSidebarOpen = true" class="lg:hidden p-1.5 rounded-md hover:bg-white/[0.07] text-white/35">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="flex-1 flex justify-center">
            <div class="relative w-full max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search employees, departments..." class="w-full pl-9 pr-4 py-[6px] rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08]"/>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button class="p-2 rounded-lg hover:bg-white/[0.06] text-white/40 hover:text-white/70 transition-colors relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </button>
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open" class="w-8 h-8 rounded-full prod-bg-muted prod-text text-[11px] font-bold flex items-center justify-center hover:opacity-80 transition-opacity">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </button>
                <div x-show="open" x-cloak class="absolute right-0 top-full mt-2 w-48 bg-[#0F0F18] border border-white/[0.08] rounded-lg shadow-xl overflow-hidden z-50">
                    <div class="px-3.5 py-2.5 border-b border-white/[0.06]">
                        <p class="text-[12px] text-white/80 font-medium truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-[11px] text-white/30 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/60 hover:bg-white/[0.06] hover:text-white/80 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    <div class="border-t border-white/[0.06]">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-2.5 w-full px-3.5 py-2.5 text-[12px] text-red-400/80 hover:bg-red-500/10 hover:text-red-400 transition-colors text-left">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="flex-1 overflow-auto">
        {{ $slot }}
    </main>
</div>

{{-- Mobile sidebar overlay --}}
<div x-show="mobileSidebarOpen" x-cloak @click="mobileSidebarOpen = false" class="fixed inset-0 bg-black/60 z-40 lg:hidden"></div>

<x-ui.confirm-modal />
</body>
</html>
