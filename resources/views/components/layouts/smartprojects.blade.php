@props(['title' => 'BAI Projects', 'project' => null, 'currentView' => 'list', 'canEdit' => false, 'lightBg' => false])

@php
$statusConfig = [
    'not_started' => ['label' => 'Not Started', 'class' => 'bg-white/[0.07] text-white/45'],
    'in_progress'  => ['label' => 'In Progress', 'class' => 'bg-orange-500/15 text-orange-400'],
    'on_hold'      => ['label' => 'On Hold',     'class' => 'bg-amber-500/15 text-amber-400'],
    'completed'    => ['label' => 'Completed',   'class' => 'bg-green-500/15 text-green-400'],
    'cancelled'    => ['label' => 'Cancelled',   'class' => 'bg-red-500/15 text-red-400'],
];
$sc = $project ? ($statusConfig[$project->status] ?? ['label' => $project->status, 'class' => 'bg-white/[0.07] text-white/45']) : null;

// Project progress
$projectProgress = 0;
$projectTotal    = 0;
$projectDone     = 0;
if ($project) {
    $projectTotal    = $project->tasks()->whereNull('parent_task_id')->whereNull('deleted_at')->count();
    $projectDone     = $project->tasks()->whereNull('parent_task_id')->whereNull('deleted_at')->where('is_completed', true)->count();
    $projectProgress = $projectTotal > 0 ? round($projectDone / $projectTotal * 100) : 0;
}
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $project ? $project->name . ' — ' : '' }}{{ $title }} — BAI Projects</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            var t = localStorage.getItem('sp-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="h-full antialiased font-sans bg-[#0D0D18]"
      x-data="{
          mobileSidebarOpen: false,
          showCreateProject: false,
          theme: localStorage.getItem('sp-theme') || 'dark',
          toggleTheme() {
              this.theme = this.theme === 'dark' ? 'light' : 'dark';
              localStorage.setItem('sp-theme', this.theme);
              document.documentElement.setAttribute('data-theme', this.theme);
          }
      }">

