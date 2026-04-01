<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BAI — Business Automation & Insights</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#0A0A10]" x-data="{ orgDropdown: false, userDropdown: false }">

    {{-- ============================================================ --}}
    {{-- TOP BAR                                                       --}}
    {{-- ============================================================ --}}
    <header class="fixed top-0 left-0 right-0 h-14 bg-[#0A0A10]/95 backdrop-blur-md border-b border-white/[0.06] z-40 flex items-center px-6">

        {{-- BAI Brand --}}
        <div class="flex items-center gap-2.5">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="bai-hub-bg" x1="0" y1="0" x2="28" y2="28" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#312E81"/>
                        <stop offset="55%" stop-color="#4338CA"/>
                        <stop offset="100%" stop-color="#7C3AED"/>
                    </linearGradient>
                    <linearGradient id="bai-hub-shine" x1="0" y1="0" x2="0" y2="28" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="white" stop-opacity="0.18"/>
                        <stop offset="60%" stop-color="white" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                <rect width="28" height="28" rx="7" fill="url(#bai-hub-bg)"/>
                <rect width="28" height="28" rx="7" fill="url(#bai-hub-shine)"/>
                <circle cx="14" cy="7.5" r="2" fill="white"/>
                <circle cx="7.5" cy="20" r="2" fill="white"/>
                <circle cx="20.5" cy="20" r="2" fill="white"/>
                <line x1="14" y1="9.5" x2="8.8" y2="18.5" stroke="white" stroke-opacity="0.65" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="14" y1="9.5" x2="19.2" y2="18.5" stroke="white" stroke-opacity="0.65" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="9.5" y1="20" x2="18.5" y2="20" stroke="white" stroke-opacity="0.65" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <div>
                <span class="text-[15px] font-bold text-white/90 tracking-tight leading-none">BAI</span>
                <span class="block text-[9px] font-medium text-white/35 tracking-wider uppercase leading-none mt-0.5">Business Automation</span>
            </div>
        </div>

        <div class="flex-1"></div>

        {{-- Right: Org switcher + User --}}
        <div class="flex items-center gap-2">

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
                    <div class="border-t border-white/[0.06] px-3.5 py-2">
                        <a href="{{ route('organizations.create') }}" class="flex items-center gap-2 text-[11px] text-white/30 hover:text-white/60 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New Organization
                        </a>
                    </div>
                </div>
            </div>

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
                    @if($org)
                        <a href="{{ route('organizations.show', $org) }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Org Settings
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
    </header>

    {{-- ============================================================ --}}
    {{-- PAGE CONTENT                                                  --}}
    {{-- ============================================================ --}}
    <main class="pt-14 min-h-full">
        <div class="max-w-5xl mx-auto px-6 py-10">
            {{ $slot }}
        </div>
    </main>

    <x-ui.toast />
    <x-ui.confirm-modal />
</body>
</html>
