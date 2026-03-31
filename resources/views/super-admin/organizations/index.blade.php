<x-layouts.super-admin title="Organizations">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Organizations</h1>
            <p class="text-sm text-white/40 mt-0.5">Manage all registered organizations</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="sa-card p-4 mb-6">
        <form method="GET" action="{{ route('super-admin.organizations.index') }}" class="flex items-center gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search organizations by name, slug, or owner..." class="sa-input w-full">
            </div>
            <select name="status" class="sa-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="sa-btn-red">Search</button>
        </form>
    </div>

    {{-- Organizations Table --}}
    <div class="sa-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full sa-table">
                <thead>
                    <tr>
                        <th class="text-left">Organization</th>
                        <th class="text-left">Owner</th>
                        <th class="text-center">Members</th>
                        <th class="text-left">Subscriptions</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations ?? [] as $org)
                    <tr>
                        <td>
                            <div>
                                <p class="font-medium text-white/90">{{ $org->name }}</p>
                                <p class="text-[11px] text-white/30">{{ $org->slug }}</p>
                            </div>
                        </td>
                        <td>
                            <div>
                                <p class="text-white/70">{{ $org->owner->name ?? 'N/A' }}</p>
                                <p class="text-[11px] text-white/30">{{ $org->owner->email ?? '' }}</p>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="text-white/60">{{ $org->members_count ?? $org->members->count() ?? 0 }}</span>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @forelse($org->subscriptions ?? [] as $sub)
                                    <span class="sa-badge sa-badge-blue">{{ $sub->product->name ?? 'Unknown' }}</span>
                                @empty
                                    <span class="text-white/20 text-xs">None</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="text-center">
                            @if($org->is_active ?? true)
                                <span class="sa-badge sa-badge-green">Active</span>
                            @else
                                <span class="sa-badge sa-badge-red">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('super-admin.organizations.show', $org) }}" class="sa-btn-outline text-xs px-3 py-1.5">View</a>
                                @if($org->is_active ?? true)
                                    <form method="POST" action="{{ route('super-admin.organizations.deactivate', $org) }}" onsubmit="return confirm('Are you sure you want to deactivate this organization?')">
                                        @csrf
                                        <button type="submit" class="text-xs px-3 py-1.5 rounded-md border border-red-500/30 text-red-400 hover:bg-red-500/10 transition-colors">Deactivate</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('super-admin.organizations.activate', $org) }}" onsubmit="return confirm('Are you sure you want to activate this organization?')">
                                        @csrf
                                        <button type="submit" class="text-xs px-3 py-1.5 rounded-md border border-green-500/30 text-green-400 hover:bg-green-500/10 transition-colors">Activate</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12">
                            <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p class="text-white/30 text-sm">No organizations found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($organizations ?? collect(), 'links'))
        <div class="px-5 py-4 border-t border-white/[0.06]">
            {{ $organizations->withQueryString()->links() }}
        </div>
        @endif
    </div>

</x-layouts.super-admin>
