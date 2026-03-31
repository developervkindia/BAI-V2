<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Members - {{ $organization->name }}</title>
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
    <div class="max-w-7xl mx-auto px-6 py-8" x-data="usersManager()">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-semibold text-white/85">Members</h1>
                <p class="text-white/40 text-[13px] mt-1">Manage organization members, roles, and invitations</p>
            </div>
            <button @click="showInviteModal = true"
                    class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-orange-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Invite User
            </button>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3 text-green-400 text-[13px]">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters Bar --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4 mb-6">
            <div class="flex flex-wrap items-center gap-3">
                {{-- Search --}}
                <div class="flex-1 min-w-[240px]">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text"
                               x-model="search"
                               placeholder="Search by name or email..."
                               class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl pl-9 pr-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                    </div>
                </div>

                {{-- Department Filter --}}
                <select x-model="filterDepartment"
                        class="bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition min-w-[150px]">
                    <option value="">All Departments</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>

                {{-- Role Filter --}}
                <select x-model="filterRole"
                        class="bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition min-w-[140px]">
                    <option value="">All Roles</option>
                    @foreach($roles ?? [] as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>

                {{-- Status Filter --}}
                <select x-model="filterStatus"
                        class="bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition min-w-[130px]">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="on_leave">On Leave</option>
                </select>
            </div>
        </div>

        {{-- Users Table --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/[0.07]">
                            <th class="text-left px-5 py-3.5 text-[11px] font-semibold text-white/38 uppercase tracking-wider">Member</th>
                            <th class="text-left px-5 py-3.5 text-[11px] font-semibold text-white/38 uppercase tracking-wider">Department</th>
                            <th class="text-left px-5 py-3.5 text-[11px] font-semibold text-white/38 uppercase tracking-wider">Roles</th>
                            <th class="text-left px-5 py-3.5 text-[11px] font-semibold text-white/38 uppercase tracking-wider">Status</th>
                            <th class="text-right px-5 py-3.5 text-[11px] font-semibold text-white/38 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.04]">
                        @forelse($members as $member)
                            <tr class="hover:bg-white/[0.02] transition"
                                x-show="matchesFilters('{{ addslashes($member->name) }}', '{{ addslashes($member->email) }}', '{{ $member->employeeProfiles->first()?->department ?? '' }}', '{{ $member->pivot->role ?? 'member' }}', '{{ $member->employeeProfiles->first()?->status ?? 'active' }}')"
                                x-cloak>
                                {{-- Avatar + Name + Email --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-orange-500/20 to-orange-600/20 flex items-center justify-center text-orange-400 text-[13px] font-semibold flex-shrink-0">
                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-[13px] font-medium text-white/80">{{ $member->name }}</div>
                                            <div class="text-[12px] text-white/35">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Department & Designation --}}
                                <td class="px-5 py-3.5">
                                    @php $profile = $member->employeeProfiles->first(); @endphp
                                    @if($profile && ($profile->designation || $profile->department))
                                        <div class="text-[12px] text-white/60">{{ $profile->designation ?? '-' }}</div>
                                        <div class="text-[11px] text-white/30">{{ $profile->department ?? '' }}</div>
                                    @else
                                        <span class="text-[12px] text-white/25">—</span>
                                    @endif
                                </td>

                                {{-- Roles --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap gap-1.5">
                                        @php $sysRole = $member->pivot->role ?? 'member'; @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium
                                            {{ $sysRole === 'owner' ? 'bg-red-500/15 text-red-400' : ($sysRole === 'admin' ? 'bg-violet-500/15 text-violet-400' : 'bg-sky-500/15 text-sky-400') }}">
                                            {{ ucfirst($sysRole) }}
                                        </span>
                                        @foreach($member->organizationRoles as $role)
                                            @if(!$role->is_system)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-orange-500/15 text-orange-400">
                                                    {{ $role->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-3.5">
                                    @php
                                        $status = $member->employeeProfiles->first()?->status ?? 'active';
                                        $statusClasses = match($status) {
                                            'active' => 'bg-green-500/15 text-green-400',
                                            'inactive' => 'bg-red-500/15 text-red-400',
                                            'on_leave' => 'bg-amber-500/15 text-amber-400',
                                            default => 'bg-white/10 text-white/50',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium {{ $statusClasses }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ str_replace('_', ' ', ucfirst($status)) }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.show', [$organization, $member]) }}"
                                           class="inline-flex items-center gap-1 border border-white/[0.08] text-white/40 hover:text-white/65 hover:border-white/[0.15] rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View
                                        </a>
                                        <a href="{{ route('users.edit', [$organization, $member]) }}"
                                           class="inline-flex items-center gap-1 border border-white/[0.08] text-white/40 hover:text-white/65 hover:border-white/[0.15] rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        @if(($member->employeeProfiles->first()?->status ?? 'active') === 'active')
                                            <form action="{{ route('users.deactivate', [$organization, $member]) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Are you sure you want to deactivate this member?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 border border-red-500/15 text-red-400/50 hover:text-red-400 hover:border-red-500/30 rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                    Deactivate
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('users.activate', [$organization, $member]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 border border-green-500/15 text-green-400/50 hover:text-green-400 hover:border-green-500/30 rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Activate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center">
                                    <div class="w-12 h-12 rounded-xl bg-white/[0.05] flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-6 h-6 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-white/60 text-[14px] font-medium mb-1">No members yet</h3>
                                    <p class="text-white/30 text-[12px]">Invite people to join your organization.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($members->hasPages())
                <div class="px-5 py-4 border-t border-white/[0.05]">
                    {{ $members->links() }}
                </div>
            @endif
        </div>

        {{-- Invite Modal --}}
        <div x-show="showInviteModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-cloak>
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showInviteModal = false"></div>

            {{-- Modal --}}
            <div class="relative bg-[#111120] border border-white/[0.1] rounded-2xl w-full max-w-md shadow-2xl"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop>
                <form action="{{ route('org-invitations.store', $organization) }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-[16px] font-semibold text-white/85">Invite User</h2>
                            <button type="button" @click="showInviteModal = false" class="text-white/30 hover:text-white/60 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            {{-- Email --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">
                                    Email Address <span class="text-red-400">*</span>
                                </label>
                                <input type="email"
                                       name="email"
                                       required
                                       placeholder="user@example.com"
                                       class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/25 transition">
                            </div>

                            {{-- System Role --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">
                                    System Role <span class="text-red-400">*</span>
                                </label>
                                <select name="system_role"
                                        required
                                        class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>

                            {{-- Custom Role --}}
                            <div>
                                <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">
                                    Custom Role (Optional)
                                </label>
                                <select name="role_id"
                                        class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-orange-500/50 transition">
                                    <option value="">No custom role</option>
                                    @foreach($roles ?? [] as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-white/[0.07] flex items-center justify-end gap-3">
                        <button type="button"
                                @click="showInviteModal = false"
                                class="border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2.5 text-[13px] font-medium transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="bg-orange-500 hover:bg-orange-400 text-white rounded-xl px-4 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-orange-500/20">
                            Send Invitation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function usersManager() {
            return {
                search: '',
                filterDepartment: '',
                filterRole: '',
                filterStatus: '',
                showInviteModal: false,

                matchesFilters(name, email, department, roleIds, status) {
                    // Search filter
                    if (this.search) {
                        const query = this.search.toLowerCase();
                        if (!name.toLowerCase().includes(query) && !email.toLowerCase().includes(query)) {
                            return false;
                        }
                    }
                    // Department filter
                    if (this.filterDepartment && department !== this.filterDepartment) {
                        return false;
                    }
                    // Role filter
                    if (this.filterRole && !roleIds.split(',').includes(this.filterRole)) {
                        return false;
                    }
                    // Status filter
                    if (this.filterStatus && status !== this.filterStatus) {
                        return false;
                    }
                    return true;
                }
            };
        }
    </script>

</body>
</html>
