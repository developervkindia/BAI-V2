<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Super Admin' }} - Platform Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0A0A14; }
        .sa-sidebar { background: #08080F; }
        .sa-card { background: #12121F; border: 1px solid rgba(255,255,255,0.08); border-radius: 1rem; }
        .sa-input {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            color: white;
            border-radius: 0.75rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .sa-input:focus {
            outline: none;
            border-color: rgba(239,68,68,0.5);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }
        .sa-input::placeholder { color: rgba(255,255,255,0.3); }
        .sa-table th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgba(255,255,255,0.4);
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .sa-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.7);
            font-size: 0.875rem;
        }
        .sa-table tr:hover td { background: rgba(255,255,255,0.02); }
        .sa-nav-link {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            color: rgba(255,255,255,0.45);
            transition: all 0.2s;
            text-decoration: none;
        }
        .sa-nav-link:hover { color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.04); }
        .sa-nav-link.active {
            color: white;
            background: rgba(239,68,68,0.12);
        }
        .sa-nav-link.active .sa-nav-icon { color: #ef4444; }
        .sa-btn-red {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sa-btn-red:hover { opacity: 0.9; transform: translateY(-1px); }
        .sa-btn-outline {
            background: transparent;
            color: rgba(255,255,255,0.6);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.1);
            cursor: pointer;
            transition: all 0.2s;
        }
        .sa-btn-outline:hover { border-color: rgba(255,255,255,0.2); color: white; }
        .sa-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 500;
        }
        .sa-badge-green { background: rgba(34,197,94,0.15); color: #4ade80; }
        .sa-badge-red { background: rgba(239,68,68,0.15); color: #f87171; }
        .sa-badge-blue { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .sa-badge-yellow { background: rgba(234,179,8,0.15); color: #facc15; }
        .sa-badge-purple { background: rgba(168,85,247,0.15); color: #c084fc; }
        .sa-badge-gray { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.5); }
        .sa-select {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            color: white;
            border-radius: 0.5rem;
            padding: 0.375rem 2rem 0.375rem 0.625rem;
            font-size: 0.8125rem;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='rgba(255,255,255,0.4)' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
        }
        .sa-select:focus {
            outline: none;
            border-color: rgba(239,68,68,0.5);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.1);
        }
        .sa-select option { background: #12121F; color: white; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
    </style>
</head>
<body class="antialiased min-h-screen" style="background: #0A0A14;">

    {{-- Impersonation Banner --}}
    @if(session('super_admin_impersonating'))
    <div class="fixed top-0 left-0 right-0 z-[100] flex items-center justify-center gap-3 px-4 py-2" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
        <svg class="w-4 h-4 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-white text-sm font-medium">
            Impersonating <strong>{{ session('impersonated_user_name', 'Unknown User') }}</strong>
        </span>
        <form method="POST" action="{{ route('super-admin.stop-impersonating') }}" class="inline">
            @csrf
            <button type="submit" class="ml-2 px-3 py-0.5 bg-white/20 hover:bg-white/30 text-white text-xs font-medium rounded-full transition-colors">
                Stop Impersonating
            </button>
        </form>
    </div>
    @endif

    <div class="flex min-h-screen" @if(session('super_admin_impersonating')) style="padding-top: 36px;" @endif>
        {{-- Sidebar --}}
        <aside class="sa-sidebar fixed top-0 left-0 bottom-0 w-[220px] flex flex-col border-r border-white/[0.06] z-50" @if(session('super_admin_impersonating')) style="padding-top: 36px;" @endif>
            {{-- Sidebar Header --}}
            <div class="px-5 py-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-white text-sm font-semibold leading-tight">Platform Admin</h1>
                        <p class="text-[11px] text-white/30 leading-tight">Super Admin Panel</p>
                    </div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 space-y-0.5 overflow-y-auto">
                <a href="{{ route('super-admin.dashboard') }}" class="sa-nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zm0 6a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1h-4a1 1 0 01-1-1v-5zM4 13a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2z"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('super-admin.organizations.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.organizations.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Organizations
                </a>

                <a href="{{ route('super-admin.users.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    Users
                </a>

                <a href="{{ route('super-admin.subscriptions.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.subscriptions.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    Subscriptions
                </a>

                <a href="{{ route('super-admin.products.index') }}" class="sa-nav-link {{ request()->routeIs('super-admin.products.*') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Products
                </a>

                <a href="{{ route('super-admin.audit-log') }}" class="sa-nav-link {{ request()->routeIs('super-admin.audit-log') ? 'active' : '' }}">
                    <svg class="w-[18px] h-[18px] sa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Audit Log
                </a>
            </nav>

            {{-- User Info --}}
            <div class="px-3 py-3 border-t border-white/[0.06]" x-data="{ open: false }" @click.away="open = false">
                <div class="relative">
                    <button @click="open = !open" class="flex items-center gap-2.5 w-full px-1 py-1 rounded-lg hover:bg-white/[0.04] transition-colors">
                        <div class="w-7 h-7 rounded-full bg-red-500/20 flex items-center justify-center shrink-0">
                            <span class="text-red-400 text-xs font-medium">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                        </div>
                        <div class="min-w-0 text-left">
                            <p class="text-white/80 text-xs font-medium truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                            <p class="text-white/30 text-[10px] truncate">Super Admin</p>
                        </div>
                    </button>
                    <div x-show="open" x-cloak class="absolute bottom-full left-0 right-0 mb-1 bg-[#12121F] border border-white/[0.08] rounded-lg shadow-xl overflow-hidden">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2.5 text-[12px] text-white/60 hover:bg-white/[0.06] hover:text-white/80 transition-colors">
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
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 ml-[220px]">
            {{-- Top Bar --}}
            <header class="h-14 flex items-center justify-between px-6 border-b border-white/[0.06]" style="background: rgba(8,8,15,0.5); backdrop-filter: blur(12px);">
                <div class="flex items-center gap-3">
                    <span class="sa-badge sa-badge-red text-[10px] font-semibold tracking-wide uppercase px-2.5 py-1">Super Admin</span>
                    <span class="text-white/20 text-sm">/</span>
                    <h2 class="text-white/70 text-sm font-medium">{{ $title ?? 'Dashboard' }}</h2>
                </div>
                <div class="flex items-center gap-3"></div>
            </header>

            {{-- Page Content --}}
            <main class="p-6">
                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mb-6 px-4 py-3 rounded-xl text-sm font-medium" style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); color: #4ade80;">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 px-4 py-3 rounded-xl text-sm font-medium" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171;">
                    {{ session('error') }}
                </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

<x-ui.confirm-modal />
</body>
</html>
