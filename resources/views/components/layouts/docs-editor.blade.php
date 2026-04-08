@props(['title' => 'Untitled Document', 'document' => null, 'canEdit' => false])

@php
    $user = auth()->user();
    $userInitials = strtoupper(substr($user->name ?? 'U', 0, 2));
    $docType = $document?->type ?? 'document';
    $typeBadgeColors = [
        'document'     => 'bg-sky-500/20 text-sky-400',
        'spreadsheet'  => 'bg-emerald-500/20 text-emerald-400',
        'form'         => 'bg-violet-500/20 text-violet-400',
        'presentation' => 'bg-amber-500/20 text-amber-400',
    ];
    $badgeClass = $typeBadgeColors[$docType] ?? 'bg-white/10 text-white/60';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-product="docs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — BAI Docs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
    @stack('editor-head')
</head>
<body class="h-full antialiased font-sans bg-[#0F0F18]">

{{-- ============================================================ --}}
{{-- THIN STICKY HEADER                                            --}}
{{-- ============================================================ --}}
<header class="sticky top-0 z-50 h-12 bg-[#0D0D16]/95 backdrop-blur border-b border-white/[0.06] flex items-center px-3 gap-3"
        x-data="{ userMenu: false }">

    {{-- Left: Back button + Document type badge + Editable title --}}
    <div class="flex items-center gap-2.5 min-w-0 flex-1">
        {{-- Back to docs list --}}
        <a href="{{ route('docs.index') }}"
           class="shrink-0 p-1.5 rounded-lg hover:bg-white/[0.07] text-white/40 hover:text-white/75 transition-colors"
           title="Back to documents">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>

        {{-- Document type badge --}}
        <span class="shrink-0 text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $badgeClass }}">
            {{ $docType }}
        </span>

        {{-- Inline editable title --}}
        <div class="flex-1 min-w-0">
            <input id="doc-title-input"
                   type="text"
                   value="{{ $title }}"
                   oninput="
                       clearTimeout(window._titleSaveTimer);
                       window._titleSaveTimer = setTimeout(function() {
                           if (window._triggerDocAutoSave) window._triggerDocAutoSave();
                       }, 800);
                   "
                   class="w-full bg-transparent border-0 outline-none text-white text-sm font-medium placeholder-white/25 truncate px-1 py-0.5 rounded focus:ring-1 focus:ring-white/15"
                   placeholder="Untitled Document"
                   @if(!$canEdit) readonly @endif
            />
        </div>
    </div>

    {{-- Center: Save status indicator --}}
    <div class="shrink-0 flex items-center justify-center">
        <span id="save-status" class="text-white/30 text-xs">All changes saved</span>
    </div>

    {{-- Right: Share button + User avatar --}}
    <div class="flex items-center gap-2 shrink-0">
        {{-- Share button --}}
        <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium bg-sky-500/15 text-sky-400 hover:bg-sky-500/25 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
            Share
        </button>

        {{-- User avatar dropdown --}}
        <div class="relative" @click.away="userMenu = false">
            <button @click="userMenu = !userMenu"
                    class="w-8 h-8 rounded-full bg-sky-500/20 text-sky-400 text-[11px] font-bold flex items-center justify-center hover:bg-sky-500/30 transition-colors">
                {{ $userInitials }}
            </button>
            <div x-show="userMenu" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute right-0 top-full mt-2 w-56 bg-[#1A1A28] border border-white/[0.1] rounded-xl shadow-2xl z-50 overflow-hidden">
                <div class="px-3.5 py-2.5 border-b border-white/[0.06]">
                    <p class="text-[13px] font-semibold text-white/80">{{ $user->name }}</p>
                    <p class="text-[11px] text-white/35">{{ $user->email }}</p>
                </div>
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile
                </a>
                <a href="{{ route('hub') }}"
                   class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/55 hover:bg-white/[0.05] hover:text-white/80 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Hub
                </a>
                <div class="border-t border-white/[0.06]">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2.5 w-full px-3.5 py-2.5 text-[12px] text-red-400/80 hover:bg-red-500/10 hover:text-red-400 transition-colors text-left">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</header>

{{-- ============================================================ --}}
{{-- MAIN CONTENT — full width, no sidebar                         --}}
{{-- ============================================================ --}}
<main class="h-[calc(100vh-3rem)]">
    {{ $slot }}
</main>

@stack('editor-scripts')
</body>
</html>
