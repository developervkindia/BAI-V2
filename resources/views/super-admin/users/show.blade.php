<x-layouts.super-admin title="User: {{ $user->name }}">

    {{-- User Header --}}
    <div class="sa-card p-6 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center text-xl font-bold shrink-0" style="background: rgba(239,68,68,0.12); color: #ef4444;">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="" class="w-14 h-14 rounded-xl object-cover">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-bold text-white">{{ $user->name }}</h1>
                        @if($user->is_super_admin)
                            <span class="sa-badge sa-badge-red">Super Admin</span>
                        @endif
                    </div>
                    <p class="text-sm text-white/40 mt-0.5">{{ $user->email }}</p>
                    <p class="text-xs text-white/25 mt-1">Joined {{ $user->created_at->format('F d, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('super-admin.users.index') }}" class="sa-btn-outline">Back to List</a>
                @if(!$user->is_super_admin && $user->id !== auth()->id())
                    <form method="POST" action="{{ route('super-admin.impersonate', $user) }}" onsubmit="return confirm('You will be logged in as {{ $user->name }}. Continue?')">
                        @csrf
                        <button type="submit" class="sa-btn-red">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Impersonate
                            </span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Organizations Section --}}
    <div class="sa-card overflow-hidden">
        <div class="px-5 py-4 border-b border-white/[0.06]">
            <h3 class="text-white text-sm font-semibold">Organizations</h3>
        </div>

        @forelse($user->organizations ?? [] as $org)
        <div class="px-5 py-4 border-b border-white/[0.04] flex items-center justify-between hover:bg-white/[0.02] transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold" style="background: rgba(239,68,68,0.08); color: rgba(239,68,68,0.6);">
                    {{ strtoupper(substr($org->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-white/90 text-sm font-medium">{{ $org->name }}</p>
                    <p class="text-white/30 text-xs">{{ $org->slug }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @php $role = $org->pivot->role ?? 'member'; @endphp
                @if($role === 'owner')
                    <span class="sa-badge sa-badge-yellow">Owner</span>
                @elseif($role === 'admin')
                    <span class="sa-badge sa-badge-purple">Admin</span>
                @else
                    <span class="sa-badge sa-badge-gray">Member</span>
                @endif
                <a href="{{ route('super-admin.organizations.show', $org) }}" class="text-red-400 hover:text-red-300 text-xs font-medium transition-colors">
                    View Org
                </a>
            </div>
        </div>
        @empty
        <div class="px-5 py-12 text-center">
            <svg class="w-10 h-10 text-white/10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-white/30 text-sm">This user doesn't belong to any organizations</p>
        </div>
        @endforelse
    </div>

</x-layouts.super-admin>
