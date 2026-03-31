<x-layouts.super-admin title="Users">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Users</h1>
            <p class="text-sm text-white/40 mt-0.5">Manage all platform users</p>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="sa-card p-4 mb-6">
        <form method="GET" action="{{ route('super-admin.users.index') }}" class="flex items-center gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users by name or email..." class="sa-input w-full">
            </div>
            <button type="submit" class="sa-btn-red">Search</button>
        </form>
    </div>

    {{-- Users Table --}}
    <div class="sa-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full sa-table">
                <thead>
                    <tr>
                        <th class="text-left">User</th>
                        <th class="text-left">Email</th>
                        <th class="text-left">Organizations</th>
                        <th class="text-center">Role</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background: rgba(239,68,68,0.12);">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <span class="text-red-400 text-xs font-medium">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    @endif
                                </div>
                                <span class="font-medium text-white/90">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="text-white/50">{{ $user->email }}</td>
                        <td>
                            <div class="text-white/50 text-sm">
                                @if($user->organizations && $user->organizations->count() > 0)
                                    {{ $user->organizations->pluck('name')->implode(', ') }}
                                @else
                                    <span class="text-white/20">None</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            @if($user->is_super_admin)
                                <span class="sa-badge sa-badge-red">Super Admin</span>
                            @else
                                <span class="sa-badge sa-badge-gray">User</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('super-admin.users.show', $user) }}" class="sa-btn-outline text-xs px-3 py-1.5">View</a>
                                @if(!$user->is_super_admin && $user->id !== auth()->id())
                                    <form method="POST" action="{{ route('super-admin.impersonate', $user) }}" onsubmit="return confirm('You will be logged in as {{ $user->name }}. Continue?')">
                                        @csrf
                                        <button type="submit" class="text-xs px-3 py-1.5 rounded-md border border-yellow-500/30 text-yellow-400 hover:bg-yellow-500/10 transition-colors">
                                            Impersonate
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12">
                            <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            <p class="text-white/30 text-sm">No users found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($users ?? collect(), 'links'))
        <div class="px-5 py-4 border-t border-white/[0.06]">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>

</x-layouts.super-admin>
