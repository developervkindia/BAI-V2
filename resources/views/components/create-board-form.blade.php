@props(['workspace', 'modalName' => 'create-board'])

<x-ui.modal :name="$modalName" maxWidth="2xl">
    <form method="POST" action="{{ route('boards.store', $workspace) }}" enctype="multipart/form-data" class="p-6 space-y-6" x-data="{
        selectedTemplate: 'blank',
        bgType: 'color',
        selectedBg: '#1a1a1a',
        imagePreview: null,
        step: 1,
        templates: {
            'blank': { name: 'Blank Board', description: 'Start from scratch.', icon: 'blank', color: 'bg-neutral-700' },
            'agile-sprint': { name: 'Agile Sprint', description: 'Backlog to done workflow.', icon: 'sprint', color: 'bg-indigo-800' },
            'bug-tracking': { name: 'Bug Tracking', description: 'Triage and resolve issues.', icon: 'bug', color: 'bg-red-900' },
            'product-roadmap': { name: 'Product Roadmap', description: 'Plan from idea to release.', icon: 'roadmap', color: 'bg-blue-900' },
            'devops-pipeline': { name: 'DevOps Pipeline', description: 'Infra and deployments.', icon: 'devops', color: 'bg-amber-900' },
            'release-management': { name: 'Release Management', description: 'Coordinate releases.', icon: 'release', color: 'bg-emerald-900' }
        },
        handleImageSelect(e) {
            const file = e.target.files[0];
            if (file) {
                this.bgType = 'image';
                const reader = new FileReader();
                reader.onload = (ev) => { this.imagePreview = ev.target.result; };
                reader.readAsDataURL(file);
            }
        }
    }">
        @csrf
        <input type="hidden" name="template" :value="selectedTemplate">
        <input type="hidden" name="background_type" :value="bgType">
        <input type="hidden" name="background_value" :value="bgType === 'color' ? selectedBg : ''">

        <!-- Step 1: Template -->
        <div x-show="step === 1">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Create Board</h2>
            <p class="text-xs text-gray-500 mb-4">Choose a starting template.</p>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                <template x-for="(tpl, key) in templates" :key="key">
                    <button type="button" @click="selectedTemplate = key"
                        class="text-left p-3 rounded-lg border transition-all"
                        :class="selectedTemplate === key ? 'border-white/40 bg-white/5 dark:border-white/20' : 'border-gray-200 dark:border-white/5 hover:border-gray-300 dark:hover:border-white/10'">
                        <div class="w-8 h-8 rounded flex items-center justify-center mb-2" :class="tpl.color">
                            <svg x-show="tpl.icon === 'blank'" class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <svg x-show="tpl.icon === 'sprint'" class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <svg x-show="tpl.icon === 'bug'" class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19H19a2 2 0 001.75-2.95L13.74 4.39a2 2 0 00-3.48 0L3.33 16.05A2 2 0 005.07 19z"/></svg>
                            <svg x-show="tpl.icon === 'roadmap'" class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            <svg x-show="tpl.icon === 'devops'" class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <svg x-show="tpl.icon === 'release'" class="w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        </div>
                        <h3 class="text-xs font-semibold text-gray-800 dark:text-gray-200" x-text="tpl.name"></h3>
                        <p class="text-[10px] text-gray-500 mt-0.5 line-clamp-1" x-text="tpl.description"></p>
                    </button>
                </template>
            </div>

            <div class="flex justify-end mt-4">
                <button type="button" @click="step = 2" class="px-4 py-2 rounded-lg bg-black dark:bg-white/10 text-white text-xs font-medium hover:bg-neutral-800 dark:hover:bg-white/15 transition-colors">
                    Next
                    <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Details -->
        <div x-show="step === 2" x-cloak>
            <div class="flex items-center gap-2 mb-4">
                <button type="button" @click="step = 1" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-white/5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Board Details</h2>
            </div>

            <!-- Preview -->
            <div class="rounded-xl overflow-hidden mb-4 h-24 relative"
                :style="bgType === 'image' && imagePreview ? 'background-image:url(' + imagePreview + ');background-size:cover;background-position:center' : 'background:' + selectedBg">
                <div class="absolute inset-0 bg-black/20 flex items-end p-3">
                    <div class="flex gap-1.5">
                        <div class="w-12 h-6 bg-white/20 rounded"></div>
                        <div class="w-12 h-6 bg-white/20 rounded"></div>
                        <div class="w-12 h-6 bg-white/20 rounded"></div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <x-ui.input label="Board name" name="name" required placeholder="e.g. Q2 Sprint" autofocus />

                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Background</label>

                    <!-- Solid colors -->
                    <div class="grid grid-cols-8 gap-1.5 mb-3">
                        @foreach(['#1a1a1a', '#1e293b', '#1e3a5f', '#14532d', '#713f12', '#7f1d1d', '#581c87', '#374151', '#0c4a6e', '#064e3b', '#78350f', '#991b1b', '#4c1d95', '#111827', '#0f766e', '#365314'] as $color)
                            <button type="button"
                                @click="bgType = 'color'; selectedBg = '{{ $color }}'; imagePreview = null"
                                class="h-8 rounded transition-all"
                                :class="bgType === 'color' && selectedBg === '{{ $color }}' ? 'ring-2 ring-white/60 ring-offset-1 ring-offset-gray-900 scale-110' : 'hover:scale-105'"
                                style="background: {{ $color }}">
                            </button>
                        @endforeach
                    </div>

                    <!-- Image upload -->
                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-dashed border-gray-300 dark:border-white/10 hover:border-gray-400 dark:hover:border-white/20 cursor-pointer transition-colors"
                        :class="bgType === 'image' ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-500/30' : ''">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="bgType === 'image' ? 'Image selected' : 'Upload background image'"></span>
                        <input type="file" name="background_image" accept="image/*" class="hidden" @change="handleImageSelect($event)" />
                    </label>
                </div>
            </div>

            <div class="flex gap-2 mt-5">
                <button type="button" @click="step = 1" class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-white/10 text-xs font-medium text-gray-600 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5">Back</button>
                <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-black dark:bg-white/10 text-white text-xs font-medium hover:bg-neutral-800 dark:hover:bg-white/15 transition-colors">Create Board</button>
            </div>
        </div>
    </form>
</x-ui.modal>
