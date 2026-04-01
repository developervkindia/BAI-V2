<x-layouts.super-admin title="Subscriptions">

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-white">Subscriptions</h1>
            <p class="text-sm text-white/40 mt-0.5">Manage all platform subscriptions</p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="sa-card p-4 mb-6">
        <form method="GET" action="{{ route('super-admin.subscriptions.index') }}" class="flex items-center gap-3">
            <select name="product" class="sa-select" onchange="this.form.submit()">
                <option value="">All Products</option>
                @foreach($products ?? [] as $product)
                    <option value="{{ $product->id }}" {{ request('product') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                @endforeach
            </select>
            <select name="plan" class="sa-select" onchange="this.form.submit()">
                <option value="">All Plans</option>
                <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free</option>
                <option value="pro" {{ request('plan') === 'pro' ? 'selected' : '' }}>Pro</option>
                <option value="enterprise" {{ request('plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
            </select>
            <select name="status" class="sa-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="trial" {{ request('status') === 'trial' ? 'selected' : '' }}>Trial</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="sa-btn-outline">Filter</button>
        </form>
    </div>

    {{-- Subscriptions Table --}}
    <div class="sa-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full sa-table">
                <thead>
                    <tr>
                        <th class="text-left">Organization</th>
                        <th class="text-left">Product</th>
                        <th class="text-center">Plan</th>
                        <th class="text-center">Status</th>
                        <th class="text-left">Start Date</th>
                        <th class="text-left">End Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions ?? [] as $sub)
                    <tr x-data="{ editing: false }">
                        <td>
                            <a href="{{ route('super-admin.organizations.show', $sub->organization) }}" class="font-medium text-white/90 hover:text-red-400 transition-colors">
                                {{ $sub->organization->name ?? 'Unknown' }}
                            </a>
                        </td>
                        <td class="text-white/60">{{ $sub->product->name ?? 'Unknown' }}</td>
                        <td class="text-center">
                            {{-- Display Mode --}}
                            <template x-if="!editing">
                                <span>
                                    @if(($sub->plan ?? 'free') === 'pro')
                                        <span class="sa-badge sa-badge-purple">Pro</span>
                                    @elseif(($sub->plan ?? 'free') === 'enterprise')
                                        <span class="sa-badge sa-badge-yellow">Enterprise</span>
                                    @else
                                        <span class="sa-badge sa-badge-gray">Free</span>
                                    @endif
                                </span>
                            </template>
                            {{-- Edit Mode --}}
                            <template x-if="editing">
                                <form method="POST" action="{{ route('super-admin.subscriptions.update', $sub) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="plan" class="sa-select text-xs">
                                        <option value="free" {{ ($sub->plan ?? 'free') === 'free' ? 'selected' : '' }}>Free</option>
                                        <option value="pro" {{ ($sub->plan ?? '') === 'pro' ? 'selected' : '' }}>Pro</option>
                                        <option value="enterprise" {{ ($sub->plan ?? '') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                    </select>
                            </template>
                        </td>
                        <td class="text-center">
                            <template x-if="!editing">
                                <span>
                                    @if(($sub->status ?? 'active') === 'active')
                                        <span class="sa-badge sa-badge-green">Active</span>
                                    @elseif(($sub->status ?? '') === 'trial')
                                        <span class="sa-badge sa-badge-blue">Trial</span>
                                    @elseif(($sub->status ?? '') === 'expired')
                                        <span class="sa-badge sa-badge-red">Expired</span>
                                    @else
                                        <span class="sa-badge sa-badge-gray">{{ ucfirst($sub->status ?? 'Unknown') }}</span>
                                    @endif
                                </span>
                            </template>
                            <template x-if="editing">
                                <span>
                                    <select name="status" class="sa-select text-xs">
                                        <option value="active" {{ ($sub->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="trial" {{ ($sub->status ?? '') === 'trial' ? 'selected' : '' }}>Trial</option>
                                        <option value="expired" {{ ($sub->status ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="cancelled" {{ ($sub->status ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </span>
                            </template>
                        </td>
                        <td class="text-white/40 text-xs">{{ $sub->starts_at ? $sub->starts_at->format('M d, Y') : 'N/A' }}</td>
                        <td class="text-white/40 text-xs">{{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : 'Never' }}</td>
                        <td>
                            <div class="flex items-center justify-end gap-2">
                                <template x-if="!editing">
                                    <div class="flex items-center gap-2">
                                        <button @click="editing = true" class="sa-btn-outline text-xs px-3 py-1.5">Edit</button>
                                        <form method="POST" action="{{ route('super-admin.subscriptions.destroy', $sub) }}" x-data x-on:submit.prevent="$dispatch('confirm-modal', { title: 'Delete Subscription', message: 'Delete this subscription? This cannot be undone.', confirmLabel: 'Delete', variant: 'danger', onConfirm: () => $el.submit() })">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs px-3 py-1.5 rounded-md border border-red-500/30 text-red-400 hover:bg-red-500/10 transition-colors">Delete</button>
                                        </form>
                                    </div>
                                </template>
                                <template x-if="editing">
                                    <div class="flex items-center gap-2">
                                        <button type="submit" class="sa-btn-red text-xs px-3 py-1.5">Save</button>
                                        </form>
                                        <button @click="editing = false" class="sa-btn-outline text-xs px-3 py-1.5">Cancel</button>
                                    </div>
                                </template>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <p class="text-white/30 text-sm">No subscriptions found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($subscriptions ?? collect(), 'links'))
        <div class="px-5 py-4 border-t border-white/[0.06]">
            {{ $subscriptions->withQueryString()->links() }}
        </div>
        @endif
    </div>

</x-layouts.super-admin>
