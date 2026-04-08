@props(['board'])

@php
$currentRoute = request()->route()->getName();
$workspaces = auth()->user()->allWorkspaces();
$views = [
    ['route' => 'boards.show', 'label' => 'Board', 'icon' => 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7'],
    ['route' => 'boards.calendar', 'label' => 'Calendar', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['route' => 'boards.timeline', 'label' => 'Timeline', 'icon' => 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6'],
    ['route' => 'boards.table', 'label' => 'Table', 'icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
    ['route' => 'boards.dashboard', 'label' => 'Dashboard', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $board->name ?? 'Board' }} — BAI Board</title>
    <style>[x-cloak]{display:none !important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans overflow-hidden bg-[#0F0F18]" x-data="{ mobileSidebarOpen: false }">
<x-impersonation-banner />

{{-- ============================================================ --}}
{{-- SIDEBAR (same as smartboard layout)                           --}}
{{-- ============================================================ --}}
<aside class="fixed inset-y-0 left-0 w-[220px] bg-[#0B0B14] flex flex-col z-30 border-r border-white/[0.06] hidden lg:flex">
    {{-- Product header — BAI Board --}}
    <div class="shrink-0 border-b border-white/[0.06]">
        <a href="{{ route('hub') }}" class="block px-3 pt-3 pb-1">
            <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="w-full h-auto">
        </a>
        <div class="px-3 pb-2.5">
            <span class="text-[10px] font-semibold text-indigo-400/80 tracking-wider uppercase">Board &middot; Kanban</span>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto scrollbar-thin p-2.5 space-y-0.5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/80 hover:bg-white/[0.05] transition-colors">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('search') }}" class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium text-white/50 hover:text-white/80 hover:bg-white/[0.05] transition-colors">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </a>

        @if($workspaces->count() > 0)
            <div class="pt-3 pb-1 px-2.5">
                <span class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Workspaces</span>
            </div>
            @foreach($workspaces as $ws)
                <div x-data="{ expanded: true }">
                    <button @click="expanded = !expanded" class="flex items-center gap-2 w-full px-2.5 py-2 rounded-lg text-[12px] font-medium text-white/48 hover:text-white/78 hover:bg-white/[0.05] transition-colors">
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform text-white/28" :class="expanded && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <span class="truncate flex-1 text-left">{{ $ws->name }}</span>
                    </button>
                    <div x-show="expanded" x-collapse class="ml-4 space-y-0.5 mt-0.5">
                        @foreach($ws->boards->where('is_archived', false) as $b)
                            <a href="{{ route('boards.show', $b) }}" class="flex items-center gap-2 pl-3 pr-2.5 py-1.5 rounded-lg text-[12px] transition-colors {{ $b->id === $board->id ? 'bg-indigo-500/15 text-indigo-300' : 'text-white/38 hover:text-white/70 hover:bg-white/[0.04]' }}">
                                <span class="w-3.5 h-3.5 rounded shrink-0" style="background: {{ $b->background_value ?? '#1a1a1a' }};"></span>
                                <span class="truncate">{{ $b->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </nav>

    {{-- Bottom --}}
    <div class="border-t border-white/[0.06] p-2.5 space-y-0.5 shrink-0">
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open" class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg hover:bg-white/[0.05] transition-colors">
                <div class="w-7 h-7 rounded-full bg-indigo-500/20 text-indigo-300 text-[10px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
                <p class="text-[12px] font-medium text-white/65 truncate flex-1 text-left">{{ auth()->user()->name ?? 'User' }}</p>
            </button>
            <div x-show="open" style="display:none" class="absolute bottom-full left-0 right-0 mb-1 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl overflow-hidden z-50">
                <div class="px-3.5 py-2.5 border-b border-white/[0.06]">
                    <p class="text-[13px] font-semibold text-white/80">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] text-white/35">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="block px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05]">Profile</a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="w-full text-left px-3.5 py-2.5 text-[12px] text-red-400/80 hover:bg-red-500/10">Sign Out</button>
                </form>
            </div>
        </div>
    </div>
</aside>

{{-- ============================================================ --}}
{{-- MAIN AREA (right of sidebar)                                  --}}
{{-- ============================================================ --}}
<div class="lg:pl-[220px] h-full flex flex-col">

    {{-- Board Background --}}
    <div class="absolute inset-0 lg:left-[220px] z-0"
        @if(($board->background_type ?? 'color') === 'image')
            style="background-image: url('{{ asset('storage/' . $board->background_value) }}'); background-size: cover; background-position: center;"
        @elseif(($board->background_type ?? 'color') === 'gradient')
            style="background: {{ $board->background_value }};"
        @else
            style="background: {{ $board->background_value ?? '#1a1a1a' }};"
        @endif
    ></div>

    {{-- Board Header --}}
    <header class="relative z-20 h-12 bg-black/40 backdrop-blur-md flex items-center px-4 gap-2 shrink-0 border-b border-white/[0.06]">
        {{-- Mobile menu --}}
        <button @click="mobileSidebarOpen = true" class="lg:hidden p-1.5 rounded-md hover:bg-white/10 text-white/40">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <h1 class="text-sm font-semibold text-white/90 truncate max-w-[180px]">{{ $board->name }}</h1>

        <button class="p-1 rounded hover:bg-white/10 text-white/30 hover:text-amber-400 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        </button>

        @if($board->workspace)
            <span class="text-[10px] text-white/30 hidden md:block">{{ $board->workspace->name }}</span>
        @endif

        {{-- View tabs --}}
        <div class="hidden md:flex items-center bg-white/[0.06] rounded-lg overflow-hidden ml-2">
            @foreach($views as $v)
                <a href="{{ route($v['route'], $board) }}" class="flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium transition-colors {{ $currentRoute === $v['route'] ? 'bg-white/15 text-white/90' : 'text-white/40 hover:text-white/65' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $v['icon'] }}"/></svg>
                    {{ $v['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex-1"></div>

        {{-- Filter --}}
        <button onclick="window.dispatchEvent(new CustomEvent('toggle-filter-bar'))" class="flex items-center gap-1 px-2 py-1 rounded text-[11px] text-white/50 hover:bg-white/10 hover:text-white/70 transition-colors cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filter
        </button>

        <div class="w-px h-4 bg-white/10"></div>

        {{-- Members --}}
        <div class="flex -space-x-1">
            @foreach($board->members->take(4) as $member)
                <div class="w-6 h-6 rounded-full bg-white/10 text-white/50 text-[8px] font-bold flex items-center justify-center ring-1 ring-black/30" title="{{ $member->name }}">
                    @if($member->avatar_url)
                        <img src="{{ $member->avatar_url }}" class="w-full h-full rounded-full object-cover" />
                    @else
                        {{ strtoupper(substr($member->name, 0, 2)) }}
                    @endif
                </div>
            @endforeach
            @if($board->members->count() > 4)
                <div class="w-6 h-6 rounded-full bg-white/10 text-white/40 text-[8px] font-bold flex items-center justify-center ring-1 ring-black/30">+{{ $board->members->count() - 4 }}</div>
            @endif
        </div>

        {{-- Chat --}}
        <button onclick="window.dispatchEvent(new CustomEvent('open-board-chat'))" class="p-1.5 rounded hover:bg-white/10 text-white/40 hover:text-white/70 transition-colors cursor-pointer" title="Chat">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </button>

        {{-- Share --}}
        <button onclick="window.dispatchEvent(new CustomEvent('open-board-members'))" class="flex items-center gap-1 px-2.5 py-1 rounded text-[11px] font-medium bg-white/10 text-white/70 hover:bg-white/15 transition-colors cursor-pointer">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Share
        </button>

        {{-- Menu --}}
        <button onclick="window.dispatchEvent(new CustomEvent('open-board-menu'))" class="p-1.5 rounded hover:bg-white/10 text-white/40 hover:text-white/70 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"/></svg>
        </button>
    </header>

    {{-- Board Content --}}
    <main class="relative z-10 flex-1 overflow-hidden">
        {{ $slot }}
    </main>

    {{-- Mobile view tabs --}}
    <nav class="relative z-20 md:hidden h-10 bg-black/50 backdrop-blur-md flex items-center justify-center gap-0.5 px-4 border-t border-white/[0.06] shrink-0">
        @foreach($views as $v)
            <a href="{{ route($v['route'], $board) }}" class="flex items-center gap-1 px-3 py-1 rounded text-[11px] font-medium transition-colors {{ $currentRoute === $v['route'] ? 'bg-white/15 text-white/90' : 'text-white/40 hover:bg-white/5 hover:text-white/60' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $v['icon'] }}"/></svg>
                {{ $v['label'] }}
            </a>
        @endforeach
    </nav>
</div>

{{-- Mobile sidebar --}}
<div x-show="mobileSidebarOpen" style="display:none" @click="mobileSidebarOpen = false" class="fixed inset-0 bg-black/60 z-40 lg:hidden"></div>
<aside x-show="mobileSidebarOpen" style="display:none" class="fixed inset-y-0 left-0 w-[280px] bg-[#0B0B14] border-r border-white/[0.06] flex flex-col z-50 lg:hidden">
    <div class="px-3 pt-3 pb-2 flex items-center justify-between border-b border-white/[0.06]">
        <div class="flex-1 min-w-0">
            <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="w-[180px] h-auto">
            <span class="text-[10px] font-semibold text-indigo-400/80 tracking-wider uppercase mt-1 block">Board &middot; Kanban</span>
        </div>
        <button @click="mobileSidebarOpen = false" class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white/80">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2.5 py-2.5 rounded-lg text-[13px] font-medium text-white/55 hover:text-white/85 hover:bg-white/[0.05] transition-colors">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
    </nav>
</aside>

<x-ui.toast />

<script>
function notificationBell() {
    return {
        notifications: [], unreadCount: 0, showPanel: false,
        init() { this.fetchNotifications(); setInterval(() => this.fetchNotifications(), 30000); },
        async fetchNotifications() {
            try { const res = await fetch('/api/notifications', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); if (res.ok) { const d = await res.json(); this.notifications = d.notifications; this.unreadCount = d.unread_count; } } catch(e) {}
        },
        togglePanel() { this.showPanel = !this.showPanel; if (this.showPanel) this.fetchNotifications(); },
        async markRead(n) { if (!n.read_at) { await fetch(`/api/notifications/${n.id}/read`, { method: 'PUT', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); n.read_at = new Date().toISOString(); this.unreadCount = Math.max(0, this.unreadCount - 1); } if (n.data?.board_id) window.location.href = `/b/${n.data.board_id}`; },
        async markAllRead() { await fetch('/api/notifications/read-all', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }); this.notifications.forEach(n => n.read_at = new Date().toISOString()); this.unreadCount = 0; },
        timeAgo(d) { const m = Math.floor((Date.now() - new Date(d).getTime()) / 60000); if (m < 1) return 'now'; if (m < 60) return m + 'm'; const h = Math.floor(m/60); if (h < 24) return h + 'h'; return Math.floor(h/24) + 'd'; }
    };
}
</script>
<x-ui.confirm-modal />
</body>
</html>
