@props(['title' => 'BAI Docs', 'currentView' => 'home'])

@php
$org = auth()->user()?->currentOrganization();
$impersonating = session('super_admin_impersonating');
$docsNavActive = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium bg-gradient-to-r from-sky-500/15 to-violet-600/10 text-sky-100 border border-sky-500/20';
$docsNavIdle = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-white/50 hover:text-white/85 hover:bg-white/[0.04]';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-product="docs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — BAI Docs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
        .scrollbar-none { scrollbar-width: none; }
    </style>
</head>
<body class="h-full antialiased font-sans bg-[#0F0F18] text-white/90" x-data="{ mobileSidebarOpen: false }">
<x-impersonation-banner />

{{-- ============================================================ --}}
{{-- SIDEBAR                                                       --}}
{{-- ============================================================ --}}
<aside class="fixed inset-y-0 left-0 w-[240px] bg-[#0B0B12] flex flex-col z-30 border-r border-white/[0.06] hidden lg:flex">

    {{-- Logo --}}
    <div class="shrink-0 border-b border-white/[0.06]">
        <a href="{{ route('hub') }}" class="block px-3 pt-3 pb-1">
            <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="w-full h-auto">
        </a>
        <div class="px-3 pb-2.5">
            <span class="text-[10px] font-semibold text-sky-400/80 tracking-wider uppercase">Docs &middot; Workspace</span>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 pt-4 space-y-0.5 scrollbar-none">

        {{-- New button with dropdown --}}
        <div x-data="{ open: false }" class="relative mb-3">
            <button @click="open = !open" class="w-full flex items-center gap-2 px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-sky-500 transition-all">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New
            </button>
            <div x-show="open" @click.away="open = false" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute left-0 right-0 mt-1.5 bg-[#1a1a2e] border border-white/10 rounded-xl shadow-2xl py-1.5 z-50">
                <a href="{{ route('docs.documents.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-white/70 hover:bg-white/[0.06] hover:text-white/90 transition-colors">
                    <span class="w-7 h-7 rounded-lg bg-sky-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <span>Document</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 ml-auto shrink-0"></span>
                </a>
                <a href="{{ route('docs.spreadsheets.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-white/70 hover:bg-white/[0.06] hover:text-white/90 transition-colors">
                    <span class="w-7 h-7 rounded-lg bg-emerald-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M14 3v18M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6z"/></svg>
                    </span>
                    <span>Spreadsheet</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 ml-auto shrink-0"></span>
                </a>
                <a href="{{ route('docs.forms.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-white/70 hover:bg-white/[0.06] hover:text-white/90 transition-colors">
                    <span class="w-7 h-7 rounded-lg bg-violet-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
                    </span>
                    <span>Form</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400 ml-auto shrink-0"></span>
                </a>
                <a href="{{ route('docs.presentations.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-white/70 hover:bg-white/[0.06] hover:text-white/90 transition-colors">
                    <span class="w-7 h-7 rounded-lg bg-amber-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    </span>
                    <span>Presentation</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 ml-auto shrink-0"></span>
                </a>
            </div>
        </div>

        {{-- Home --}}
        <a href="{{ route('docs.index') }}" class="{{ $currentView === 'home' ? $docsNavActive : $docsNavIdle }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>

        {{-- Starred --}}
        <a href="{{ route('docs.starred') }}" class="{{ $currentView === 'starred' ? $docsNavActive : $docsNavIdle }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            Starred
        </a>

        {{-- Shared with me --}}
        <a href="{{ route('docs.shared') }}" class="{{ $currentView === 'shared' ? $docsNavActive : $docsNavIdle }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
            Shared with me
        </a>

        {{-- All folders --}}
        <a href="{{ route('docs.index', ['type' => 'folder']) }}" class="{{ $currentView === 'folders' ? $docsNavActive : $docsNavIdle }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            All folders
        </a>

        {{-- Search --}}
        <a href="{{ route('docs.index', ['search' => 1]) }}" class="{{ $currentView === 'search' ? $docsNavActive : $docsNavIdle }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </a>

        {{-- Trash (gated) --}}
        @can_permission('docs.moderate')
        <a href="{{ route('docs.trash') }}" class="{{ $currentView === 'trash' ? $docsNavActive : $docsNavIdle }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Trash
        </a>
        @endif

    </nav>

    {{-- Footer --}}
    <div class="p-3 border-t border-white/[0.06] shrink-0 space-y-2">
        <x-product-switcher :currentProduct="'docs'" />
        <a href="{{ route('hub') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-[12px] text-white/40 hover:text-white/70 hover:bg-white/[0.04] transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to BAI Hub
        </a>
    </div>
</aside>

{{-- ============================================================ --}}
{{-- MAIN CONTENT                                                  --}}
{{-- ============================================================ --}}
<div class="lg:pl-[240px] min-h-full flex flex-col {{ $impersonating ? 'pt-12' : '' }}">

    {{-- Header --}}
    <header class="sticky {{ $impersonating ? 'top-12' : 'top-0' }} z-20 bg-[#0D0D16]/90 backdrop-blur-xl border-b border-white/[0.07] shrink-0">
        <div class="flex items-center gap-3 h-14 px-4 lg:px-8 max-w-7xl mx-auto w-full">
            <button type="button" @click="mobileSidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-white/[0.06] text-white/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ route('docs.index') }}" class="hidden sm:flex items-center gap-2 text-[13px] font-semibold text-white/70 hover:text-sky-200 transition-colors">
                <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="h-8 w-auto">
                Docs
            </a>
            <nav class="hidden md:flex items-center gap-1 ml-6">
                @isset($breadcrumb)
                    {{ $breadcrumb }}
                @endisset
            </nav>
            <div class="flex-1"></div>
            <div class="flex items-center gap-2 text-[11px] text-white/35 truncate max-w-[40%]">
                <span class="w-6 h-6 rounded-md bg-sky-500/20 text-sky-300 text-[9px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($org?->name ?? 'O', 0, 1)) }}</span>
                <span class="truncate hidden sm:inline">{{ $org?->name }}</span>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 overflow-auto px-4 py-8 lg:px-10 lg:py-10 max-w-7xl w-full mx-auto">
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-[13px] text-emerald-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-[13px] text-red-200">{{ session('error') }}</div>
        @endif
        {{ $slot }}
    </main>
</div>

{{-- ============================================================ --}}
{{-- MOBILE SIDEBAR OVERLAY                                        --}}
{{-- ============================================================ --}}
<div x-show="mobileSidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="absolute inset-0 bg-black/70" @click="mobileSidebarOpen = false"></div>
    <aside class="absolute left-0 top-0 bottom-0 w-[min(280px,88vw)] bg-[#0B0B12] border-r border-white/[0.08] shadow-2xl flex flex-col pt-14 px-3 pb-4 overflow-y-auto">
        <button type="button" @click="mobileSidebarOpen = false" class="absolute top-3 right-3 p-2 rounded-lg text-white/40 hover:bg-white/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- Mobile: New button --}}
        <div x-data="{ open: false }" class="relative mb-3">
            <button @click="open = !open" class="w-full flex items-center gap-2 px-4 py-2.5 rounded-xl text-[13px] font-semibold bg-gradient-to-r from-sky-500 to-sky-600 text-white shadow-lg shadow-sky-500/20">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New
            </button>
            <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 right-0 mt-1.5 bg-[#1a1a2e] border border-white/10 rounded-xl shadow-2xl py-1.5 z-50">
                <a href="{{ route('docs.documents.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-white/70 hover:bg-white/[0.06]">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-400 shrink-0"></span> Document
                </a>
                <a href="{{ route('docs.spreadsheets.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-white/70 hover:bg-white/[0.06]">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span> Spreadsheet
                </a>
                <a href="{{ route('docs.forms.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-white/70 hover:bg-white/[0.06]">
                    <span class="w-1.5 h-1.5 rounded-full bg-violet-400 shrink-0"></span> Form
                </a>
                <a href="{{ route('docs.presentations.create') }}" class="flex items-center gap-3 px-3.5 py-2.5 text-[13px] text-white/70 hover:bg-white/[0.06]">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span> Presentation
                </a>
            </div>
        </div>

        <a href="{{ route('docs.index') }}" class="px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'home' ? 'text-white' : 'text-white/55' }}">Home</a>
        <a href="{{ route('docs.starred') }}" class="px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'starred' ? 'text-white' : 'text-white/55' }}">Starred</a>
        <a href="{{ route('docs.shared') }}" class="px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'shared' ? 'text-white' : 'text-white/55' }}">Shared with me</a>
        <a href="{{ route('docs.index', ['type' => 'folder']) }}" class="px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'folders' ? 'text-white' : 'text-white/55' }}">All folders</a>
        <a href="{{ route('docs.index', ['search' => 1]) }}" class="px-3 py-2.5 rounded-xl text-[13px] text-white/55">Search</a>
        @can_permission('docs.moderate')
        <a href="{{ route('docs.trash') }}" class="px-3 py-2.5 rounded-xl text-[13px] text-white/55">Trash</a>
        @endif
        <a href="{{ route('hub') }}" class="mt-auto px-3 py-2 text-[12px] text-white/35">
            <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            BAI Hub
        </a>
    </aside>
</div>

<x-ui.confirm-modal />

@stack('docs-scripts')
</body>
</html>
