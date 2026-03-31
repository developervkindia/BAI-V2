<x-layouts.app :workspaces="$workspaces ?? []">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Member Directory</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $workspace->name }}</p>
            </div>
            <a href="{{ route('workspaces.show', $workspace) }}" class="text-sm text-primary-600 hover:text-primary-700 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to workspace
            </a>
        </div>

        <!-- Search & Filter -->
        <div class="flex items-center gap-4 mb-6">
            <form method="GET" class="flex-1">
                <input type="text" name="q" value="{{ $query }}" placeholder="Search members by name or email..." class="w-full rounded-xl border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-gray-200" />
            </form>
            <form method="GET">
                <input type="hidden" name="q" value="{{ $query }}" />
                <select name="role" onchange="this.form.submit()" class="rounded-xl border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-sm dark:bg-gray-700 dark:text-gray-200">
                    <option value="">All roles</option>
                    <option value="admin" {{ $roleFilter === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="normal" {{ $roleFilter === 'normal' ? 'selected' : '' }}>Member</option>
                    <option value="observer" {{ $roleFilter === 'observer' ? 'selected' : '' }}>Observer</option>
                </select>
            </form>
        </div>

        <!-- Owner -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-4">
            <div class="flex items-center gap-4 p-4">
                <x-ui.avatar :name="$owner->name" :src="$owner->avatar_url" size="md" />
                <div class="flex-1">
                    <div class="font-semibold text-gray-900 dark:text-white">{{ $owner->name }}</div>
                    <div class="text-sm text-gray-500">{{ $owner->email }}</div>
                </div>
                <span class="px-3 py-1 bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 rounded-full text-xs font-semibold">Owner</span>
            </div>
        </div>

        <!-- Members -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($members as $member)
                <div class="flex items-center gap-4 p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <x-ui.avatar :name="$member->name" :src="$member->avatar_url ?? null" size="md" />
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $member->name }}</div>
                        <div class="text-sm text-gray-500 truncate">{{ $member->email }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                        @if($member->pivot->role === 'admin') bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400
                        @elseif($member->pivot->role === 'observer') bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400
                        @else bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400
                        @endif
                    ">{{ ucfirst($member->pivot->role) }}</span>
                </div>
            @empty
                <div class="p-8 text-center text-gray-400">No members found</div>
            @endforelse
        </div>

        <!-- Groups -->
        @if($groups->isNotEmpty())
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mt-8 mb-4">Member Groups</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($groups as $group)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ $group->name }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($group->members as $m)
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-1 rounded-lg">{{ $m->name }}</span>
                            @endforeach
                            @if($group->members->isEmpty())
                                <span class="text-xs text-gray-400">No members</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
