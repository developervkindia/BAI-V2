<x-layouts.smartprojects :project="$project" currentView="chat" :canEdit="$canEdit">

<div class="flex flex-col h-[calc(100vh-176px)]"
     x-data="projectChat({{ Js::from(['projectSlug' => $project->slug, 'canEdit' => $canEdit]) }})"
     x-init="loadMessages()">

    {{-- Messages area --}}
    <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3" x-ref="chatArea">
        <template x-for="msg in messages" :key="msg.id">
            <div class="flex gap-3 group">
                <div class="w-7 h-7 rounded-full bg-orange-500/20 text-orange-300 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5"
                     x-text="msg.user?.name?.substring(0,2).toUpperCase()"></div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline gap-2">
                        <span class="text-[12px] font-medium text-white/70" x-text="msg.user?.name"></span>
                        <span class="text-[10px] text-white/20" x-text="msg.created_at"></span>
                        <button x-show="msg.is_mine" @click="deleteMessage(msg.id)"
                            class="opacity-0 group-hover:opacity-100 text-[10px] text-red-400/60 hover:text-red-400 transition-all ml-auto">Delete</button>
                    </div>
                    <p class="text-[13px] text-white/55 mt-0.5 whitespace-pre-wrap" x-text="msg.body"></p>
                </div>
            </div>
        </template>
        <p x-show="messages.length === 0" class="text-center text-[12px] text-white/20 py-8">No messages yet. Start the conversation!</p>
    </div>

    {{-- Input --}}
    @if($canEdit)
    <div class="shrink-0 border-t border-white/[0.06] px-6 py-3">
        <form @submit.prevent="sendMessage()" class="flex gap-2">
            <input type="text" x-model="newMessage" placeholder="Type a message..."
                class="flex-1 px-4 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-[13px] text-white/75 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-orange-500/40"/>
            <button type="submit" :disabled="!newMessage.trim()"
                class="px-4 py-2.5 rounded-xl bg-orange-500 text-white text-[13px] font-medium hover:bg-orange-400 disabled:opacity-30 transition-colors">
                Send
            </button>
        </form>
    </div>
    @endif
</div>

<script>
function projectChat(config) {
    return {
        messages: [],
        newMessage: '',
        async loadMessages() {
            const r = await fetch(`/api/projects/${config.projectSlug}/chat`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }});
            if (r.ok) { const d = await r.json(); this.messages = d.messages || []; this.$nextTick(() => this.scrollBottom()); }
        },
        async sendMessage() {
            if (!this.newMessage.trim()) return;
            const body = this.newMessage.trim();
            this.newMessage = '';
            const r = await fetch(`/api/projects/${config.projectSlug}/chat`, {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ body })
            });
            if (r.ok) { const d = await r.json(); if (d.message) this.messages.push(d.message); this.$nextTick(() => this.scrollBottom()); }
        },
        async deleteMessage(id) {
            await fetch(`/api/project-messages/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }});
            this.messages = this.messages.filter(m => m.id !== id);
        },
        scrollBottom() { if (this.$refs.chatArea) this.$refs.chatArea.scrollTop = this.$refs.chatArea.scrollHeight; }
    };
}
</script>

</x-layouts.smartprojects>
