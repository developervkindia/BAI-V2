<x-layouts.smartboard title="Dashboard" :workspaces="$workspaces">
    <div class="space-y-8" x-data>
        <!-- Welcome Header -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-heading">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Here's an overview of your boards and workspaces.</p>
        </div>

        <!-- Starred Boards -->
        @if($starredBoards->count())
            <section>
                <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    <svg class="w-5 h-5 text-sunny-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Starred Boards
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($starredBoards as $board)
                        <x-board-card :board="$board" />
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Invited Boards (from other people's workspaces) -->
        @if($invitedBoards->count())
            <section>
                <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Shared with Me
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($invitedBoards as $board)
                        <x-board-card :board="$board" />
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Workspaces -->
        @forelse($workspaces as $workspace)
            <section>
                <div class="flex items-center justify-between mb-4">
                    <a href="{{ route('workspaces.show', $workspace) }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white font-bold flex items-center justify-center text-lg">
                            {{ strtoupper(substr($workspace->name, 0, 1)) }}
                        </div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 group-hover:text-primary-600 transition-colors">{{ $workspace->name }}</h2>
                    </a>
                    <div class="flex items-center gap-2">
                        <x-ui.button variant="ghost" size="sm" href="{{ route('workspaces.show', $workspace) }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </x-ui.button>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($workspace->boards->where('is_archived', false) as $board)
                        <x-board-card :board="$board" />
                    @endforeach
                    <!-- Create new board -->
                    <button
                        @click="$dispatch('open-modal', 'create-board-{{ $workspace->id }}')"
                        class="border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-2xl flex items-center justify-center h-32 transition-all cursor-pointer group"
                    >
                        <div class="text-center">
                            <svg class="w-8 h-8 text-gray-400 group-hover:text-primary-500 mx-auto mb-1 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span class="text-sm text-gray-500 group-hover:text-primary-600 font-medium transition-colors">Create board</span>
                        </div>
                    </button>
                </div>

                <!-- Create Board Modal with Templates -->
                <x-create-board-form :workspace="$workspace" :modalName="'create-board-' . $workspace->id" />
            </section>
        @empty
            <!-- No workspaces -->
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <h3 class="text-xl font-bold text-gray-600 dark:text-gray-400 mb-2">No workspaces yet</h3>
                <p class="text-gray-400 dark:text-gray-500 mb-6 max-w-md mx-auto">Create your first workspace to start organizing your projects into boards.</p>
                <x-ui.button variant="primary" size="lg" @click="$dispatch('open-modal', 'create-workspace')">
                    Create Your First Workspace
                </x-ui.button>
            </div>
        @endforelse

        <!-- Create Workspace Modal -->
        <x-ui.modal name="create-workspace" maxWidth="md">
            <form method="POST" action="{{ route('workspaces.store') }}" class="p-6 space-y-5">
                @csrf
                <h2 class="text-xl font-bold text-gray-900 dark:text-white font-heading">Create Workspace</h2>
                <x-ui.input label="Workspace name" name="name" required placeholder="e.g. My Team" />
                <x-ui.input label="Description (optional)" name="description" placeholder="What's this workspace for?" />
                <x-ui.button type="submit" variant="primary" class="w-full">Create Workspace</x-ui.button>
            </form>
        </x-ui.modal>
    </div>
</x-layouts.smartboard>
