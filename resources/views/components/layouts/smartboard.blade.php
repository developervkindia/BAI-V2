@props(['title' => 'Dashboard', 'workspaces' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — BAI Board</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#0F0F18]"
      x-data="{ mobileSidebarOpen: false }">

{{-- ============================================================ --}}
{{-- SIDEBAR                                                       --}}
{{-- ============================================================ --}}
<aside class="fixed inset-y-0 left-0 w-[220px] bg-[#0B0B14] flex flex-col z-30 border-r border-white/[0.06] hidden lg:flex">

    {{-- PRODUCT HEADER — BAI Board --}}
    <div class="relative bg-gradient-to-br from-[#1e1b4b] via-[#3730a3] to-[#4f46e5] px-4 py-3.5 flex items-center gap-2.5 shrink-0 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-white/[0.14] to-transparent pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-white/0 via-white/20 to-white/0"></div>
        {{-- BAI Board icon --}}
        <div class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 flex items-center justify-center shrink-0 relative">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M13 2L4.09 12.97H11L10 22l8.91-10.97H13L14 2z"/>
            </svg>
        </div>
        <div>
            <span class="text-[13px] font-bold text-white leading-none tracking-tight">BAI Board</span>
            <span class="block text-[9px] text-white/55 font-medium tracking-wider uppercase leading-none mt-0.5">Kanban & Boards</span>
        </div>
    </div>

    {{-- NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto scrollbar-thin p-2.5 space-y-0.5">

        {{-- Core links --}}
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-500/15 text-indigo-300' : 'text-white/50 hover:text-white/80 hover:bg-white/[0.05]' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('search') }}"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] font-medium transition-colors {{ request()->routeIs('search') ? 'bg-indigo-500/15 text-indigo-300' : 'text-white/50 hover:text-white/80 hover:bg-white/[0.05]' }}">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Search
        </a>

        {{-- Workspaces --}}
        @if(isset($workspaces) && $workspaces && $workspaces->count() > 0)
            <div class="pt-3 pb-1 px-2.5">
                <span class="text-[10px] font-semibold text-white/22 uppercase tracking-widest">Workspaces</span>
            </div>

            @foreach($workspaces as $workspace)
                <div x-data="{ expanded: true }">
                    <button @click="expanded = !expanded"
                            class="flex items-center gap-2 w-full px-2.5 py-2 rounded-lg text-[12px] font-medium text-white/48 hover:text-white/78 hover:bg-white/[0.05] transition-colors">
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform text-white/28" :class="expanded && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="truncate flex-1 text-left">{{ $workspace->name }}</span>
                    </button>
                    <div x-show="expanded" x-collapse class="ml-4 space-y-0.5 mt-0.5">
                        @foreach($workspace->boards->where('is_archived', false) as $board)
                            <a href="{{ route('boards.show', $board) }}"
                               class="flex items-center gap-2 pl-3 pr-2.5 py-1.5 rounded-lg text-[12px] transition-colors {{ request()->is('b/'.$board->slug) ? 'bg-indigo-500/15 text-indigo-300' : 'text-white/38 hover:text-white/70 hover:bg-white/[0.04]' }}">
                                <span class="w-3.5 h-3.5 rounded shrink-0" style="background: {{ $board->background_value ?? 'linear-gradient(135deg,#7c3aed,#d946ef)' }};"></span>
                                <span class="truncate">{{ $board->name }}</span>
                            </a>
                        @endforeach
                        @if($workspace->boards->where('is_archived', false)->isEmpty())
                            <p class="pl-3 pr-2.5 py-1 text-[11px] text-white/20 italic">No boards yet</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif

    </nav>

    {{-- BOTTOM: Hub link + User --}}
    <div class="border-t border-white/[0.06] p-2.5 space-y-0.5 shrink-0">
        {{-- Back to Hub --}}
        <a href="{{ route('hub') }}"
           class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[12px] font-medium text-white/38 hover:text-white/65 hover:bg-white/[0.04] transition-colors">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="5"  cy="5"  r="1.5"/>
                <circle cx="12" cy="5"  r="1.5"/>
                <circle cx="19" cy="5"  r="1.5"/>
                <circle cx="5"  cy="12" r="1.5"/>
                <circle cx="12" cy="12" r="1.5"/>
                <circle cx="19" cy="12" r="1.5"/>
                <circle cx="5"  cy="19" r="1.5"/>
                <circle cx="12" cy="19" r="1.5"/>
                <circle cx="19" cy="19" r="1.5"/>
            </svg>
            BAI Hub
        </a>

        {{-- User --}}
        <div x-data="{ open: false }" @click.away="open = false" class="relative">
            <button @click="open = !open"
                    class="flex items-center gap-2.5 w-full px-2.5 py-2 rounded-lg hover:bg-white/[0.05] transition-colors">
                <div class="w-7 h-7 rounded-full bg-indigo-500/20 text-indigo-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <p class="text-[12px] font-medium text-white/65 truncate flex-1 text-left">{{ auth()->user()->name ?? 'User' }}</p>
                <svg class="w-3 h-3 text-white/20 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"/></svg>
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

{{-- ============================================================ --}}
{{-- MOBILE OVERLAY                                                --}}
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
       class="fixed inset-y-0 left-0 w-[280px] bg-[#0B0B14] border-r border-white/[0.06] flex flex-col z-50 lg:hidden">
    <div class="relative bg-gradient-to-br from-[#1e1b4b] via-[#3730a3] to-[#4f46e5] px-4 py-3.5 flex items-center justify-between overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-white/[0.14] to-transparent pointer-events-none"></div>
        <div class="flex items-center gap-2.5 relative">
            <div class="w-7 h-7 rounded-lg bg-white/15 border border-white/20 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L4.09 12.97H11L10 22l8.91-10.97H13L14 2z"/></svg>
            </div>
            <span class="text-[14px] font-bold text-white">BAI Board</span>
        </div>
        <button @click="mobileSidebarOpen = false" class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white/80 relative">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-2.5 py-2.5 rounded-lg text-[13px] font-medium text-white/55 hover:text-white/85 hover:bg-white/[0.05] transition-colors">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('hub') }}" class="flex items-center gap-2.5 px-2.5 py-2.5 rounded-lg text-[13px] font-medium text-white/40 hover:text-white/70 hover:bg-white/[0.04] transition-colors">
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="5" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="19" cy="5" r="1.5"/><circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/><circle cx="5" cy="19" r="1.5"/><circle cx="12" cy="19" r="1.5"/><circle cx="19" cy="19" r="1.5"/></svg>
            BAI Hub
        </a>
    </nav>
</aside>

{{-- ============================================================ --}}
{{-- MAIN CONTENT                                                  --}}
{{-- ============================================================ --}}
<div class="lg:pl-[220px] min-h-full flex flex-col">

    {{-- TOP BAR --}}
    <header class="sticky top-0 z-20 h-14 bg-[#0D0D18]/95 backdrop-blur-md border-b border-white/[0.06] flex items-center gap-4 px-4 lg:px-5 shrink-0">

        <button @click="mobileSidebarOpen = true" class="lg:hidden p-1.5 rounded-md hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        {{-- Search --}}
        <div class="flex-1 max-w-xs">
            <form action="{{ route('search') }}" method="GET" class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-white/25 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search boards…"
                       class="w-full pl-9 pr-3 py-1.5 bg-white/[0.06] border border-white/[0.08] rounded-lg text-[13px] text-white/75 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-indigo-500/40 transition-all"/>
            </form>
        </div>

        <div class="flex items-center gap-1.5 ml-auto">

            {{-- Create board --}}
            <x-ui.dropdown align="right" width="44">
                <x-slot name="trigger">
                    <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-semibold text-white/90 bg-indigo-600 hover:bg-indigo-500 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span class="hidden sm:inline">Create</span>
                    </button>
                </x-slot>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5">New Board</a>
                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5">New Workspace</a>
            </x-ui.dropdown>

            {{-- Notifications --}}
            <div x-data="appNotificationBell()" class="relative">
                <button @click="togglePanel()" class="relative p-2 rounded-lg hover:bg-white/[0.07] text-white/40 hover:text-white/75 transition-colors">
                    <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
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
                        <button @click="markAllRead()" class="text-[11px] text-indigo-400 hover:text-indigo-300 transition-colors">Mark all read</button>
                    </div>
                    <div class="max-h-80 overflow-y-auto scrollbar-thin">
                        <template x-if="notifications.length === 0">
                            <p class="p-6 text-center text-white/30 text-[13px]">You're all caught up!</p>
                        </template>
                        <template x-for="n in notifications" :key="n.id">
                            <div @click="markRead(n)" class="px-4 py-3 hover:bg-white/[0.04] cursor-pointer border-b border-white/[0.04] transition-colors" :class="n.read_at ? 'opacity-50' : ''">
                                <div class="flex items-start gap-3">
                                    <div class="w-1.5 h-1.5 rounded-full mt-2 shrink-0" :class="n.read_at ? 'bg-white/20' : 'bg-indigo-400'"></div>
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

        </div>
    </header>

    {{-- PAGE CONTENT --}}
    <main class="flex-1 p-5 lg:p-6">
        {{ $slot }}
    </main>

</div>

<x-ui.toast />

<script>
function appNotificationBell() {
    return {
        notifications: [], unreadCount: 0, showPanel: false,
        init() { this.fetchNotifications(); setInterval(() => this.fetchNotifications(), 30000); },
        async fetchNotifications() {
            try {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch('/api/notifications', { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
                if (res.ok) { const data = await res.json(); this.notifications = data.notifications; this.unreadCount = data.unread_count; }
            } catch(e) {}
        },
        togglePanel() { this.showPanel = !this.showPanel; if (this.showPanel) this.fetchNotifications(); },
        async markRead(n) {
            if (!n.read_at) {
                const csrf = document.querySelector('meta[name="csrf-token"]').content;
                await fetch(`/api/notifications/${n.id}/read`, { method: 'PUT', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
                n.read_at = new Date().toISOString(); this.unreadCount = Math.max(0, this.unreadCount - 1);
            }
            if (n.data?.board_id) window.location.href = `/b/${n.data.board_id}`;
        },
        async markAllRead() {
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            await fetch('/api/notifications/read-all', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            this.notifications.forEach(n => n.read_at = new Date().toISOString()); this.unreadCount = 0;
        },
        timeAgo(dateStr) {
            const diff = Date.now() - new Date(dateStr).getTime(); const mins = Math.floor(diff / 60000);
            if (mins < 1) return 'Just now'; if (mins < 60) return mins + 'm ago';
            const hrs = Math.floor(mins / 60); if (hrs < 24) return hrs + 'h ago';
            return Math.floor(hrs / 24) + 'd ago';
        }
    };
}
</script>

</body>
</html>
