@props(['title' => 'Dashboard', 'workspaces' => null, 'product' => 'board'])

@php
$org = auth()->user()?->currentOrganization();
$orgInitial = strtoupper(substr($org?->name ?? 'S', 0, 1));
$productConfig = config('products', []);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-product="{{ $product }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — BAI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#0F0F18]"
      x-data="{ sidebarOpen: true, mobileSidebarOpen: false, orgDropdownOpen: false, userDropdownOpen: false }">
<x-impersonation-banner />

{{-- ============================================================ --}}
{{-- SIDEBAR (Desktop)                                            --}}
{{-- ============================================================ --}}
<aside class="fixed inset-y-0 left-0 w-[220px] bg-[#0B0B12] border-r border-white/[0.06] flex flex-col z-30 transition-transform duration-200 hidden lg:flex"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    {{-- TOP: Logo + Org Identity --}}
    <div class="relative shrink-0 border-b border-white/[0.06]">
        <a href="{{ route('hub') }}" class="block px-3 pt-3 pb-1">
            <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="w-full h-auto">
        </a>
        <div class="flex items-center gap-2 px-3 pb-2.5">
            {{-- Org Name --}}
            <div class="flex-1 min-w-0 cursor-pointer" @click="orgDropdownOpen = !orgDropdownOpen">
                <p class="text-[12px] font-semibold text-white/70 truncate leading-tight">{{ $org?->name ?? 'BAI' }}</p>
                <p class="text-[9px] text-white/30 capitalize truncate">{{ ucfirst($product) }}</p>
            </div>
            <button @click="orgDropdownOpen = !orgDropdownOpen"
                    class="w-5 h-5 flex items-center justify-center text-white/25 hover:text-white/60 transition-colors rounded shrink-0">
                <svg class="w-3.5 h-3.5 transition-transform" :class="orgDropdownOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        {{-- Org Dropdown --}}
        <div x-show="orgDropdownOpen" x-cloak @click.away="orgDropdownOpen = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="absolute top-full left-3 right-3 mt-1 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl z-50 overflow-hidden">
            @php $organizations = auth()->user()?->allOrganizations() ?? collect(); @endphp
            @foreach($organizations as $o)
                <form method="POST" action="{{ route('organizations.switch', $o) }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2.5 text-[12px] text-white/60 hover:bg-white/[0.05] hover:text-white/90 transition-colors text-left">
                        <span class="w-6 h-6 rounded-md prod-bg-muted prod-text text-[10px] font-bold flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($o->name, 0, 1)) }}
                        </span>
                        <span class="truncate flex-1">{{ $o->name }}</span>
                        @if($o->id === $org?->id)
                            <svg class="w-3 h-3 text-green-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @endif
                    </button>
                </form>
            @endforeach
            <div class="border-t border-white/[0.06] px-3 py-2">
                <a href="{{ route('organizations.create') }}" class="flex items-center gap-2 text-[11px] text-white/30 hover:text-white/60 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Organization
                </a>
            </div>
        </div>
    </div>

    {{-- NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto scrollbar-thin p-2.5 space-y-0.5">

        {{-- Hub Home --}}
        <a href="{{ route('hub') }}"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/85 hover:bg-white/[0.05] transition-colors {{ request()->routeIs('hub') ? 'nav-active' : '' }}">
            <svg class="w-4 h-4 shrink-0 text-white/35" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Home
        </a>

        {{-- Section: Products --}}
        <div class="pt-3 pb-1 px-2.5">
            <span class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Products</span>
        </div>

        @foreach($productConfig as $key => $def)
            @php
                $isAvailable = $def['available'] ?? false;
                $hasRoute = !empty($def['route']);
                $isActive = ($product === $key)
                    || ($key === 'board' && request()->routeIs('dashboard'))
                    || ($key === 'projects' && request()->routeIs('projects.*'));
                $iconPath = $def['icon'] ?? '';
            @endphp
            @if($isAvailable && $hasRoute)
                <a href="{{ route($def['route']) }}"
                   class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/85 hover:bg-white/[0.05] transition-colors {{ $isActive ? 'nav-active' : '' }}">
                    <svg class="w-4 h-4 shrink-0 text-white/35" fill="currentColor" viewBox="0 0 24 24">
                        <path d="{{ $iconPath }}"/>
                    </svg>
                    {{ $def['name'] ?? $key }}
                </a>
            @else
                <div class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium text-white/22 cursor-default select-none">
                    <svg class="w-4 h-4 shrink-0 text-white/18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $iconPath }}"/>
                    </svg>
                    <span class="flex-1 truncate">{{ $def['name'] ?? $key }}</span>
                    <span class="text-[9px] bg-white/8 text-white/25 px-1.5 py-0.5 rounded-full font-medium shrink-0">Soon</span>
                </div>
            @endif
        @endforeach

        {{-- Workspaces (SmartBoard context) --}}
        @if(isset($workspaces) && $workspaces && $workspaces->count() > 0)
            <div class="pt-3 pb-1 px-2.5">
                <span class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Workspaces</span>
            </div>
            @foreach($workspaces as $workspace)
                <div x-data="{ expanded: true }">
                    <button @click="expanded = !expanded"
                            class="flex items-center gap-2 w-full px-2.5 py-2 rounded-lg text-[12px] font-medium text-white/45 hover:text-white/75 hover:bg-white/[0.05] transition-colors">
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform text-white/30" :class="expanded && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="truncate flex-1 text-left">{{ $workspace->name }}</span>
                    </button>
                    <div x-show="expanded" x-collapse class="ml-4 space-y-0.5 mt-0.5">
                        @foreach($workspace->boards->where('is_archived', false) as $board)
                            <a href="{{ route('boards.show', $board) }}"
                               class="flex items-center gap-2.5 pl-4 pr-2.5 py-1.5 rounded-lg text-[12px] text-white/38 hover:text-white/70 hover:bg-white/[0.04] transition-colors {{ request()->is('b/'.$board->slug) ? 'nav-active' : '' }}">
                                <span class="w-3.5 h-3.5 rounded shrink-0" style="background: {{ $board->background_value ?? 'linear-gradient(135deg,#7c3aed,#d946ef)' }};"></span>
                                <span class="truncate">{{ $board->name }}</span>
                            </a>
                        @endforeach
                        @if($workspace->boards->where('is_archived', false)->isEmpty())
                            <p class="pl-4 pr-2.5 py-1.5 text-[11px] text-white/20 italic">No boards yet</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

    </nav>

    {{-- BOTTOM: User --}}
    <div class="border-t border-white/[0.06] p-2.5 shrink-0">
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg hover:bg-white/[0.05] transition-colors group">
                <div class="w-7 h-7 rounded-full prod-bg-muted prod-text text-[10px] font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-[12px] font-medium text-white/70 truncate leading-tight">{{ auth()->user()->name ?? 'User' }}</p>
                    <p class="text-[10px] text-white/28 truncate leading-tight">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <svg class="w-3.5 h-3.5 text-white/20 group-hover:text-white/40 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute bottom-full left-0 right-0 mb-1 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl overflow-hidden z-50">
                <div class="px-3.5 py-2.5 border-b border-white/[0.06]">
                    <p class="text-[13px] font-semibold text-white/80">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-white/35">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </a>
                @if($org && $org->isAdmin(auth()->user()))
                    <a href="{{ route('organizations.manage', $org) }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        Organization
                    </a>
                @elseif($org)
                    <a href="{{ route('organizations.show', $org) }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Organization
                    </a>
                @endif
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

{{-- ============================================================ --}}
{{-- MOBILE SIDEBAR OVERLAY                                       --}}
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
    <div class="flex items-center justify-between px-3 pt-3 pb-2 border-b border-white/[0.06]">
        <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="w-[180px] h-auto">
        <button @click="mobileSidebarOpen = false" class="p-1.5 rounded-lg hover:bg-white/[0.06] text-white/40 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="flex-1 overflow-y-auto p-3 space-y-0.5">
        <a href="{{ route('hub') }}" class="flex items-center gap-2.5 px-2.5 py-2.5 rounded-lg text-[13px] font-medium text-white/55 hover:text-white/85 hover:bg-white/[0.05] transition-colors {{ request()->routeIs('hub') ? 'nav-active' : '' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>
        @foreach($productConfig as $key => $def)
            @if(($def['available'] ?? false) && !empty($def['route']))
                <a href="{{ route($def['route']) }}" class="flex items-center gap-2.5 px-2.5 py-2.5 rounded-lg text-[13px] font-medium text-white/55 hover:text-white/85 hover:bg-white/[0.05] transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $def['icon'] ?? '' }}"/></svg>
                    {{ $def['name'] ?? $key }}
                </a>
            @endif
        @endforeach
    </nav>
</aside>

{{-- ============================================================ --}}
{{-- MAIN CONTENT                                                  --}}
{{-- ============================================================ --}}
<div class="lg:pl-[220px] min-h-full flex flex-col" :class="sidebarOpen ? '' : 'lg:pl-0'">

    {{-- TOP BAR --}}
    <header class="sticky top-0 z-20 h-14 bg-[#0D0D16]/95 backdrop-blur-md border-b border-white/[0.06] flex items-center gap-4 px-4 lg:px-5 shrink-0">

        {{-- Left: Hamburger --}}
        <div class="flex items-center gap-2">
            <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex p-1.5 rounded-md hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <button @click="mobileSidebarOpen = true" class="lg:hidden p-1.5 rounded-md hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        {{-- Center: Search --}}
        <div class="flex-1 max-w-xs">
            <form action="{{ route('search') }}" method="GET" class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white/25 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search…"
                       class="w-full pl-9 pr-3 py-1.5 bg-white/[0.06] border border-white/[0.08] rounded-lg text-[13px] text-white/75 placeholder-white/25 focus:outline-none focus:ring-1 focus:border-white/20 transition-all"
                       style="--tw-ring-color: var(--prod-accent)"/>
            </form>
        </div>

        {{-- Right: Actions --}}
        <div class="flex items-center gap-1 ml-auto">

            {{-- Create button --}}
            <button class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-semibold text-white/90 transition-all prod-btn">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Create
            </button>

            {{-- Notifications --}}
            <div x-data="appNotificationBell()" class="relative">
                <button @click="togglePanel()" class="relative p-2 rounded-lg hover:bg-white/[0.07] text-white/40 hover:text-white/75 transition-colors">
                    <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount"
                          class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-0.5 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center"></span>
                </button>
                <div x-show="showPanel" x-cloak @click.away="showPanel = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute right-0 top-full mt-2 w-[340px] bg-[#1A1A28] border border-white/[0.1] rounded-2xl shadow-2xl z-50 overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-white/[0.06]">
                        <h3 class="text-[13px] font-semibold text-white/75">Notifications</h3>
                        <button @click="markAllRead()" class="text-[11px] prod-text hover:opacity-80 transition-opacity">Mark all read</button>
                    </div>
                    <div class="max-h-80 overflow-y-auto scrollbar-thin">
                        <template x-if="notifications.length === 0">
                            <p class="p-6 text-center text-white/30 text-[13px]">You're all caught up!</p>
                        </template>
                        <template x-for="n in notifications" :key="n.id">
                            <div @click="markRead(n)" class="px-4 py-3 hover:bg-white/[0.04] cursor-pointer border-b border-white/[0.04] transition-colors" :class="n.read_at ? 'opacity-50' : ''">
                                <div class="flex items-start gap-3">
                                    <div class="w-1.5 h-1.5 rounded-full mt-2 shrink-0" :class="n.read_at ? 'bg-white/20' : 'prod-bg'"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[12px] font-medium text-white/75 truncate" x-text="n.title"></p>
                                        <p class="text-[11px] text-white/40 mt-0.5 truncate" x-text="n.body"></p>
                                        <p class="text-[10px] text-white/25 mt-1" x-text="timeAgo(n.created_at)"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Product Switcher --}}
            <x-product-switcher :products="$accessibleProducts ?? collect()" />

        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="flex-1">
        <div class="p-5 lg:p-6">
            {{ $slot }}
        </div>
    </main>

