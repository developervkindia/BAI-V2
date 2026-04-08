<x-layouts.org-management :organization="$organization" activeTab="general">

    <div class="max-w-2xl space-y-8">

        {{-- Page title --}}
        <div>
            <h1 class="text-[20px] font-bold text-white/85">General Settings</h1>
            <p class="text-[13px] text-white/35 mt-1">Manage your organization's basic information</p>
        </div>

        {{-- General Settings --}}
        @if($organization->isAdmin(auth()->user()))
            <section class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-6">
                <h2 class="text-[13px] font-semibold text-white/55 mb-4">Organization Details</h2>
                <form method="POST" action="{{ route('organizations.update', $organization) }}" class="space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-[11px] font-medium text-white/35 mb-1.5">Organization Name</label>
                        <input type="text" name="name" required value="{{ old('name', $organization->name) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 text-[13px] focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500/50 focus:outline-none transition"/>
                        @error('name')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-white/35 mb-1.5">Description</label>
                        <input type="text" name="description" value="{{ old('description', $organization->description) }}"
                               placeholder="Brief description of your organization"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 text-[13px] placeholder-white/20 focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500/50 focus:outline-none transition"/>
                    </div>
                    <div class="pt-1">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-500/15 border border-indigo-500/25 text-indigo-400 text-[13px] font-medium hover:bg-indigo-500/25 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </section>
        @endif

        {{-- Products / Subscriptions --}}
        <section class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-6">
            <h2 class="text-[13px] font-semibold text-white/55 mb-4">Active Products</h2>
            @php
            $colorMap = [
                'indigo'  => ['bg' => 'bg-indigo-500/20', 'text' => 'text-indigo-400'],
                'sky'     => ['bg' => 'bg-sky-500/20',    'text' => 'text-sky-400'],
                'violet'  => ['bg' => 'bg-violet-500/20', 'text' => 'text-violet-400'],
                'emerald' => ['bg' => 'bg-emerald-500/20','text' => 'text-emerald-400'],
                'amber'   => ['bg' => 'bg-amber-500/20',  'text' => 'text-amber-400'],
                'rose'    => ['bg' => 'bg-rose-500/20',   'text' => 'text-rose-400'],
                'teal'    => ['bg' => 'bg-teal-500/20',   'text' => 'text-teal-400'],
            ];
            @endphp
            <div class="space-y-2">
                @forelse($organization->subscriptions as $sub)
                    @php
                        $def    = $productConfig[$sub->product->key] ?? [];
                        $colors = $colorMap[$def['color'] ?? 'indigo'] ?? $colorMap['indigo'];
                    @endphp
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.04]">
                        <div class="w-8 h-8 rounded-lg {{ $colors['bg'] }} flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 {{ $colors['text'] }}" fill="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $def['icon'] ?? '' }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-white/70">{{ $sub->product->name }}</p>
                            <p class="text-[11px] text-white/30 capitalize">{{ $sub->plan }} plan</p>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full {{ $sub->isActive() ? 'bg-green-500/15 text-green-400' : 'bg-white/5 text-white/30' }}">
                            {{ $sub->isActive() ? 'Active' : ucfirst($sub->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-[13px] text-white/30 px-4">No active products.</p>
                @endforelse
            </div>
        </section>

        {{-- Members overview --}}
        <section class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-[13px] font-semibold text-white/55">Members <span class="text-white/25 font-normal">({{ $organization->members->count() }})</span></h2>
                <a href="{{ route('users.index', $organization) }}" class="text-[11px] text-indigo-400/70 hover:text-indigo-400 font-medium transition-colors">
                    Manage Members &rarr;
                </a>
            </div>
            <div class="space-y-1.5">
                @foreach($organization->members->take(8) as $member)
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/[0.03] transition-colors">
                        <div class="w-7 h-7 rounded-full bg-white/[0.08] text-white/45 text-[10px] font-bold flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($member->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-medium text-white/65 truncate">{{ $member->name }}</p>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/[0.05] text-white/30 capitalize">
                            {{ $member->pivot->role }}
                        </span>
                    </div>
                @endforeach
                @if($organization->members->count() > 8)
                    <a href="{{ route('users.index', $organization) }}" class="block text-center text-[11px] text-white/30 hover:text-white/50 pt-2 transition-colors">
                        View all {{ $organization->members->count() }} members
                    </a>
                @endif
            </div>
        </section>

        {{-- Workspaces --}}
        @if($workspaces->count())
            <section class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-6">
                <h2 class="text-[13px] font-semibold text-white/55 mb-4">BAI Board Workspaces</h2>
                <div class="space-y-1.5">
                    @foreach($workspaces as $ws)
                        <a href="{{ route('workspaces.show', $ws) }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/[0.04] transition-colors group">
                            <div class="w-6 h-6 rounded-md bg-white/[0.06] flex items-center justify-center shrink-0 text-[10px] font-bold text-white/35">
                                {{ strtoupper(substr($ws->name, 0, 1)) }}
                            </div>
                            <span class="text-[12px] text-white/55 group-hover:text-white/75 transition-colors">{{ $ws->name }}</span>
                            <svg class="w-3 h-3 text-white/15 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </div>

</x-layouts.org-management>
