<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BAI — Business Automation & Insights</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#0A0A10]">

    <header class="fixed top-0 left-0 right-0 h-14 bg-[#0A0A10]/95 backdrop-blur-md border-b border-white/[0.06] z-40 flex items-center px-6">

        {{-- BAI Brand --}}
        <a href="{{ route('hub') }}" class="flex items-center">
            <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="h-10 w-auto">
        </a>

        <div class="flex-1"></div>

        {{-- Right actions --}}
        <div class="flex items-center gap-2">

            {{-- Impersonation indicator --}}
            @if(session('super_admin_impersonating'))
                <form method="POST" action="{{ route('super-admin.stop-impersonating') }}" class="flex items-center">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-medium bg-red-500/15 border border-red-500/25 text-red-400 hover:bg-red-500/25 hover:text-red-300 transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Stop Impersonating
                    </button>
                </form>
            @endif

            {{-- Platform Admin --}}
            @if(auth()->user()->is_super_admin && !session('super_admin_impersonating'))
                <a href="{{ route('super-admin.dashboard') }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[11px] font-medium bg-red-500/10 border border-red-500/20 text-red-400/80 hover:bg-red-500/20 hover:text-red-300 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Admin
                </a>
            @endif

            {{-- Org switcher --}}
            @php $org = auth()->user()?->currentOrganization(); @endphp
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-white/[0.08] bg-white/[0.04] hover:bg-white/[0.07] text-[12px] font-medium text-white/60 hover:text-white/85 transition-all">
                    <span class="w-4 h-4 rounded bg-violet-500/20 text-violet-400 text-[9px] font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($org?->name ?? 'S', 0, 1)) }}
                    </span>
                    <span class="truncate max-w-32">{{ $org?->name ?? 'Organization' }}</span>
                    <svg class="w-3 h-3 text-white/30 transition-transform shrink-0" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     class="absolute right-0 top-full mt-2 w-56 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl z-50 overflow-hidden">
                    @php $organizations = auth()->user()?->allOrganizations() ?? collect(); @endphp
                    @foreach($organizations as $o)
                        <form method="POST" action="{{ route('organizations.switch', $o) }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/60 hover:bg-white/[0.05] hover:text-white/85 transition-colors text-left">
                                <span class="w-5 h-5 rounded bg-indigo-500/15 text-indigo-400 text-[9px] font-bold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($o->name, 0, 1)) }}
                                </span>
                                <span class="truncate flex-1">{{ $o->name }}</span>
                                @if($o->id === $org?->id)
                                    <svg class="w-3 h-3 text-green-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                @endif
                            </button>
                        </form>
                    @endforeach
                    <div class="border-t border-white/[0.06] px-3.5 py-2 space-y-1">
                        @if($org && $org->isAdmin(auth()->user()))
                            <a href="{{ route('organizations.manage', $org) }}" class="flex items-center gap-2 py-1.5 text-[12px] text-white/55 hover:text-white/85 transition-colors">
                                <svg class="w-3.5 h-3.5 shrink-0 text-violet-400/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Organization management
                            </a>
                        @elseif($org)
                            <a href="{{ route('organizations.show', $org) }}" class="flex items-center gap-2 py-1.5 text-[12px] text-white/45 hover:text-white/75 transition-colors">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                View organization
                            </a>
                        @endif
                        <a href="{{ route('organizations.create') }}" class="flex items-center gap-2 text-[11px] text-white/30 hover:text-white/60 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New Organization
                        </a>
                    </div>
                </div>
            </div>

            {{-- Organization (hub-level management for owners/admins) --}}
            @if($org && $org->isAdmin(auth()->user()))
                <a href="{{ route('organizations.manage', $org) }}"
                   class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg border border-white/[0.08] bg-white/[0.04] hover:bg-white/[0.07] text-[12px] font-medium text-white/50 hover:text-white/85 transition-all"
                   title="Organization management">
                    <svg class="w-3.5 h-3.5 text-violet-400/90 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span class="truncate max-w-[9rem]">Organization</span>
                </a>
            @elseif($org)
                <a href="{{ route('organizations.show', $org) }}"
                   class="w-8 h-8 rounded-lg flex items-center justify-center text-white/30 hover:text-white/60 hover:bg-white/[0.06] transition-colors"
                   title="View organization">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </a>
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

    <main class="pt-14 min-h-full">
        <div class="max-w-5xl mx-auto px-6 py-10">
            {{ $slot }}
        </div>
    </main>

    <x-ui.toast />
    <x-ui.confirm-modal />
</body>
</html>
