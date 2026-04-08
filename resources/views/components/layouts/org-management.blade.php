@props(['organization', 'activeTab' => 'general'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $organization->name }} — Organization Settings</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#0A0A10]">

    {{-- TOP BAR --}}
    <header class="fixed top-0 left-0 right-0 h-14 bg-[#0A0A10]/95 backdrop-blur-md border-b border-white/[0.06] z-40 flex items-center px-6">
        <a href="{{ route('hub') }}" class="flex items-center">
            <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="h-10 w-auto">
        </a>

        <div class="mx-4 w-px h-6 bg-white/[0.08]"></div>
        <span class="text-[13px] font-medium text-white/45">{{ $organization->name }}</span>

        <div class="flex-1"></div>

        <div class="flex items-center gap-2">
            @if(session('super_admin_impersonating'))
                <form method="POST" action="{{ route('super-admin.stop-impersonating') }}" class="flex items-center">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-medium bg-red-500/15 border border-red-500/25 text-red-400 hover:bg-red-500/25 hover:text-red-300 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Stop Impersonating
                    </button>
                </form>
            @endif

            {{-- User menu --}}
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open"
                        class="w-8 h-8 rounded-full bg-indigo-500/15 text-indigo-400 text-[10px] font-bold flex items-center justify-center hover:bg-indigo-500/25 transition-colors">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </button>
                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute right-0 top-full mt-2 w-52 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl z-50 overflow-hidden">
                    <div class="px-3.5 py-3 border-b border-white/[0.06]">
                        <p class="text-[13px] font-semibold text-white/80">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-white/35">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
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

    {{-- BODY --}}
    <div class="pt-14 flex h-screen">

        {{-- SIDEBAR --}}
        <aside class="w-56 shrink-0 border-r border-white/[0.06] bg-[#0D0D16] overflow-y-auto">
            <div class="px-4 pt-6 pb-3">
                <a href="{{ route('hub') }}" class="flex items-center gap-2 text-[11px] text-white/30 hover:text-white/55 transition-colors mb-5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Hub
                </a>
                <div class="flex items-center gap-2.5 mb-1">
                    <span class="w-7 h-7 rounded-lg bg-violet-500/20 text-violet-400 text-[10px] font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($organization->name, 0, 1)) }}
                    </span>
                    <span class="text-[13px] font-semibold text-white/80 truncate">{{ $organization->name }}</span>
                </div>
                <p class="text-[10px] text-white/25 ml-[38px]">Organization Management</p>
            </div>

            <nav class="px-3 pb-6 space-y-0.5">
                <p class="text-[10px] font-semibold text-white/20 uppercase tracking-wider px-3 pt-4 pb-2">Organization</p>

                @if($organization->isAdmin(auth()->user()))
                <a href="{{ route('organizations.manage', $organization) }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[12px] font-medium transition-colors
                          {{ $activeTab === 'overview' ? 'bg-white/[0.07] text-white/85' : 'text-white/40 hover:text-white/65 hover:bg-white/[0.04]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    Overview
                </a>
                @endif

                <a href="{{ route('organizations.show', $organization) }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[12px] font-medium transition-colors
                          {{ $activeTab === 'general' ? 'bg-white/[0.07] text-white/85' : 'text-white/40 hover:text-white/65 hover:bg-white/[0.04]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    General
                </a>

                @if($organization->isAdmin(auth()->user()))
                <p class="text-[10px] font-semibold text-white/20 uppercase tracking-wider px-3 pt-5 pb-2">People</p>

                <a href="{{ route('users.index', $organization) }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[12px] font-medium transition-colors
                          {{ $activeTab === 'members' ? 'bg-white/[0.07] text-white/85' : 'text-white/40 hover:text-white/65 hover:bg-white/[0.04]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Members
                </a>

                <a href="{{ route('roles.index', $organization) }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[12px] font-medium transition-colors
                          {{ $activeTab === 'roles' ? 'bg-white/[0.07] text-white/85' : 'text-white/40 hover:text-white/65 hover:bg-white/[0.04]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Roles & Permissions
                </a>

                <p class="text-[10px] font-semibold text-white/20 uppercase tracking-wider px-3 pt-5 pb-2">Billing</p>

                <a href="{{ route('subscriptions.index') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-[12px] font-medium transition-colors
                          {{ $activeTab === 'subscriptions' ? 'bg-white/[0.07] text-white/85' : 'text-white/40 hover:text-white/65 hover:bg-white/[0.04]' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Subscriptions
                </a>
                @endif
            </nav>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 overflow-y-auto">
            <div class="max-w-5xl mx-auto px-8 py-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    <x-ui.toast />
    <x-ui.confirm-modal />
</body>
</html>