{{-- ================================================================ --}}
{{-- SIDEBAR                                                           --}}
{{-- ================================================================ --}}
<aside class="fixed inset-y-0 left-0 w-[220px] bg-[#0B0B12] flex flex-col z-30 border-r border-white/[0.06] hidden lg:flex">

    {{-- Product header — BAI Projects --}}
    <div class="relative bg-gradient-to-br from-[#7c2d12] via-[#c2410c] to-[#ea580c] px-4 py-3.5 flex items-center gap-2.5 shrink-0 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-white/[0.14] to-transparent pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-white/0 via-white/20 to-white/0"></div>
        <div class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 flex items-center justify-center shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
        </div>
        <div>
            <span class="text-[13px] font-bold text-white leading-none tracking-tight">BAI Projects</span>
            <span class="block text-[9px] text-white/55 font-medium tracking-wider uppercase leading-none mt-0.5">Project Management</span>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto p-2.5 space-y-0.5">

        {{-- All Projects --}}
        <a href="{{ route('projects.index') }}"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ !$project && request()->routeIs('projects.*') ? 'bg-orange-500/15 text-orange-300' : 'text-white/50 hover:text-white/80 hover:bg-white/[0.05]' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            All Projects
        </a>

        {{-- Clients --}}
        <a href="{{ route('clients.index') }}"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ request()->routeIs('clients.*') ? 'bg-orange-500/15 text-orange-300' : 'text-white/50 hover:text-white/80 hover:bg-white/[0.05]' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Clients
        </a>

        {{-- Project list --}}
        @if(isset($sidebarProjects) && $sidebarProjects->count() > 0)
            <div class="pt-3 pb-1 px-2.5 flex items-center justify-between">
                <span class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">My Projects</span>
                <button @click="showCreateProject = true"
                        class="w-4 h-4 rounded flex items-center justify-center text-white/25 hover:text-orange-400 hover:bg-orange-500/10 transition-colors"
                        title="New Project">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>

            @foreach($sidebarProjects as $sp)
                @php $isActive = $project && $project->id === $sp->id; @endphp
                <a href="{{ route('projects.overview', $sp) }}"
                   class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-[12px] font-medium transition-colors {{ $isActive ? 'bg-orange-500/10 text-white/85 border-l-2 border-orange-500' : 'text-white/48 hover:text-white/78 hover:bg-white/[0.04]' }}">
                    <span class="w-2 h-2 rounded-full shrink-0 ring-1 ring-black/20"
                          style="background: {{ $sp->color ?? '#F97316' }};"></span>
                    <span class="truncate flex-1">{{ $sp->name }}</span>
                </a>
            @endforeach

        @else
            <div class="px-3 py-4 text-center">
                <p class="text-[11px] text-white/25 mb-2">No projects yet</p>
                <button @click="showCreateProject = true" class="text-[11px] text-orange-400/70 hover:text-orange-400 transition-colors">
                    + Create one
                </button>
            </div>
        @endif

    </nav>

    {{-- Admin links --}}
    @if(isset($currentOrganization) && $currentOrganization->isAdmin(auth()->user()))
    <div class="border-t border-white/[0.06] px-2.5 pt-2 pb-0.5 space-y-0.5">
        <div class="px-2.5 pb-1 pt-1">
            <span class="text-[10px] font-semibold text-white/18 uppercase tracking-widest">Admin</span>
        </div>
        <a href="{{ route('users.index', $currentOrganization) }}"
           class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-[12px] font-medium text-white/35 hover:text-white/65 hover:bg-white/[0.04] transition-colors">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Users
        </a>
        <a href="{{ route('roles.index', $currentOrganization) }}"
           class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-[12px] font-medium text-white/35 hover:text-white/65 hover:bg-white/[0.04] transition-colors">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Roles
        </a>
    </div>
    @endif

    {{-- Footer: Product Switcher + Hub + User --}}
    <div class="border-t border-white/[0.06] p-2.5 space-y-0.5 shrink-0">
        <x-product-switcher :currentProduct="'projects'" />
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg hover:bg-white/[0.05] transition-colors">
                <div class="w-7 h-7 rounded-full bg-orange-500/20 text-orange-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <p class="text-[12px] font-medium text-white/65 truncate flex-1 text-left">{{ auth()->user()->name ?? 'User' }}</p>
                <svg class="w-3 h-3 text-white/25 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute bottom-full left-0 right-0 mb-1 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl overflow-hidden z-50">
                <div class="px-3.5 py-2.5 border-b border-white/[0.06]">
                    <p class="text-[13px] font-semibold text-white/80">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-white/35">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
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

{{-- ================================================================ --}}
{{-- MOBILE SIDEBAR                                                    --}}
{{-- ================================================================ --}}
<div x-show="mobileSidebarOpen" x-cloak @click="mobileSidebarOpen = false"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"></div>

<aside x-show="mobileSidebarOpen" x-cloak
       x-transition:enter="transition ease-out duration-250" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
       class="fixed inset-y-0 left-0 w-[280px] bg-[#0B0B12] border-r border-white/[0.06] flex flex-col z-50 lg:hidden">
    <div class="relative bg-gradient-to-br from-[#7c2d12] via-[#c2410c] to-[#ea580c] px-4 py-3.5 flex items-center justify-between overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-white/[0.14] to-transparent pointer-events-none"></div>
        <div class="flex items-center gap-2.5 relative">
            <div class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <span class="text-[14px] font-bold text-white">BAI Projects</span>
        </div>
        <button @click="mobileSidebarOpen = false" class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 relative">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
        <a href="{{ route('projects.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-[13px] font-medium text-white/55 hover:text-white/85 hover:bg-white/[0.05] transition-colors">
            All Projects
        </a>
        @if($project)
            <div class="pt-2 pb-1 px-1">
                <p class="text-[10px] font-semibold text-white/22 uppercase tracking-wider">{{ $project->name }}</p>
            </div>
            @foreach([
                ['route' => 'projects.overview', 'view' => 'overview', 'label' => 'Overview'],
                ['route' => 'projects.show', 'view' => 'list', 'label' => 'Tasks'],
                ['route' => 'projects.board', 'view' => 'board', 'label' => 'Board'],
                ['route' => 'projects.calendar', 'view' => 'calendar', 'label' => 'Calendar'],
                ['route' => 'projects.milestones', 'view' => 'milestones', 'label' => 'Milestones'],
                ['route' => 'projects.timeline', 'view' => 'timeline', 'label' => 'Timeline'],
                ['route' => 'projects.backlog', 'view' => 'backlog', 'label' => 'Backlog'],
            ] as $tab)
                <a href="{{ route($tab['route'], $project) }}"
                   class="flex items-center px-3 py-2 rounded-lg text-[13px] font-medium transition-colors {{ $currentView === $tab['view'] ? 'bg-orange-500/15 text-orange-300' : 'text-white/45 hover:text-white/78 hover:bg-white/[0.04]' }}">
                    {{ $tab['label'] }}
                </a>
            @endforeach
        @endif
    </nav>
