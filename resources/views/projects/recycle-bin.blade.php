<x-layouts.smartprojects :project="$project" currentView="recycle-bin" :canEdit="$canEdit">

<div class="px-6 py-5 max-w-screen-xl mx-auto"
     x-data="recycleBin({{ Js::from(['projectSlug' => $project->slug]) }})"
     x-init="loadItems()">

    <div class="flex items-center justify-between mb-5">
        <h2 class="text-[15px] font-semibold text-white/85">Recycle Bin</h2>
        <button @click="emptyBin()" x-show="items.length > 0"
            class="px-3.5 py-1.5 rounded-lg text-[12px] text-red-400 border border-red-500/20 hover:bg-red-500/10 transition-colors">
            Empty Bin
        </button>
    </div>

    <div class="bg-[#111120] border border-white/[0.07] rounded-2xl overflow-hidden">
        <template x-if="items.length === 0">
            <div class="py-12 text-center text-[12px] text-white/25">Recycle bin is empty</div>
        </template>
        <template x-for="item in items" :key="item.type + item.id">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-white/[0.04] hover:bg-white/[0.02]">
                <span class="px-2 py-0.5 rounded-full text-[9px] font-medium uppercase"
                    :class="{
                        'bg-orange-500/15 text-orange-400': item.type === 'task',
                        'bg-blue-500/15 text-blue-400': item.type === 'list',
                        'bg-green-500/15 text-green-400': item.type === 'milestone',
                    }" x-text="item.type"></span>
                <span class="text-[13px] text-white/65 flex-1 truncate" x-text="item.name"></span>
                <span class="text-[10px] text-white/25" x-text="item.deleted_at"></span>
                <button @click="restoreItem(item)" class="px-3 py-1 rounded-lg text-[11px] text-green-400/70 border border-green-500/20 hover:bg-green-500/10 hover:text-green-400 transition-colors">
                    Restore
                </button>
                <button @click="deleteItem(item)" class="px-3 py-1 rounded-lg text-[11px] text-red-400/70 border border-red-500/20 hover:bg-red-500/10 hover:text-red-400 transition-colors">
                    Delete Forever
                </button>
            </div>
        </template>
    </div>
</div>

<script>
function recycleBin(config) {
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf };
    return {
        items: [],
        async loadItems() {
            const r = await fetch(`/api/projects/${config.projectSlug}/recycle-bin`, { headers });
            if (r.ok) { const d = await r.json(); this.items = d.items || []; }
        },
        async restoreItem(item) {
            const r = await fetch(`/api/projects/${config.projectSlug}/recycle-bin/restore`, { method: 'POST', headers, body: JSON.stringify({ type: item.type, id: item.id }) });
            if (r.ok) this.items = this.items.filter(i => !(i.type === item.type && i.id === item.id));
        },
        async deleteItem(item) {
            if (!confirm('Permanently delete this item?')) return;
            const r = await fetch(`/api/projects/${config.projectSlug}/recycle-bin/delete`, { method: 'DELETE', headers, body: JSON.stringify({ type: item.type, id: item.id }) });
            if (r.ok) this.items = this.items.filter(i => !(i.type === item.type && i.id === item.id));
        },
        async emptyBin() {
            if (!confirm('Permanently delete ALL items in recycle bin?')) return;
            await fetch(`/api/projects/${config.projectSlug}/recycle-bin/empty`, { method: 'DELETE', headers });
            this.items = [];
        }
    };
}
</script>

</x-layouts.smartprojects>
