<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Roles & Permissions - {{ $organization->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#0D0D18] text-white antialiased">

    {{-- Top Bar --}}
    <div class="border-b border-white/[0.07] bg-[#0B0B12]">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('organizations.show', $organization) }}"
                   class="flex items-center gap-2 text-white/40 hover:text-white/70 transition text-[13px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Settings
                </a>
                <div class="w-px h-5 bg-white/[0.07]"></div>
                <span class="text-white/25 text-[12px] font-medium uppercase tracking-wider">{{ $organization->name }}</span>
            </div>
        </div>
    </div>

    {{-- Page Content --}}
    <div class="max-w-7xl mx-auto px-6 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-[22px] font-semibold text-white/85">Roles & Permissions</h1>
                <p class="text-white/40 text-[13px] mt-1">Manage organization roles and their permissions</p>
            </div>
            <a href="{{ route('roles.create', $organization) }}"
               class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-orange-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Role
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3 text-green-400 text-[13px]">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 text-red-400 text-[13px]">
                {{ session('error') }}
            </div>
        @endif

        {{-- Roles Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($roles as $role)
                <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-5 flex flex-col justify-between hover:border-white/[0.12] transition group">
                    <div>
                        {{-- Role Header --}}
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-orange-500/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-[14px] font-semibold text-white/85">{{ $role->name }}</h3>
                                    @if($role->is_system)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-violet-500/15 text-violet-400 uppercase tracking-wide mt-0.5">
                                            System
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        @if($role->description)
                            <p class="text-white/40 text-[12px] leading-relaxed mb-4">{{ $role->description }}</p>
                        @endif

                        {{-- Stats --}}
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span class="text-[12px] text-white/40">{{ $role->users_count ?? 0 }} {{ Str::plural('member', $role->users_count ?? 0) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-[12px] text-white/40">{{ $role->permissions_count ?? 0 }} {{ Str::plural('permission', $role->permissions_count ?? 0) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-3 border-t border-white/[0.05]">
                        <a href="{{ route('roles.edit', [$organization, $role]) }}"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 border border-white/[0.1] text-white/50 hover:text-white/70 hover:border-white/[0.18] rounded-xl px-3 py-2 text-[12px] font-medium transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                        @unless($role->is_system)
                            <form action="{{ route('roles.destroy', [$organization, $role]) }}"
                                  method="POST"
                                  x-data x-on:submit.prevent="$dispatch('confirm-modal', { title: 'Delete Role', message: 'Are you sure you want to delete this role? Members with this role will lose these permissions.', confirmLabel: 'Delete', variant: 'danger', onConfirm: () => $el.submit() })">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-1.5 border border-red-500/20 text-red-400/60 hover:text-red-400 hover:border-red-500/40 hover:bg-red-500/10 rounded-xl px-3 py-2 text-[12px] font-medium transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-[#111120] border border-white/[0.07] rounded-2xl p-12 text-center">
                    <div class="w-12 h-12 rounded-xl bg-white/[0.05] flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-white/60 text-[14px] font-medium mb-1">No roles defined yet</h3>
                    <p class="text-white/30 text-[12px] mb-5">Create your first role to manage permissions for your organization members.</p>
                    <a href="{{ route('roles.create', $organization) }}"
                       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2.5 text-[13px] font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Role
                    </a>
                </div>
            @endforelse
        </div>
    </div>

</body>
</html>
