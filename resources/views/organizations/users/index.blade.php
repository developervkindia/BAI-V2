<x-layouts.org-management :organization="$organization" activeTab="members">

<div x-data="usersManager()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-[20px] font-bold text-white/85">Members</h1>
            <p class="text-[13px] text-white/35 mt-1">Manage organization members, roles, and invitations</p>
        </div>
        <button @click="showInviteModal = true"
                class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl px-4 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-indigo-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Invite User
        </button>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 rounded-xl px-4 py-3 text-green-400 text-[13px]">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[240px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="search" placeholder="Search by name or email..."
                           class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl pl-9 pr-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/25 transition">
                </div>
            </div>
            <select x-model="filterDepartment"
                    class="bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-indigo-500/50 transition min-w-[150px]">
                <option value="">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
            <select x-model="filterRole"
                    class="bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-indigo-500/50 transition min-w-[140px]">
                <option value="">All Roles</option>
                @foreach($roles ?? [] as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            <select x-model="filterStatus"
                    class="bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-indigo-500/50 transition min-w-[130px]">
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
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500/20 to-violet-600/20 flex items-center justify-center text-indigo-400 text-[13px] font-semibold flex-shrink-0">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="text-[13px] font-medium text-white/80">{{ $member->name }}</div>
                                        <div class="text-[12px] text-white/35">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @php $profile = $member->employeeProfiles->first(); @endphp
                                @if($profile && ($profile->designation || $profile->department))
                                    <div class="text-[12px] text-white/60">{{ $profile->designation ?? '-' }}</div>
                                    <div class="text-[11px] text-white/30">{{ $profile->department ?? '' }}</div>
                                @else
                                    <span class="text-[12px] text-white/25">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1.5">
                                    @php $sysRole = $member->pivot->role ?? 'member'; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium
                                        {{ $sysRole === 'owner' ? 'bg-red-500/15 text-red-400' : ($sysRole === 'admin' ? 'bg-violet-500/15 text-violet-400' : 'bg-sky-500/15 text-sky-400') }}">
                                        {{ ucfirst($sysRole) }}
                                    </span>
                                    @foreach($member->organizationRoles as $role)
                                        @if(!$role->is_system)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-indigo-500/15 text-indigo-400">
                                                {{ $role->name }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
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
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('users.show', [$organization, $member]) }}"
                                       class="inline-flex items-center gap-1 border border-white/[0.08] text-white/40 hover:text-white/65 hover:border-white/[0.15] rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                        View
                                    </a>
                                    <a href="{{ route('users.edit', [$organization, $member]) }}"
                                       class="inline-flex items-center gap-1 border border-white/[0.08] text-white/40 hover:text-white/65 hover:border-white/[0.15] rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                        Edit
                                    </a>
                                    @if(($member->employeeProfiles->first()?->status ?? 'active') === 'active')
                                        <form action="{{ route('users.deactivate', [$organization, $member]) }}" method="POST" class="inline"
                                              x-data x-on:submit.prevent="$dispatch('confirm-modal', { title: 'Deactivate Member', message: 'Are you sure you want to deactivate this member?', confirmLabel: 'Deactivate', variant: 'danger', onConfirm: () => $el.submit() })">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 border border-red-500/15 text-red-400/50 hover:text-red-400 hover:border-red-500/30 rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
                                                Deactivate
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('users.activate', [$organization, $member]) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 border border-green-500/15 text-green-400/50 hover:text-green-400 hover:border-green-500/30 rounded-lg px-2.5 py-1.5 text-[11px] font-medium transition">
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
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showInviteModal = false"></div>
        <div class="relative bg-[#111120] border border-white/[0.1] rounded-2xl w-full max-w-md shadow-2xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
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
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Email Address <span class="text-red-400">*</span></label>
                            <input type="email" name="email" required placeholder="user@example.com"
                                   class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/25 transition">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">System Role <span class="text-red-400">*</span></label>
                            <select name="system_role" required class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-indigo-500/50 transition">
                                <option value="member">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Custom Role (Optional)</label>
                            <select name="role_id" class="w-full bg-white/[0.05] border border-white/[0.1] text-white/55 rounded-xl px-3.5 py-2.5 text-[13px] focus:outline-none focus:border-indigo-500/50 transition">
                                <option value="">No custom role</option>
                                @foreach($roles ?? [] as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-white/[0.07] flex items-center justify-end gap-3">
                    <button type="button" @click="showInviteModal = false"
                            class="border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2.5 text-[13px] font-medium transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl px-4 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-indigo-500/20">
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
                if (this.search) {
                    const query = this.search.toLowerCase();
                    if (!name.toLowerCase().includes(query) && !email.toLowerCase().includes(query)) return false;
                }
                if (this.filterDepartment && department !== this.filterDepartment) return false;
                if (this.filterRole && !roleIds.split(',').includes(this.filterRole)) return false;
                if (this.filterStatus && status !== this.filterStatus) return false;
                return true;
            }
        };
    }
</script>

</x-layouts.org-management>