</aside>

{{-- ================================================================ --}}
{{-- MAIN WRAPPER                                                      --}}
{{-- ================================================================ --}}
<div class="lg:pl-[220px] min-h-full flex flex-col sp-main">

    {{-- TOPBAR --}}
    <header class="sticky top-0 z-20 h-14 bg-[#0D0D18]/95 backdrop-blur-md border-b border-white/[0.06] flex items-center gap-3 px-4 lg:px-5 shrink-0">

        <button @click="mobileSidebarOpen = true" class="lg:hidden p-1.5 rounded-md hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-1.5 text-[12px] min-w-0 flex-1">
            @if($project)
                <a href="{{ route('projects.index') }}" class="text-white/32 hover:text-white/60 transition-colors shrink-0 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Projects
                </a>
                <span class="text-white/15 shrink-0">/</span>
                <span class="text-white/70 font-medium truncate">{{ $project->name }}</span>
            @else
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-white/35 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span class="text-white/70 font-medium">Projects</span>
                </div>
            @endif
        </div>

        {{-- Theme toggle --}}
        <button @click="toggleTheme()"
                class="p-2 rounded-lg text-white/40 hover:text-white/65 hover:bg-white/[0.07] transition-colors shrink-0"
                :title="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'">
            {{-- Sun icon (shown in dark mode) --}}
            <svg x-show="theme === 'dark'" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            {{-- Moon icon (shown in light mode) --}}
            <svg x-show="theme === 'light'" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>

        {{-- Right actions (only shown on index) --}}
        @if(!$project)
            <button @click="showCreateProject = true"
                    class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-[12px] font-semibold text-white bg-orange-500 hover:bg-orange-400 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span class="hidden sm:inline">New Project</span>
            </button>
        @endif
    </header>

    {{-- ============================================================ --}}
    {{-- PROJECT HEADER (shown only inside a project)                 --}}
    {{-- ============================================================ --}}
    @if($project)
    <div class="sticky top-14 z-10 bg-[#0E0E1C] border-b border-white/[0.06] shrink-0"
         style="border-left: 3px solid {{ $project->color ?? '#F97316' }}">

        {{-- Row 1: project name + status + progress + actions --}}
        <div class="flex items-center gap-3 px-5 pt-3 pb-2.5">
            <div class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-black/30"
                 style="background: {{ $project->color ?? '#F97316' }};"></div>

            <h1 class="text-[15px] font-semibold text-white/88 truncate">{{ $project->name }}</h1>

            @if($sc)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 {{ $sc['class'] }}">
                    {{ $sc['label'] }}
                </span>
            @endif

            {{-- Progress --}}
            @if($projectTotal > 0)
                <div class="hidden sm:flex items-center gap-2 flex-1 max-w-[200px]">
                    <div class="flex-1 h-1.5 bg-white/[0.08] rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all"
                             style="width: {{ $projectProgress }}%; background: {{ $project->color ?? '#F97316' }};"></div>
                    </div>
                    <span class="text-[10px] text-white/35 shrink-0 font-medium">{{ $projectProgress }}%</span>
                </div>
            @else
                <div class="flex-1"></div>
            @endif

            <div class="flex items-center gap-1.5 shrink-0 ml-auto">
                @if($canEdit)
                    <button x-data @click="$dispatch('open-modal', 'project-settings')"
                            class="p-2 rounded-lg hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors" title="Project Settings">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                @endif
                <button x-data @click="$dispatch('open-create-task')"
                        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-[12px] font-semibold text-white bg-orange-500 hover:bg-orange-400 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span class="hidden sm:inline">New Task</span>
                </button>
            </div>
        </div>

        {{-- Row 2: horizontal tab bar (all tabs visible, horizontally scrollable) --}}
        <div class="flex items-center px-4">
            <div class="flex items-center overflow-x-auto scrollbar-none flex-1 -mb-px">
                @php
                    $allTabs = [
                        ['route' => 'projects.overview',    'view' => 'overview',    'label' => 'Overview'],
                        ['route' => 'projects.show',        'view' => 'list',        'label' => 'Tasks'],
                        ['route' => 'projects.board',       'view' => 'board',       'label' => 'Board'],
                        ['route' => 'projects.calendar',    'view' => 'calendar',    'label' => 'Calendar'],
                        ['route' => 'projects.milestones',  'view' => 'milestones',  'label' => 'Milestones'],
                        ['route' => 'projects.timeline',    'view' => 'timeline',    'label' => 'Timeline'],
                        ['route' => 'projects.backlog',     'view' => 'backlog',     'label' => 'Backlog'],
                        ['route' => 'projects.timesheets',  'view' => 'timesheets',  'label' => 'Timesheets'],
                        ['route' => 'projects.resources',   'view' => 'resources',   'label' => 'Resources'],
                        ['route' => 'projects.workload',    'view' => 'workload',    'label' => 'Workload'],
                        ['route' => 'projects.reports',     'view' => 'reports',     'label' => 'Reports'],
                        ['route' => 'projects.updates',     'view' => 'updates',     'label' => 'Updates'],
                        ['route' => 'projects.chat',        'view' => 'chat',        'label' => 'Chat'],
                        ['route' => 'projects.documents',   'view' => 'documents',   'label' => 'Documents'],
                        ['route' => 'projects.scope',       'view' => 'scope',       'label' => 'Scope'],
                        ['route' => 'projects.budget',      'view' => 'budget',      'label' => 'Budget'],
                    ];
                    if ($project && $project->project_type === 'billing') {
                        $allTabs[] = ['route' => 'projects.billing', 'view' => 'billing', 'label' => 'Billing'];
                    }
                @endphp
                @foreach($allTabs as $tab)
                    <a href="{{ route($tab['route'], $project) }}"
                       class="px-2.5 py-2.5 text-[12px] font-medium border-b-2 whitespace-nowrap transition-colors {{ $currentView === $tab['view'] ? 'border-orange-500 text-white/90' : 'border-transparent text-white/40 hover:text-white/70 hover:border-white/20' }}">
                        {{ $tab['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- PAGE CONTENT --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

{{-- ============================================================ --}}
{{-- QUICK CREATE PROJECT MODAL (inside sp-main for theme scope)  --}}
{{-- ============================================================ --}}
<div x-show="showCreateProject" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     @click.self="showCreateProject = false">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
    <div x-data="createProjectModal()"
         class="relative bg-[#16162A] border border-white/[0.12] rounded-2xl w-full max-w-lg p-6 shadow-2xl"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-[16px] font-bold text-white/85">New Project</h2>
            <button @click="showCreateProject = false" class="w-7 h-7 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/40 hover:text-white/70 flex items-center justify-center transition-colors">
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

            {{-- Project Type --}}
            <div>
                <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Project Type</label>
                <div class="flex gap-2">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="project_type" value="fixed" x-model="projectType" class="sr-only">
                        <div :class="projectType === 'fixed' ? 'border-orange-500/50 bg-orange-500/10 text-orange-300' : 'border-white/[0.1] bg-white/[0.03] text-white/45 hover:border-white/20'"
                             class="px-3 py-2.5 rounded-xl border text-[13px] font-medium text-center transition-all cursor-pointer">
                            Fixed / Milestone
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="project_type" value="billing" x-model="projectType" class="sr-only">
                        <div :class="projectType === 'billing' ? 'border-orange-500/50 bg-orange-500/10 text-orange-300' : 'border-white/[0.1] bg-white/[0.03] text-white/45 hover:border-white/20'"
                             class="px-3 py-2.5 rounded-xl border text-[13px] font-medium text-center transition-all cursor-pointer">
                            Billing / Hourly
                        </div>
                    </label>
                </div>
            </div>

            {{-- Client + Budget/Rate --}}
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
                <button type="button" @click="showCreateProject = false"
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

</div>{{-- end .sp-main --}}

<script>
function createProjectModal() {
    return {
        projectType: 'fixed',
        async init() {
            // Load clients for the dropdown
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

<x-ui.toast />
<x-ui.confirm-modal />
</body>
</html>
