<x-layouts.app title="{{ $workspace->name }}" :workspaces="$workspaces">
    <div class="space-y-6" x-data="{ activeTab: 'boards' }">
        <!-- Workspace Header -->
        <div class="bg-gradient-to-r from-primary-600 to-secondary-500 rounded-2xl p-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center text-3xl font-bold">
                    {{ strtoupper(substr($workspace->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold font-heading">{{ $workspace->name }}</h1>
                    @if($workspace->description)
                        <p class="text-white/80 mt-1">{{ $workspace->description }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex -space-x-2">
                        @foreach($workspace->members->take(5) as $member)
                            <x-ui.avatar :name="$member->name" :src="$member->avatar_url" size="sm" />
                        @endforeach
                    </div>
                    @if($workspace->isAdmin(auth()->user()))
                        <button @click="activeTab = 'members'" class="px-3 py-1.5 rounded-lg bg-white/20 hover:bg-white/30 text-sm font-medium transition-colors">
                            Invite
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700">
            @foreach(['boards' => 'Boards', 'members' => 'Members', 'settings' => 'Settings'] as $tab => $label)
                <button
                    @click="activeTab = '{{ $tab }}'"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition-colors"
                    :class="activeTab === '{{ $tab }}' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                >{{ $label }}</button>
            @endforeach
        </div>

        <!-- Boards Tab -->
        <div x-show="activeTab === 'boards'">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($workspace->boards->where('is_archived', false) as $board)
                    <x-board-card :board="$board" />
                @endforeach
                <button
                    @click="$dispatch('open-modal', 'create-board-ws')"
                    class="border-2 border-dashed border-gray-300 hover:border-primary-400 hover:bg-primary-50 rounded-2xl flex items-center justify-center h-32 transition-all cursor-pointer group"
                >
                    <div class="text-center">
                        <svg class="w-8 h-8 text-gray-400 group-hover:text-primary-500 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span class="text-sm text-gray-500 group-hover:text-primary-600 font-medium">Create board</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Members Tab -->
        <div x-show="activeTab === 'members'" x-cloak class="max-w-3xl space-y-4">
            @if($workspace->isAdmin(auth()->user()))
                <form method="POST" action="{{ route('workspace-members.store', $workspace) }}" class="flex gap-3 items-end bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700">
                    @csrf
                    <div class="flex-1">
                        <x-ui.input label="Invite by email" type="email" name="email" required placeholder="colleague@example.com" :error="$errors->first('email')" />
                    </div>
                    <select name="role" class="rounded-xl border-2 border-gray-200 px-3 py-3 text-sm">
                        <option value="normal">Normal</option>
                        <option value="admin">Admin</option>
                    </select>
                    <x-ui.button type="submit" variant="primary">Invite</x-ui.button>
                </form>
            @endif

            <div class="space-y-2">
                <!-- Owner -->
                <div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <x-ui.avatar :name="$workspace->owner->name" :src="$workspace->owner->avatar_url" size="md" />
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">{{ $workspace->owner->name }}</p>
                        <p class="text-sm text-gray-500">{{ $workspace->owner->email }}</p>
                    </div>
                    <x-ui.badge color="violet">Owner</x-ui.badge>
                </div>

                @foreach($workspace->members->where('id', '!=', $workspace->owner_id) as $member)
                    <div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                        <x-ui.avatar :name="$member->name" :src="$member->avatar_url" size="md" />
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $member->name }}</p>
                            <p class="text-sm text-gray-500">{{ $member->email }}</p>
                        </div>
                        <x-ui.badge color="{{ $member->pivot->role === 'admin' ? 'violet' : 'gray' }}">{{ ucfirst($member->pivot->role) }}</x-ui.badge>
                        @if($workspace->isAdmin(auth()->user()))
                            <form method="POST" action="{{ route('workspace-members.destroy', [$workspace, $member]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-danger-500 hover:text-danger-700 text-sm">Remove</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Settings Tab -->
        <div x-show="activeTab === 'settings'" x-cloak class="max-w-2xl">
            @if($workspace->isAdmin(auth()->user()))
                <form method="POST" action="{{ route('workspaces.update', $workspace) }}" class="space-y-5 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700">
                    @csrf @method('PUT')
                    <x-ui.input label="Workspace name" name="name" :value="$workspace->name" required />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                        <textarea name="description" rows="3" class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-800 dark:text-gray-100 dark:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/20 transition-all">{{ $workspace->description }}</textarea>
                    </div>
                    <x-ui.button type="submit" variant="primary">Save Changes</x-ui.button>
                </form>

                @if($workspace->owner_id === auth()->id())
                    <div class="mt-8 p-6 bg-danger-50 dark:bg-danger-900/20 rounded-2xl border border-danger-200 dark:border-danger-800">
                        <h3 class="text-lg font-bold text-danger-700 dark:text-danger-400 mb-2">Danger Zone</h3>
                        <p class="text-sm text-danger-600 dark:text-danger-400 mb-4">Deleting this workspace will permanently remove all boards and data.</p>
                        <form method="POST" action="{{ route('workspaces.destroy', $workspace) }}" onsubmit="return confirm('Are you sure? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <x-ui.button type="submit" variant="danger" size="sm">Delete Workspace</x-ui.button>
                        </form>
                    </div>
                @endif
            @else
                <p class="text-gray-500">Only workspace admins can manage settings.</p>
            @endif
        </div>

        <!-- Create Board Modal with Templates -->
        <x-create-board-form :workspace="$workspace" modalName="create-board-ws" />
    </div>
</x-layouts.app>
