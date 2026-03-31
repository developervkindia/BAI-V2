@props(['project', 'currentView' => 'list', 'canEdit' => false])

@php
$org = auth()->user()?->currentOrganization();

$navItems = [
    ['view' => 'overview',   'route' => 'projects.overview',   'label' => 'Overview',
     'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ['view' => 'list',       'route' => 'projects.show',       'label' => 'List',
     'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
    ['view' => 'board',      'route' => 'projects.board',      'label' => 'Board',
     'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2'],
    ['view' => 'backlog',    'route' => 'projects.backlog',    'label' => 'Backlog',
     'icon' => 'M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01'],
    ['view' => 'timeline',   'route' => 'projects.timeline',   'label' => 'Timeline',
     'icon' => 'M13 17h8m-8-5h8m-8-5h8M3 17l2-2-2-2m0 4V7m0 10l2-2-2-2'],
    ['view' => 'calendar',   'route' => 'projects.calendar',   'label' => 'Calendar',
     'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['view' => 'milestones', 'route' => 'projects.milestones', 'label' => 'Milestones',
     'icon' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H13.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9'],
];

$statusColors = [
    'not_started' => 'bg-white/10 text-white/40',
    'in_progress'  => 'bg-blue-500/20 text-blue-400',
    'on_hold'      => 'bg-amber-500/20 text-amber-400',
    'completed'    => 'bg-green-500/20 text-green-400',
    'cancelled'    => 'bg-red-500/20 text-red-400',
];
$statusLabels = [
    'not_started' => 'Not Started',
    'in_progress'  => 'In Progress',
    'on_hold'      => 'On Hold',
    'completed'    => 'Completed',
    'cancelled'    => 'Cancelled',
];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-product="projects">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $project->name }} — BAI Projects</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#0F0F18]"
      x-data="{ mobileSidebarOpen: false, userDropdownOpen: false }">