</div>

<x-ui.toast />

<script>
function appNotificationBell() {
    return {
        notifications: [],
        unreadCount: 0,
        showPanel: false,
        init() {
            this.fetchNotifications();
            setInterval(() => this.fetchNotifications(), 30000);
        },
        async fetchNotifications() {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch('/api/notifications', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
                if (res.ok) {
                    const data = await res.json();
                    this.notifications = data.notifications;
                    this.unreadCount = data.unread_count;
                }
            } catch(e) {}
        },
        togglePanel() { this.showPanel = !this.showPanel; if (this.showPanel) this.fetchNotifications(); },
        async markRead(n) {
            if (!n.read_at) {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                await fetch(`/api/notifications/${n.id}/read`, { method: 'PUT', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
                n.read_at = new Date().toISOString();
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            }
            if (n.data?.board_id) window.location.href = `/b/${n.data.board_id}`;
        },
        async markAllRead() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            await fetch('/api/notifications/read-all', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            this.notifications.forEach(n => n.read_at = new Date().toISOString());
            this.unreadCount = 0;
        },
        timeAgo(dateStr) {
            const diff = Date.now() - new Date(dateStr).getTime();
            const mins = Math.floor(diff / 60000);
            if (mins < 1) return 'Just now';
            if (mins < 60) return mins + 'm ago';
            const hrs = Math.floor(mins / 60);
            if (hrs < 24) return hrs + 'h ago';
            return Math.floor(hrs / 24) + 'd ago';
        }
    };
}
</script>

</body>
</html>
