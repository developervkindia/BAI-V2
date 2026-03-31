<x-layouts.smartprojects :project="$project" currentView="documents" :canEdit="$canEdit">

<div class="px-6 py-5 max-w-screen-xl mx-auto"
     x-data="documentHub({{ Js::from(['projectSlug' => $project->slug, 'canEdit' => $canEdit]) }})"
     x-init="loadRoot()">

    {{-- Breadcrumbs --}}
    <div class="flex items-center gap-2 mb-4">
        <button @click="navigateTo(null)" class="text-[12px] text-orange-400/70 hover:text-orange-400 transition-colors">Documents</button>
        <template x-for="crumb in breadcrumbs" :key="crumb.id">
            <div class="flex items-center gap-2">
                <span class="text-white/15">/</span>
                <button @click="navigateTo(crumb.id)" class="text-[12px] text-white/50 hover:text-white/75 transition-colors" x-text="crumb.name"></button>
            </div>
        </template>
    </div>

    {{-- Actions bar --}}
    <div class="flex items-center gap-2 mb-4" x-show="canEdit">
        <button @click="showNewFolder = true" class="px-3 py-1.5 rounded-lg text-[12px] text-white/50 border border-white/[0.1] hover:bg-white/[0.05] hover:text-white/70 transition-colors">
            + New Folder
        </button>
        <label class="px-3 py-1.5 rounded-lg text-[12px] text-white bg-orange-500 hover:bg-orange-400 cursor-pointer transition-colors">
            Upload File
            <input type="file" class="sr-only" @change="uploadFile($event)" multiple>
        </label>
    </div>

    {{-- New folder input --}}
    <div x-show="showNewFolder" x-cloak class="mb-4 flex gap-2">
        <input type="text" x-model="newFolderName" placeholder="Folder name..."
            @keydown.enter="createFolder()" @keydown.escape="showNewFolder = false"
            class="px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-[12px] text-white/70 focus:outline-none focus:ring-1 focus:ring-orange-500/40 w-48"/>
        <button @click="createFolder()" class="px-3 py-1.5 rounded-lg text-[11px] bg-orange-500/20 text-orange-400">Create</button>
    </div>

    {{-- Content grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        {{-- Folders --}}
        <template x-for="folder in folders" :key="'f' + folder.id">
            <div @click="navigateTo(folder.id)"
                 class="bg-[#111120] border border-white/[0.07] rounded-xl p-4 cursor-pointer hover:border-orange-500/20 hover:bg-orange-500/[0.03] transition-all group">
                <div class="flex items-center gap-2.5">
                    <svg class="w-8 h-8 text-orange-400/50 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2 6a2 2 0 012-2h5l2 2h9a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/>
                    </svg>
                    <span class="text-[12px] text-white/65 truncate" x-text="folder.name"></span>
                </div>
            </div>
        </template>

        {{-- Files --}}
        <template x-for="file in files" :key="'a' + file.id">
            <div class="bg-[#111120] border border-white/[0.07] rounded-xl p-4 group">
                <div class="flex items-center gap-2.5 mb-2">
                    <svg class="w-6 h-6 text-white/25 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-[11px] text-white/55 truncate flex-1" x-text="file.filename"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[9px] text-white/25" x-text="file.size_fmt"></span>
                    <a :href="file.url" target="_blank" class="text-[10px] text-orange-400/60 hover:text-orange-400 transition-colors">Download</a>
                </div>
            </div>
        </template>
    </div>

    <p x-show="folders.length === 0 && files.length === 0" class="text-center text-[12px] text-white/20 py-12">
        No files or folders yet
    </p>
</div>

<script>
function documentHub(config) {
    return {
        folders: [], files: [], breadcrumbs: [], currentFolderId: null,
        showNewFolder: false, newFolderName: '', canEdit: config.canEdit,
        async loadRoot() { await this.loadContents(null); },
        async loadContents(folderId) {
            const url = folderId ? `/api/project-folders/${folderId}` : `/api/projects/${config.projectSlug}/documents`;
            const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }});
            if (r.ok) {
                const d = await r.json();
                this.folders = d.folders || [];
                this.files = d.files || d.attachments || [];
                this.currentFolderId = folderId;
            }
        },
        navigateTo(folderId) {
            if (folderId === null) { this.breadcrumbs = []; this.loadContents(null); return; }
            const existing = this.breadcrumbs.findIndex(b => b.id === folderId);
            if (existing !== -1) { this.breadcrumbs = this.breadcrumbs.slice(0, existing + 1); }
            else {
                const folder = this.folders.find(f => f.id === folderId);
                if (folder) this.breadcrumbs.push({ id: folder.id, name: folder.name });
            }
            this.loadContents(folderId);
        },
        async createFolder() {
            if (!this.newFolderName.trim()) return;
            const r = await fetch(`/api/projects/${config.projectSlug}/folders`, {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ name: this.newFolderName.trim(), parent_id: this.currentFolderId })
            });
            if (r.ok) { const d = await r.json(); if (d.folder) this.folders.push(d.folder); }
            this.newFolderName = ''; this.showNewFolder = false;
        },
        async uploadFile(event) {
            for (const file of event.target.files) {
                const fd = new FormData(); fd.append('file', file);
                if (this.currentFolderId) fd.append('project_folder_id', this.currentFolderId);
                const r = await fetch(`/api/projects/${config.projectSlug}/documents/upload`, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: fd
                });
                if (r.ok) { const d = await r.json(); if (d.file) this.files.push(d.file); }
            }
            event.target.value = '';
        }
    };
}
</script>

</x-layouts.smartprojects>
