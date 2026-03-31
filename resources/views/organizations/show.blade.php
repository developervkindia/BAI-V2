<x-layouts.app :title="$organization->name . ' — Settings'" :workspaces="collect()">
    <div class="max-w-3xl mx-auto space-y-8">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('hub') }}" class="p-1.5 rounded-lg hover:bg-white/5 text-white/30 hover:text-white/60 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-xl font-bold text-white/80">{{ $organization->name }}</h1>
            </div>
        </div>

        {{-- General Settings --}}
        @if($organization->isAdmin(auth()->user()))
            <section class="bg-white/[0.03] border border-white/5 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-white/60 mb-4">General</h2>
                <form method="POST" action="{{ route('organizations.update', $organization) }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-white/40 mb-1.5">Name</label>
                        <input type="text" name="name" required value="{{ old('name', $organization->name) }}"
                               class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white/80 text-sm focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"/>
                        @error('name')<p class="text-xs text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-white/40 mb-1.5">Description</label>
                        <input type="text" name="description" value="{{ old('description', $organization->description) }}"
                               placeholder="Optional"
                               class="w-full px-3 py-2 rounded-lg bg-white/5 border border-white/10 text-white/80 text-sm focus:ring-1 focus:ring-indigo-500/50 focus:outline-none"/>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-white/10 text-white/70 hover:bg-white/15 text-sm font-medium transition-colors">
                        Save Changes
                    </button>
                </form>
            </section>
        @endif

        {{-- Admin Panel --}}
        @if($organization->isAdmin(auth()->user()))
            <section class="bg-white/[0.03] border border-white/5 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-white/60 mb-4">Admin Panel</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a href="{{ route('users.index', $organization) }}"
                       class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:border-orange-500/30 hover:bg-orange-500/[0.04] transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[13px] font-semibold text-white/75 group-hover:text-white/90">User Management</div>
                            <div class="text-[11px] text-white/30">Manage team members & profiles</div>
                        </div>
                    </a>
                    <a href="{{ route('roles.index', $organization) }}"
                       class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:border-orange-500/30 hover:bg-orange-500/[0.04] transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[13px] font-semibold text-white/75 group-hover:text-white/90">Roles & Permissions</div>
                            <div class="text-[11px] text-white/30">Manage access control</div>
                        </div>
                    </a>
                    <a href="{{ route('profile.full') }}"
                       class="flex items-center gap-3 p-4 rounded-xl bg-white/[0.03] border border-white/[0.06] hover:border-orange-500/30 hover:bg-orange-500/[0.04] transition-all group">
                        <div class="w-10 h-10 rounded-xl bg-sky-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-[13px] font-semibold text-white/75 group-hover:text-white/90">My Profile</div>
                            <div class="text-[11px] text-white/30">View & edit your details</div>
                        </div>
                    </a>
                </div>
            </section>
        @endif

        {{-- Products / Subscriptions --}}
        <section class="bg-white/[0.03] border border-white/5 rounded-2xl p-6">
            <h2 class="text-sm font-semibold text-white/60 mb-4">Products</h2>
            @php
            $colorMap = [
                'indigo'  => ['bg' => 'bg-indigo-500/20', 'text' => 'text-indigo-400'],
                'sky'     => ['bg' => 'bg-sky-500/20',    'text' => 'text-sky-400'],
                'violet'  => ['bg' => 'bg-violet-500/20', 'text' => 'text-violet-400'],
                'emerald' => ['bg' => 'bg-emerald-500/20','text' => 'text-emerald-400'],
            ];
            @endphp
            <div class="space-y-2">
                @forelse($organization->subscriptions as $sub)
                    @php
                        $def    = $productConfig[$sub->product->key] ?? [];
                        $colors = $colorMap[$def['color'] ?? 'indigo'] ?? $colorMap['indigo'];
                    @endphp
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/[0.03]">
                        <div class="w-8 h-8 rounded-lg {{ $colors['bg'] }} flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 {{ $colors['text'] }}" fill="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $def['icon'] ?? '' }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white/70">{{ $sub->product->name }}</p>
                            <p class="text-xs text-white/30 capitalize">{{ $sub->plan }} · {{ $sub->status }}</p>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full {{ $sub->isActive() ? 'bg-green-500/15 text-green-400' : 'bg-white/5 text-white/30' }}">
                            {{ $sub->isActive() ? 'Active' : ucfirst($sub->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-white/30 px-4">No active products.</p>
                @endforelse
            </div>
        </section>

        {{-- Members --}}
        <section class="bg-white/[0.03] border border-white/5 rounded-2xl p-6">
            <h2 class="text-sm font-semibold text-white/60 mb-4">Members <span class="text-white/30 font-normal">({{ $organization->members->count() }})</span></h2>
            <div class="space-y-2">
                @foreach($organization->members as $member)
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/[0.03] transition-colors">
                        <div class="w-8 h-8 rounded-full bg-white/10 text-white/50 text-xs font-bold flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white/70 truncate">{{ $member->name }}</p>
                            <p class="text-xs text-white/30 truncate">{{ $member->email }}</p>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/5 text-white/30 capitalize">
                            {{ $member->pivot->role }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Workspaces (SmartBoard) --}}
        @if($workspaces->count())
            <section class="bg-white/[0.03] border border-white/5 rounded-2xl p-6">
                <h2 class="text-sm font-semibold text-white/60 mb-4">BAI Board Workspaces</h2>
                <div class="space-y-2">
                    @foreach($workspaces as $ws)
                        <a href="{{ route('workspaces.show', $ws) }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-white/5 transition-colors group">
                            <div class="w-7 h-7 rounded-lg bg-white/5 flex items-center justify-center shrink-0 text-xs font-bold text-white/40">
                                {{ strtoupper(substr($ws->name, 0, 1)) }}
                            </div>
                            <span class="text-sm text-white/60 group-hover:text-white/80 transition-colors">{{ $ws->name }}</span>
                            <svg class="w-3.5 h-3.5 text-white/20 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</x-layouts.app>
