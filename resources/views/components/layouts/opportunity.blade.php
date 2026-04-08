@props(['title' => 'Opportunity', 'project' => null, 'currentView' => 'home'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $project ? $project->name . ' — ' : '' }}{{ $title }} — Opportunity</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { scrollbar-width: none; }
    </style>
</head>
<body class="h-full antialiased font-sans bg-[#1A1A2E]" x-data="{ mobileSidebarOpen: false }">
<x-impersonation-banner />

{{-- ================================================================ --}}
{{-- SIDEBAR (Asana-style: dark, clean, grouped sections)             --}}
{{-- ================================================================ --}}
<aside class="fixed inset-y-0 left-0 w-[240px] bg-[#111122] flex flex-col z-30 border-r border-white/[0.06] hidden lg:flex">

    {{-- Product header — Opportunity --}}
    <div class="shrink-0 border-b border-white/[0.06]">
        <a href="{{ route('hub') }}" class="block px-3 pt-3 pb-1">
            <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="w-full h-auto">
        </a>
        <div class="px-3 pb-2.5">
            <span class="text-[10px] font-semibold text-teal-400/80 tracking-wider uppercase">Opportunity &middot; Tasks & Goals</span>
        </div>
    </div>

    {{-- Create button --}}
    <div class="px-3 pt-3 pb-2">
        <button onclick="window.dispatchEvent(new CustomEvent('opp-create-task'))"
                class="flex items-center gap-2 w-full px-3 py-2 rounded-lg bg-gradient-to-r from-teal-500 to-teal-400 text-white text-[13px] font-semibold hover:from-teal-400 hover:to-teal-300 transition-all shadow-lg shadow-teal-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Create
        </button>
    </div>

    {{-- Main nav --}}
    <nav class="flex-1 overflow-y-auto px-2 space-y-0.5 scrollbar-none">

        @php
            $navItems = [
                ['route' => 'opportunity.home',     'view' => 'home',     'label' => 'Home',     'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'opportunity.my-tasks',  'view' => 'my-tasks', 'label' => 'My tasks', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['route' => 'opportunity.inbox',     'view' => 'inbox',   'label' => 'Inbox',    'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4'],
            ];
        @endphp

        @foreach($navItems as $nav)
            <a href="{{ route($nav['route']) }}"
               class="flex items-center gap-2.5 px-3 py-[7px] rounded-lg text-[13px] font-medium transition-colors {{ $currentView === $nav['view'] ? 'bg-white/[0.08] text-white' : 'text-white/55 hover:bg-white/[0.04] hover:text-white/80' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $nav['icon'] }}"/></svg>
                {{ $nav['label'] }}
            </a>
        @endforeach

        {{-- Insights section --}}
        <div class="pt-4 pb-1 px-3 flex items-center justify-between">
            <span class="text-[11px] font-semibold text-white/25 uppercase tracking-wider">Insights</span>
            <span class="text-[11px] text-white/15">+</span>
        </div>
        <a href="{{ route('opportunity.reporting.index') }}"
           class="flex items-center gap-2.5 px-3 py-[7px] rounded-lg text-[13px] font-medium transition-colors {{ $currentView === 'reporting' ? 'bg-white/[0.08] text-white' : 'text-white/45 hover:bg-white/[0.04] hover:text-white/70' }}">
            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Reporting
        </a>
        <a href="{{ route('opportunity.portfolios.index') }}"
           class="flex items-center gap-2.5 px-3 py-[7px] rounded-lg text-[13px] font-medium transition-colors {{ $currentView === 'portfolios' ? 'bg-white/[0.08] text-white' : 'text-white/45 hover:bg-white/[0.04] hover:text-white/70' }}">
            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Portfolios
        </a>
        <a href="{{ route('opportunity.goals.index') }}"
           class="flex items-center gap-2.5 px-3 py-[7px] rounded-lg text-[13px] font-medium transition-colors {{ $currentView === 'goals' ? 'bg-white/[0.08] text-white' : 'text-white/45 hover:bg-white/[0.04] hover:text-white/70' }}">
            <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            Goals
        </a>

        {{-- Starred section --}}
        @if(isset($oppSidebarProjects) && $oppSidebarProjects->count() > 0)
        <div class="pt-4 pb-1 px-3 flex items-center justify-between">
            <span class="text-[11px] font-semibold text-white/25 uppercase tracking-wider">Starred</span>
        </div>
        @endif

        {{-- Projects section --}}
        <div class="pt-3 pb-1 px-3 flex items-center justify-between">
            <span class="text-[11px] font-semibold text-white/25 uppercase tracking-wider">Projects</span>
            <a href="{{ route('opportunity.projects.index') }}" class="w-4 h-4 flex items-center justify-center text-white/20 hover:text-teal-400 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </a>
        </div>

        @if(isset($oppSidebarProjects))
            @foreach($oppSidebarProjects as $sp)
                @php $isActive = $project && $project->id === $sp->id; @endphp
                <a href="{{ route('opportunity.projects.show', $sp) }}"
                   class="flex items-center gap-2.5 px-3 py-[6px] rounded-lg text-[13px] font-medium transition-colors {{ $isActive ? 'bg-white/[0.08] text-white' : 'text-white/50 hover:bg-white/[0.04] hover:text-white/75' }}">
                    <span class="w-[18px] h-[18px] rounded flex items-center justify-center shrink-0" style="background: {{ $sp->color ?? '#14B8A6' }}22">
                        <svg class="w-3 h-3" style="color: {{ $sp->color ?? '#14B8A6' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </span>
                    <span class="truncate">{{ $sp->name }}</span>
                </a>
            @endforeach
        @endif

    </nav>

    {{-- Footer --}}
    <div class="border-t border-white/[0.06] p-2 space-y-0.5 shrink-0">
        <x-product-switcher :currentProduct="'opportunity'" />
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open" class="flex items-center gap-2.5 w-full px-3 py-[7px] rounded-lg text-[12px] font-medium text-white/35 hover:text-white/60 hover:bg-white/[0.04] transition-colors">
                <div class="w-5 h-5 rounded-full bg-teal-500/25 text-teal-400 text-[9px] font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <span class="truncate">{{ auth()->user()->name ?? 'User' }}</span>
            </button>
            <div x-show="open" x-cloak class="absolute bottom-full left-0 right-0 mb-1 bg-[#1A1A2E] border border-white/[0.08] rounded-lg shadow-xl overflow-hidden">
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

{{-- ================================================================ --}}
{{-- MAIN CONTENT                                                      --}}
{{-- ================================================================ --}}
<div class="lg:pl-[240px] min-h-full flex flex-col">

    {{-- Top bar --}}
    <header class="sticky top-0 z-20 h-12 bg-[#1A1A2E]/95 backdrop-blur-md border-b border-white/[0.06] flex items-center gap-3 px-4 lg:px-5 shrink-0">
        {{-- Mobile hamburger --}}
        <button @click="mobileSidebarOpen = true" class="lg:hidden p-1.5 rounded-md hover:bg-white/[0.07] text-white/35">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Search bar (Asana-style centered) --}}
        <div class="flex-1 flex justify-center">
            <div class="relative w-full max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" placeholder="Search" class="w-full pl-9 pr-16 py-[6px] rounded-lg bg-white/[0.06] border border-white/[0.08] text-[13px] text-white/70 placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-teal-500/40 focus:bg-white/[0.08]"/>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 text-[10px] text-white/20">
                    <kbd class="px-1 py-0.5 rounded bg-white/[0.08] font-mono">Ctrl</kbd>
                    <kbd class="px-1 py-0.5 rounded bg-white/[0.08] font-mono">K</kbd>
                </div>
            </div>
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-1.5 shrink-0" x-data="{ open: false }" @click.away="open = false">
            <div class="relative">
                <button @click="open = !open" class="w-7 h-7 rounded-full bg-teal-500/20 text-teal-400 text-[10px] font-bold flex items-center justify-center hover:bg-teal-500/30 transition-colors">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </button>
                <div x-show="open" x-cloak class="absolute right-0 top-full mt-2 w-48 bg-[#1A1A2E] border border-white/[0.08] rounded-lg shadow-xl overflow-hidden z-50">
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

    {{-- ============================================================ --}}
    {{-- PROJECT HEADER (Asana-style, only when inside a project)     --}}
    {{-- ============================================================ --}}
    @if($project)
    <div class="sticky top-12 z-10 bg-[#1A1A2E] border-b border-white/[0.06] shrink-0">
        {{-- Project name + status --}}
        <div class="flex items-center gap-3 px-5 pt-3 pb-2">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background: {{ $project->color ?? '#14B8A6' }}22">
                <svg class="w-4 h-4" style="color: {{ $project->color ?? '#14B8A6' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            </div>
            <h1 class="text-[15px] font-semibold text-white/90 truncate">{{ $project->name }}</h1>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0
                {{ $project->status === 'on_track' ? 'bg-green-500/15 text-green-400' :
                   ($project->status === 'at_risk' ? 'bg-amber-500/15 text-amber-400' :
                   ($project->status === 'off_track' ? 'bg-red-500/15 text-red-400' : 'bg-white/[0.07] text-white/40')) }}">
                {{ str_replace('_', ' ', ucfirst($project->status)) }}
            </span>
            <div class="flex-1"></div>
            <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium border border-white/[0.1] text-white/50 hover:text-white/70 hover:bg-white/[0.04] transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                Share
            </button>
            <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium border border-white/[0.1] text-white/50 hover:text-white/70 hover:bg-white/[0.04] transition-colors">
                Customize
            </button>
        </div>

        {{-- View tabs (Asana-style: List | Board | Timeline | Calendar | ...) --}}
        <div class="flex items-center px-5 gap-0">
            @foreach([
                ['route' => 'opportunity.projects.overview',  'view' => 'overview',  'label' => 'Overview',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['route' => 'opportunity.projects.show',      'view' => 'list',      'label' => 'List',      'icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                ['route' => 'opportunity.projects.board',     'view' => 'board',     'label' => 'Board',     'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7'],
                ['route' => 'opportunity.projects.timeline',  'view' => 'timeline',  'label' => 'Timeline',  'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['route' => 'opportunity.projects.calendar',  'view' => 'calendar',  'label' => 'Calendar',  'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ] as $tab)
                <a href="{{ route($tab['route'], $project) }}"
                   class="flex items-center gap-1.5 px-3 py-2.5 text-[13px] font-medium border-b-2 whitespace-nowrap transition-colors {{ $currentView === $tab['view'] ? 'border-teal-500 text-white/90' : 'border-transparent text-white/40 hover:text-white/65' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $tab['icon'] }}"/></svg>
                    {{ $tab['label'] }}
                </a>
            @endforeach
            {{-- + add view --}}
            <button class="px-2 py-2.5 text-white/20 hover:text-white/50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </button>
        </div>
    </div>
    @endif

    {{-- PAGE CONTENT --}}
    <main class="flex-1 overflow-auto">
        {{ $slot }}
    </main>
</div>

<x-ui.confirm-modal />
</body>
</html>
