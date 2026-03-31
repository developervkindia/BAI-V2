@props(['board'])

<div class="group relative rounded-2xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 h-32" x-data="{ menuOpen: false }">
    <a href="{{ route('boards.show', $board) }}" class="absolute inset-0 z-0">
        <div class="absolute inset-0" style="{{ $board->background_style }}"></div>
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
    </a>
    <div class="relative p-4 flex flex-col justify-end h-full text-white pointer-events-none">
        <h3 class="font-bold text-lg truncate drop-shadow">{{ $board->name }}</h3>
        @if($board->workspace)
            <p class="text-white/70 text-sm drop-shadow">{{ $board->workspace->name }}</p>
        @endif
    </div>

    <!-- Star button -->
    <button class="absolute top-3 right-10 opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded hover:bg-white/20 z-10"
        onclick="event.stopPropagation(); fetch('{{ route('boards.star', $board) }}', {method:'POST', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}}).then(r=>r.json()).then(d=>{this.querySelector('svg').classList.toggle('fill-sunny-400', d.starred); this.querySelector('svg').classList.toggle('text-sunny-400', d.starred)})">
        <svg class="w-5 h-5 {{ $board->isStarredBy(auth()->user()) ? 'text-sunny-400 fill-sunny-400' : 'text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
    </button>

    <!-- Options menu -->
    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">
        <button @click.prevent="menuOpen = !menuOpen" class="p-1 rounded hover:bg-white/20 text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
        </button>
        <div x-show="menuOpen" @click.away="menuOpen = false" x-cloak
            class="absolute right-0 mt-1 w-44 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 py-1 z-50">
            <button @click="$dispatch('open-modal', 'edit-board-{{ $board->id }}'); menuOpen = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit Board
            </button>
            <form method="POST" action="{{ route('boards.archive', $board) }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    Archive
                </button>
            </form>
            <form method="POST" action="{{ route('boards.destroy', $board) }}" onsubmit="return confirm('Permanently delete this board and all its data? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-danger-600 hover:bg-danger-50 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Board
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Board Modal -->
    <x-ui.modal name="edit-board-{{ $board->id }}" maxWidth="md">
        <form method="POST" action="{{ route('boards.update', $board) }}" class="p-6 space-y-5">
            @csrf @method('PUT')
            <h2 class="text-xl font-bold text-gray-900 dark:text-white font-heading">Edit Board</h2>
            <x-ui.input label="Board name" name="name" :value="$board->name" required />
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-800 dark:text-gray-100 dark:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/20 transition-all" placeholder="What's this board for?">{{ $board->description }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Visibility</label>
                <select name="visibility" class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-800 dark:text-gray-100 dark:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/20">
                    <option value="private" {{ $board->visibility === 'private' ? 'selected' : '' }}>Private — Only board members</option>
                    <option value="workspace" {{ $board->visibility === 'workspace' ? 'selected' : '' }}>Workspace — All workspace members</option>
                    <option value="public" {{ $board->visibility === 'public' ? 'selected' : '' }}>Public — Anyone with link</option>
                </select>
            </div>
            <x-ui.button type="submit" variant="primary" class="w-full">Save Changes</x-ui.button>
        </form>
    </x-ui.modal>
</div>
