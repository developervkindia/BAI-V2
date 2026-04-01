<x-layouts.super-admin title="Organization: {{ $organization->name }}">

    {{-- Organization Header --}}
    <div class="sa-card p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg font-bold" style="background: rgba(239,68,68,0.12); color: #ef4444;">
                    {{ strtoupper(substr($organization->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold text-white">{{ $organization->name }}</h1>
                        @if($organization->is_active ?? true)
                            <span class="sa-badge sa-badge-green">Active</span>
                        @else
                            <span class="sa-badge sa-badge-red">Inactive</span>
                        @endif
                    </div>
                    <p class="text-sm text-white/40 mt-0.5">{{ $organization->slug }}</p>
                    <p class="text-xs text-white/30 mt-1">
                        Owner: <span class="text-white/60">{{ $organization->owner->name ?? 'N/A' }}</span>
                        <span class="text-white/20 mx-1">|</span>
                        {{ $organization->owner->email ?? '' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('super-admin.organizations.index') }}" class="sa-btn-outline">Back to List</a>
                @if($organization->is_active ?? true)
                    <form method="POST" action="{{ route('super-admin.organizations.deactivate', $organization) }}" x-data x-on:submit.prevent="$dispatch('confirm-modal', { title: 'Deactivate Organization', message: 'Are you sure you want to deactivate this organization?', confirmLabel: 'Deactivate', variant: 'danger', onConfirm: () => $el.submit() })">
                        @csrf
                        <button type="submit" class="sa-btn-red" style="background: linear-gradient(135deg, #dc2626, #991b1b);">Deactivate</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('super-admin.organizations.activate', $organization) }}" x-data x-on:submit.prevent="$dispatch('confirm-modal', { title: 'Activate Organization', message: 'Are you sure you want to activate this organization?', confirmLabel: 'Activate', variant: 'warning', onConfirm: () => $el.submit() })">
                        @csrf
                        <button type="submit" class="sa-btn-red" style="background: linear-gradient(135deg, #22c55e, #16a34a);">Activate</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="sa-card p-4">
            <p class="text-[10px] uppercase tracking-wider text-white/30 font-medium mb-1">Members</p>
            <p class="text-2xl font-bold text-white">{{ $organization->members->count() }}</p>
        </div>
        <div class="sa-card p-4">
            <p class="text-[10px] uppercase tracking-wider text-white/30 font-medium mb-1">Projects</p>
            <p class="text-2xl font-bold text-white">{{ $projectsCount }}</p>
        </div>
        <div class="sa-card p-4">
            <p class="text-[10px] uppercase tracking-wider text-white/30 font-medium mb-1">Boards</p>
            <p class="text-2xl font-bold text-white">{{ $boardsCount }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Members Table --}}
        <div class="sa-card overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.06]">
                <h3 class="text-white text-sm font-semibold">Members</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full sa-table">
                    <thead>
                        <tr>
                            <th class="text-left">Name</th>
                            <th class="text-left">Email</th>
                            <th class="text-center">Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organization->members ?? [] as $member)
                        <tr>
                            <td class="font-medium text-white/90">
                                <a href="{{ route('super-admin.users.show', $member) }}" class="hover:text-red-400 transition-colors">
                                    {{ $member->name }}
                                </a>
                            </td>
                            <td class="text-white/50">{{ $member->email }}</td>
                            <td class="text-center">
                                @php $role = $member->pivot->role ?? 'member'; @endphp
                                @if($role === 'owner')
                                    <span class="sa-badge sa-badge-yellow">Owner</span>
                                @elseif($role === 'admin')
                                    <span class="sa-badge sa-badge-purple">Admin</span>
                                @else
                                    <span class="sa-badge sa-badge-gray">Member</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-6 text-white/30">No members</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Subscriptions Table --}}
        <div class="sa-card overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.06]">
                <h3 class="text-white text-sm font-semibold">Subscriptions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full sa-table">
                    <thead>
                        <tr>
                            <th class="text-left">Product</th>
                            <th class="text-center">Plan</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Dates</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organization->subscriptions ?? [] as $sub)
                        <tr>
                            <td class="font-medium text-white/90">{{ $sub->product->name ?? 'Unknown' }}</td>
                            <td class="text-center">
                                @if(($sub->plan ?? 'free') === 'pro')
                                    <span class="sa-badge sa-badge-purple">Pro</span>
                                @elseif(($sub->plan ?? 'free') === 'enterprise')
                                    <span class="sa-badge sa-badge-yellow">Enterprise</span>
                                @else
                                    <span class="sa-badge sa-badge-gray">Free</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($sub->status ?? 'active') === 'active')
                                    <span class="sa-badge sa-badge-green">Active</span>
                                @elseif(($sub->status ?? '') === 'trial')
                                    <span class="sa-badge sa-badge-blue">Trial</span>
                                @else
                                    <span class="sa-badge sa-badge-red">{{ ucfirst($sub->status ?? 'Inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-right text-white/40 text-xs">
                                {{ $sub->starts_at ? $sub->starts_at->format('M d, Y') : 'N/A' }}
                                @if($sub->ends_at)
                                    <br><span class="text-white/20">to {{ $sub->ends_at->format('M d, Y') }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-6 text-white/30">No subscriptions</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add Subscription Form --}}
    <div class="sa-card p-5">
        <h3 class="text-white text-sm font-semibold mb-4">Add Subscription</h3>
        <form method="POST" action="{{ route('super-admin.organizations.add-subscription', $organization) }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="block text-[11px] uppercase tracking-wider text-white/30 font-medium mb-1.5">Product</label>
                <select name="product_id" class="sa-select w-full" required>
                    <option value="">Select product...</option>
                    @foreach($products ?? [] as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-[11px] uppercase tracking-wider text-white/30 font-medium mb-1.5">Plan</label>
                <select name="plan" class="sa-select w-full" required>
                    <option value="free">Free</option>
                    <option value="pro">Pro</option>
                    <option value="enterprise">Enterprise</option>
                </select>
            </div>
            <button type="submit" class="sa-btn-red whitespace-nowrap">Add Subscription</button>
        </form>
    </div>

</x-layouts.super-admin>
