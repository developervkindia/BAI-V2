@props(['title' => 'Knowledge Base', 'currentView' => 'home'])

@php
$org = auth()->user()?->currentOrganization();
$impersonating = session('super_admin_impersonating');
$kbHubHome = ($currentView === 'home');
$kbNavActive = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium bg-gradient-to-r from-sky-500/15 to-violet-600/10 text-sky-100 border border-sky-500/20';
$kbNavIdle = 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium text-white/50 hover:text-white/85 hover:bg-white/[0.04]';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-product="knowledge_base">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Knowledge Hub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .kb-prose { color: rgba(255,255,255,0.78); line-height: 1.75; font-size: 16px; }
        .kb-prose h1 { font-size: 1.875rem; font-weight: 700; color: rgba(255,255,255,0.94); margin: 1.35em 0 0.55em; }
        .kb-prose h2 { font-size: 1.45rem; font-weight: 650; color: rgba(255,255,255,0.9); margin: 1.2em 0 0.5em; }
        .kb-prose h3 { font-size: 1.2rem; font-weight: 600; color: rgba(255,255,255,0.85); margin: 1.05em 0 0.4em; }
        .kb-prose p { margin: 0.7em 0; }
        .kb-prose ul, .kb-prose ol { margin: 0.7em 0; padding-left: 1.4em; }
        .kb-prose li { margin: 0.3em 0; }
        .kb-prose a { color: #7dd3fc; text-decoration: underline; text-underline-offset: 3px; }
        .kb-prose blockquote { border-left: 3px solid rgba(125,211,252,0.4); margin: 1.1em 0; padding: 0.5em 0 0.5em 1.1em; color: rgba(255,255,255,0.58); background: rgba(255,255,255,0.02); border-radius: 0 8px 8px 0; }
        .kb-prose table { width: 100%; border-collapse: collapse; margin: 1.1em 0; font-size: 14px; border-radius: 10px; overflow: hidden; }
        .kb-prose th, .kb-prose td { border: 1px solid rgba(255,255,255,0.09); padding: 0.55em 0.75em; text-align: left; }
        .kb-prose th { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.88); }
        .kb-prose pre, .kb-prose code { font-family: ui-monospace, monospace; font-size: 13px; }
        .kb-prose pre { background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 1em 1.1em; overflow-x: auto; margin: 1.1em 0; }
        .kb-prose :not(pre) > code { background: rgba(255,255,255,0.07); padding: 0.15em 0.45em; border-radius: 5px; }
        .kb-prose img { max-width: 100%; height: auto; border-radius: 12px; margin: 0.85em 0; }
        .kb-prose hr { border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 1.75em 0; }
    </style>
</head>
<body class="h-full antialiased font-sans bg-[#0F0F18] text-white/90" x-data="{ mobileSidebarOpen: false, kbHash: '' }" x-init="kbHash = window.location.hash; window.addEventListener('hashchange', () => { kbHash = window.location.hash })">
<x-impersonation-banner />

<aside class="fixed inset-y-0 left-0 w-[240px] bg-[#0B0B12] flex flex-col z-30 border-r border-white/[0.06] hidden lg:flex">
    <div class="shrink-0 border-b border-white/[0.06]">
        <a href="{{ route('hub') }}" class="block px-3 pt-3 pb-1">
            <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="w-full h-auto">
        </a>
        <div class="px-3 pb-2.5">
            <span class="text-[10px] font-semibold text-sky-400/80 tracking-wider uppercase">Knowledge &middot; Internal Docs</span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 pt-4 space-y-0.5">
        <a href="{{ route('knowledge.index') }}"
           @if($kbHubHome)
           :class="(kbHash !== '#kb-articles' && kbHash !== '#kb-categories') ? '{{ $kbNavActive }}' : '{{ $kbNavIdle }}'"
           @else
           class="{{ $currentView === 'home' ? $kbNavActive : $kbNavIdle }}"
           @endif>
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>
        <a href="{{ route('knowledge.index') }}#kb-categories"
           @if($kbHubHome)
           :class="kbHash === '#kb-categories' ? '{{ $kbNavActive }}' : '{{ $kbNavIdle }}'"
           @else
           class="{{ $kbNavIdle }}"
           @endif>
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            Categories
        </a>
        <a href="{{ route('knowledge.index') }}#kb-articles"
           @if($kbHubHome)
           :class="kbHash === '#kb-articles' ? '{{ $kbNavActive }}' : '{{ $kbNavIdle }}'"
           @else
           class="{{ $kbNavIdle }}"
           @endif>
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
            Articles
        </a>
        @plan_feature('knowledge_base', 'fulltext_search')
        <a href="{{ route('knowledge.search') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'search' ? 'bg-gradient-to-r from-sky-500/15 to-violet-600/10 text-sky-100 border border-sky-500/20' : 'text-white/50 hover:text-white/85 hover:bg-white/[0.04]' }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </a>
        @endif
        @can_permission('knowledge.contribute')
        <a href="{{ route('knowledge.articles.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'create' ? 'bg-gradient-to-r from-sky-500/15 to-violet-600/10 text-sky-100 border border-sky-500/20' : 'text-white/50 hover:text-white/85 hover:bg-white/[0.04]' }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New article
        </a>
        @endif
        @can_permission('knowledge.moderate')
        <a href="{{ route('knowledge.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'categories' ? 'bg-gradient-to-r from-sky-500/15 to-violet-600/10 text-sky-100 border border-sky-500/20' : 'text-white/50 hover:text-white/85 hover:bg-white/[0.04]' }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Manage categories
        </a>
        <a href="{{ route('knowledge.trash') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'trash' ? 'bg-gradient-to-r from-sky-500/15 to-violet-600/10 text-sky-100 border border-sky-500/20' : 'text-white/50 hover:text-white/85 hover:bg-white/[0.04]' }}">
            <svg class="w-4 h-4 shrink-0 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Trash
        </a>
        @endif
    </nav>

    <div class="p-3 border-t border-white/[0.06] shrink-0 space-y-2">
        <x-product-switcher :currentProduct="'knowledge_base'" />
        <a href="{{ route('hub') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-[12px] text-white/40 hover:text-white/70 hover:bg-white/[0.04]">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            BAI Hub
        </a>
    </div>
