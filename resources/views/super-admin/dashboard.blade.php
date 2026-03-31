<x-layouts.super-admin title="Dashboard">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {{-- Total Organizations --}}
        <div class="sa-card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: rgba(239,68,68,0.12);">
                    <svg class="w-4.5 h-4.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <span class="text-[10px] uppercase tracking-wider text-white/30 font-medium">Organizations</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($orgCount ?? 0) }}</p>
            <p class="text-xs text-white/30 mt-1">Total registered</p>
        </div>

        {{-- Total Users --}}
        <div class="sa-card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: rgba(59,130,246,0.12);">
                    <svg class="w-4.5 h-4.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                </div>
                <span class="text-[10px] uppercase tracking-wider text-white/30 font-medium">Users</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($userCount ?? 0) }}</p>
            <p class="text-xs text-white/30 mt-1">Total registered</p>
        </div>

        {{-- Active Subscriptions --}}
        <div class="sa-card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: rgba(34,197,94,0.12);">
                    <svg class="w-4.5 h-4.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <span class="text-[10px] uppercase tracking-wider text-white/30 font-medium">Subscriptions</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($activeSubCount ?? 0) }}</p>
            <p class="text-xs text-white/30 mt-1">Currently active</p>
        </div>

        {{-- Products --}}
        <div class="sa-card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: rgba(168,85,247,0.12);">
                    <svg class="w-4.5 h-4.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="text-[10px] uppercase tracking-wider text-white/30 font-medium">Products</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ number_format($productCount ?? 0) }}</p>
            <p class="text-xs text-white/30 mt-1">On platform</p>
        </div>
    </div>

    {{-- Two Column Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Organizations --}}
        <div class="sa-card overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <h3 class="text-white text-sm font-semibold">Recent Organizations</h3>
                <a href="{{ route('super-admin.organizations.index') }}" class="text-red-400 hover:text-red-300 text-xs font-medium transition-colors">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full sa-table">
                    <thead>
                        <tr>
                            <th class="text-left">Name</th>
                            <th class="text-left">Owner</th>
                            <th class="text-center">Members</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrgs ?? [] as $org)
                        <tr>
                            <td class="font-medium text-white/90">{{ $org->name }}</td>
                            <td>{{ $org->owner->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $org->members_count ?? $org->members->count() ?? 0 }}</td>
                            <td class="text-center">
                                @if($org->is_active ?? true)
                                    <span class="sa-badge sa-badge-green">Active</span>
                                @else
                                    <span class="sa-badge sa-badge-red">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right text-white/40 text-xs">{{ $org->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-white/30">No organizations yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Users --}}
        <div class="sa-card overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.06] flex items-center justify-between">
                <h3 class="text-white text-sm font-semibold">Recent Users</h3>
                <a href="{{ route('super-admin.users.index') }}" class="text-red-400 hover:text-red-300 text-xs font-medium transition-colors">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full sa-table">
                    <thead>
                        <tr>
                            <th class="text-left">Name</th>
                            <th class="text-left">Email</th>
                            <th class="text-center">Orgs</th>
                            <th class="text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers ?? [] as $user)
                        <tr>
                            <td class="font-medium text-white/90">{{ $user->name }}</td>
                            <td class="text-white/50">{{ $user->email }}</td>
                            <td class="text-center">{{ $user->organizations_count ?? $user->organizations->count() ?? 0 }}</td>
                            <td class="text-right text-white/40 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-white/30">No users yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</x-layouts.super-admin>