{{-- ============================================================ --}}
{{-- SIDEBAR                                                      --}}
{{-- ============================================================ --}}
<aside class="fixed inset-y-0 left-0 w-[220px] bg-[#0B0B12] border-r border-white/[0.06] flex flex-col z-30 hidden lg:flex">

    {{-- TOP: Org + back --}}
    <div class="flex items-center gap-2.5 px-3 h-14 border-b border-white/[0.06] shrink-0">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[13px] font-bold shrink-0 prod-bg-muted prod-text">
            {{ strtoupper(substr($org?->name ?? 'S', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[12px] font-semibold text-white/70 truncate leading-tight">{{ $org?->name ?? 'BAI' }}</p>
            <a href="{{ route('projects.index') }}" class="text-[10px] text-white/30 hover:text-white/55 transition-colors flex items-center gap-0.5">
                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                BAI Projects
            </a>
        </div>
    </div>

    {{-- PROJECT IDENTITY --}}
    <div class="px-3 py-3 border-b border-white/[0.06] shrink-0">
        <div class="flex items-start gap-2.5">
            <div class="w-3 h-3 rounded-full shrink-0 mt-1 ring-2 ring-white/10" style="background: {{ $project->color ?? '#F97316' }};"></div>
            <div class="flex-1 min-w-0">
                <p class="text-[13px] font-semibold text-white/85 leading-snug">{{ $project->name }}</p>
                <span class="inline-flex mt-1 text-[9px] px-1.5 py-0.5 rounded-full font-medium {{ $statusColors[$project->status] ?? 'bg-white/10 text-white/40' }}">
                    {{ $statusLabels[$project->status] ?? $project->status }}
                </span>
            </div>
        </div>
        @if($project->end_date)
            <p class="mt-2 text-[10px] text-white/28 pl-5.5" style="padding-left:22px">
                Due {{ $project->end_date->format('M j, Y') }}
            </p>
        @endif
    </div>

    {{-- PROJECT VIEWS NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto scrollbar-thin p-2.5 space-y-0.5">
        <div class="pb-1 px-2.5">
            <span class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Views</span>
        </div>
        @foreach($navItems as $item)
            <a href="{{ route($item['route'], $project) }}"
               class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium text-white/48 hover:text-white/82 hover:bg-white/[0.05] transition-colors {{ $currentView === $item['view'] ? 'nav-active' : '' }}">
                <svg class="w-4 h-4 shrink-0 text-white/32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach

        {{-- Members --}}
        @if($project->members && $project->members->count() > 0)
            <div class="pt-4 pb-1 px-2.5">
                <span class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Members</span>
            </div>
            <div class="px-2.5 py-1.5 flex flex-wrap gap-1.5">
                @foreach($project->members->take(8) as $member)
                    <div class="w-7 h-7 rounded-full bg-white/10 text-white/55 text-[9px] font-bold flex items-center justify-center ring-1 ring-black/50 cursor-default" title="{{ $member->name }}">
                        {{ strtoupper(substr($member->name, 0, 2)) }}
                    </div>
                @endforeach
                @if($project->members->count() > 8)
                    <div class="w-7 h-7 rounded-full bg-white/[0.06] text-white/30 text-[9px] flex items-center justify-center ring-1 ring-black/50">
                        +{{ $project->members->count() - 8 }}
                    </div>
                @endif
            </div>
        @endif
    </nav>

    {{-- BOTTOM: Settings + User --}}
    <div class="border-t border-white/[0.06] p-2.5 space-y-1 shrink-0">
        @if($canEdit)
            <button x-data @click="$dispatch('open-modal', 'project-settings')"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg text-[12px] font-medium text-white/42 hover:text-white/72 hover:bg-white/[0.05] transition-colors">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Project Settings
            </button>
        @endif

        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg hover:bg-white/[0.05] transition-colors">
                <div class="w-7 h-7 rounded-full prod-bg-muted prod-text text-[10px] font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <p class="text-[12px] font-medium text-white/65 truncate flex-1 text-left">{{ auth()->user()->name ?? 'User' }}</p>
            </button>
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="absolute bottom-full left-0 right-0 mb-1 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl overflow-hidden z-50">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                <a href="{{ route('hub') }}" class="flex items-center gap-2 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    BAI Home
                </a>
                <div class="border-t border-white/[0.06]">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 w-full px-3.5 py-2.5 text-[12px] text-red-400/80 hover:bg-red-500/10 hover:text-red-400 transition-colors text-left">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>

{{-- ============================================================ --}}
{{-- MOBILE OVERLAY                                               --}}
{{-- ============================================================ --}}
<div x-show="mobileSidebarOpen" x-cloak x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="mobileSidebarOpen = false"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"></div>

<aside x-show="mobileSidebarOpen" x-cloak
       x-transition:enter="transition ease-out duration-250"
       x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
       class="fixed inset-y-0 left-0 w-[280px] bg-[#0B0B12] border-r border-white/[0.06] flex flex-col z-50 lg:hidden">
    <div class="flex items-center justify-between px-4 h-14 border-b border-white/[0.06]">
        <div class="flex items-center gap-2">
            <div class="w-3 h-3 rounded-full ring-2 ring-white/10" style="background: {{ $project->color ?? '#F97316' }};"></div>
            <span class="text-[14px] font-semibold text-white/80 truncate">{{ $project->name }}</span>
        </div>
        <button @click="mobileSidebarOpen = false" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/40">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
        <a href="{{ route('projects.index') }}" class="flex items-center gap-2.5 px-2.5 py-2.5 rounded-lg text-[12px] text-white/40 hover:text-white/65 hover:bg-white/[0.04] transition-colors mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            All Projects
        </a>
        @foreach($navItems as $item)
            <a href="{{ route($item['route'], $project) }}"
               class="flex items-center gap-2.5 px-2.5 py-2.5 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/85 hover:bg-white/[0.05] transition-colors {{ $currentView === $item['view'] ? 'nav-active' : '' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</aside>

{{-- ============================================================ --}}
{{-- MAIN CONTENT                                                  --}}
{{-- ============================================================ --}}
<div class="lg:pl-[220px] min-h-full flex flex-col">

    {{-- TOP BAR --}}
    <header class="sticky top-0 z-20 h-14 bg-[#0D0D16]/95 backdrop-blur-md border-b border-white/[0.06] flex items-center gap-3 px-4 lg:px-5 shrink-0">

        {{-- Mobile menu --}}
        <button @click="mobileSidebarOpen = true" class="lg:hidden p-1.5 rounded-md hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-1.5 text-[12px] min-w-0 flex-1">
            <a href="{{ route('projects.index') }}" class="text-white/30 hover:text-white/60 transition-colors shrink-0">Projects</a>
            <span class="text-white/15 shrink-0">/</span>
            <span class="text-white/65 font-medium truncate">{{ $project->name }}</span>
            <span class="text-white/15 shrink-0 hidden sm:block">/</span>
            <span class="text-white/35 capitalize hidden sm:block shrink-0">{{ $currentView }}</span>
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-1.5 shrink-0">
            {{-- Progress pill --}}
            @php
                $total = $project->tasks_count ?? 0;
                $done  = $project->completed_tasks_count ?? 0;
            @endphp
            @if($total > 0)
                <div class="hidden sm:flex items-center gap-2 px-3 py-1 bg-white/[0.05] rounded-full">
                    <div class="w-16 h-1.5 bg-white/10 rounded-full overflow-hidden">
                        <div class="h-full prod-bg rounded-full" style="width: {{ $total > 0 ? round($done/$total*100) : 0 }}%"></div>
                    </div>
                    <span class="text-[11px] text-white/35">{{ $total > 0 ? round($done/$total*100) : 0 }}%</span>
                </div>
            @endif

            {{-- Add task button --}}
            <button x-data @click="$dispatch('open-create-task')"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-semibold text-white/90 prod-btn transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">New Issue</span>
            </button>

            {{-- User menu --}}
            <x-ui.dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="w-8 h-8 rounded-full prod-bg-muted prod-text text-[10px] font-bold flex items-center justify-center hover:opacity-80 transition-opacity">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </button>
                </x-slot>
                <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white/80">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-white/40">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('hub') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5">BAI Home</a>
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-white/5">Sign Out</button>
                </form>
            </x-ui.dropdown>
        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

</div>

<x-ui.toast />
</body>
</html>