</aside>

<div class="lg:pl-[240px] min-h-full flex flex-col {{ $impersonating ? 'pt-12' : '' }}">
    <header class="sticky {{ $impersonating ? 'top-12' : 'top-0' }} z-20 bg-[#0D0D16]/90 backdrop-blur-xl border-b border-white/[0.07] shrink-0">
        <div class="flex items-center gap-3 h-14 px-4 lg:px-8 max-w-7xl mx-auto w-full">
            <button type="button" @click="mobileSidebarOpen = true" class="lg:hidden p-2 rounded-lg hover:bg-white/[0.06] text-white/40">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ route('knowledge.index') }}" class="hidden sm:flex items-center gap-2 text-[13px] font-semibold text-white/70 hover:text-sky-200 transition-colors">
                <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI" class="h-8 w-auto">
                Knowledge Hub
            </a>
            <nav class="hidden md:flex items-center gap-1 ml-6">
                <a href="{{ route('knowledge.index') }}" class="px-3 py-1.5 rounded-lg text-[12px] font-medium text-white/45 hover:text-white/80 hover:bg-white/[0.05]">Home</a>
                <a href="{{ route('knowledge.index') }}#kb-categories" class="px-3 py-1.5 rounded-lg text-[12px] font-medium text-white/45 hover:text-white/80 hover:bg-white/[0.05]">Browse</a>
                @plan_feature('knowledge_base', 'fulltext_search')
                <a href="{{ route('knowledge.search') }}" class="px-3 py-1.5 rounded-lg text-[12px] font-medium text-white/45 hover:text-white/80 hover:bg-white/[0.05]">Search</a>
                @endif
            </nav>
            <div class="flex-1"></div>
            <div class="flex items-center gap-2 text-[11px] text-white/35 truncate max-w-[40%]">
                <span class="w-6 h-6 rounded-md bg-violet-500/20 text-violet-300 text-[9px] font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($org?->name ?? 'O', 0, 1)) }}</span>
                <span class="truncate hidden sm:inline">{{ $org?->name }}</span>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-auto px-4 py-8 lg:px-10 lg:py-10 max-w-7xl w-full mx-auto">
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-[13px] text-emerald-200">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-[13px] text-red-200">{{ session('error') }}</div>
        @endif
        @isset($breadcrumb)
            {{ $breadcrumb }}
        @endisset
        {{ $slot }}
    </main>
</div>

<div x-show="mobileSidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="absolute inset-0 bg-black/70" @click="mobileSidebarOpen = false"></div>
    <aside class="absolute left-0 top-0 bottom-0 w-[min(280px,88vw)] bg-[#0B0B12] border-r border-white/[0.08] shadow-2xl flex flex-col pt-14 px-3 pb-4 overflow-y-auto">
        <button type="button" @click="mobileSidebarOpen = false" class="absolute top-3 right-3 p-2 rounded-lg text-white/40 hover:bg-white/10">✕</button>
        <a href="{{ route('knowledge.index') }}"
           @if($kbHubHome)
           :class="(kbHash !== '#kb-articles' && kbHash !== '#kb-categories') ? 'px-3 py-2.5 rounded-xl text-[13px] font-medium text-white' : 'px-3 py-2.5 rounded-xl text-[13px] text-white/55'"
           @else
           class="px-3 py-2.5 rounded-xl text-[13px] font-medium {{ $currentView === 'home' ? 'text-white' : 'text-white/55' }}"
           @endif>Home</a>
        <a href="{{ route('knowledge.index') }}#kb-categories"
           @if($kbHubHome)
           :class="kbHash === '#kb-categories' ? 'px-3 py-2.5 rounded-xl text-[13px] font-medium text-white' : 'px-3 py-2.5 rounded-xl text-[13px] text-white/55'"
           @else
           class="px-3 py-2.5 rounded-xl text-[13px] text-white/55"
           @endif>Categories</a>
        <a href="{{ route('knowledge.index') }}#kb-articles"
           @if($kbHubHome)
           :class="kbHash === '#kb-articles' ? 'px-3 py-2.5 rounded-xl text-[13px] font-medium text-white' : 'px-3 py-2.5 rounded-xl text-[13px] text-white/55'"
           @else
           class="px-3 py-2.5 rounded-xl text-[13px] text-white/55"
           @endif>Articles</a>
        @plan_feature('knowledge_base', 'fulltext_search')
        <a href="{{ route('knowledge.search') }}" class="px-3 py-2.5 rounded-xl text-[13px] text-white/55">Search</a>
        @endif
        @can_permission('knowledge.contribute')
        <a href="{{ route('knowledge.articles.create') }}" class="px-3 py-2.5 rounded-xl text-[13px] text-sky-300">New article</a>
        @endif
        <a href="{{ route('hub') }}" class="mt-auto px-3 py-2 text-[12px] text-white/35">← BAI Hub</a>
    </aside>
</div>

@stack('knowledge-scripts')
</body>
</html>
