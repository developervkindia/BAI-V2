<!-- Board Menu Sidebar -->
<div
    x-data="{
        open: false,
        panel: 'menu',
        members: [],
        inviteEmail: '',
        inviteRole: 'normal',
        inviteError: '',
        inviteSuccess: '',
        inviteLoading: false,
        loadingMembers: false,
        chatMessages: [],
        chatBody: '',
        chatLoading: false,
        chatHasMore: false,
        chatUnread: 0,
        _chatPollTimer: null,

        init() {
            this._startPolling();
        },

        _startPolling() {
            clearInterval(this._chatPollTimer);
            const interval = (this.panel === 'chat' && this.open) ? 1000 : 5000;
            this._chatPollTimer = setInterval(() => this.pollChat(), interval);
        },

        async pollChat() {
            if (!this.chatMessages.length && !(this.panel === 'chat' && this.open)) return;
            try {
                const res = await fetch('/api/boards/{{ $board->id }}/chat', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                if (!res.ok) return;
                const data = await res.json();
                const oldCount = this.chatMessages.length;
                const oldLatestId = this.chatMessages.length ? this.chatMessages[this.chatMessages.length - 1].id : 0;
                this.chatMessages = data.messages;
                const newLatestId = this.chatMessages.length ? this.chatMessages[this.chatMessages.length - 1].id : 0;
                if (newLatestId > oldLatestId) {
                    if (this.panel === 'chat' && this.open) {
                        this.$nextTick(() => { const el = document.getElementById('chat-scroll'); if (el) el.scrollTop = el.scrollHeight; });
                    } else {
                        const newMsgCount = this.chatMessages.filter(m => m.id > oldLatestId).length;
                        this.chatUnread += newMsgCount;
                    }
                }
            } catch(e) {}
        },

        async loadMembers() {
            this.loadingMembers = true;
            try {
                const res = await fetch('/api/boards/{{ $board->id }}/members', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                this.members = await res.json();
            } catch(e) {}
            this.loadingMembers = false;
        },

        async inviteMember() {
            this.inviteError = '';
            this.inviteLoading = true;
            try {
                const res = await fetch('/api/boards/{{ $board->id }}/members', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ email: this.inviteEmail, role: this.inviteRole })
                });
                const data = await res.json();
                if (!res.ok) {
                    this.inviteError = data.error || 'Failed to invite member.';
                } else {
                    this.members.push(data.member);
                    this.inviteEmail = '';
                    this.inviteRole = 'normal';
                    this.inviteSuccess = data.message || 'Invited successfully!';
                    setTimeout(() => this.inviteSuccess = '', 5000);
                }
            } catch(e) {
                this.inviteError = 'Something went wrong.';
            }
            this.inviteLoading = false;
        },

        async changeRole(memberId, newRole) {
            await fetch(`/api/boards/{{ $board->id }}/members/${memberId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ role: newRole })
            });
            const m = this.members.find(m => m.id === memberId);
            if (m) m.role = newRole;
        },

        async removeMember(memberId) {
            if (!confirm('Remove this member from the board?')) return;
            const res = await fetch(`/api/boards/{{ $board->id }}/members/${memberId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            const data = await res.json();
            if (data.error) { alert(data.error); return; }
            this.members = this.members.filter(m => m.id !== memberId);
        },

        async cancelInvite(inviteId) {
            if (!confirm('Cancel this invitation?')) return;
            await fetch(`/api/boards/{{ $board->id }}/invitations/${inviteId}/cancel`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            this.members = this.members.filter(m => m.invite_id !== inviteId);
        },

        async resendInvite(inviteId) {
            const res = await fetch(`/api/boards/{{ $board->id }}/invitations/${inviteId}/resend`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            const data = await res.json();
            if (data.error) { alert(data.error); return; }
            this.inviteSuccess = data.message || 'Invitation resent!';
            setTimeout(() => this.inviteSuccess = '', 4000);
        },
        async loadChat() {
            this.chatLoading = true;
            try {
                const res = await fetch('/api/boards/{{ $board->id }}/chat', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                const data = await res.json();
                this.chatMessages = data.messages;
                this.chatHasMore = data.has_more;
                this.chatUnread = 0;
                this.$nextTick(() => { const el = document.getElementById('chat-scroll'); if (el) el.scrollTop = el.scrollHeight; });
            } catch(e) {}
            this.chatLoading = false;
        },

        async loadOlderChat() {
            if (!this.chatMessages.length) return;
            const oldest = this.chatMessages[0];
            try {
                const res = await fetch(`/api/boards/{{ $board->id }}/chat?before=${oldest.id}`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                const data = await res.json();
                this.chatMessages = [...data.messages, ...this.chatMessages];
                this.chatHasMore = data.has_more;
            } catch(e) {}
        },

        async sendChat() {
            if (!this.chatBody.trim()) return;
            const body = this.chatBody;
            this.chatBody = '';
            try {
                const res = await fetch('/api/boards/{{ $board->id }}/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ body })
                });
                if (res.ok) {
                    const msg = await res.json();
                    this.chatMessages.push(msg);
                    this.$nextTick(() => { const el = document.getElementById('chat-scroll'); if (el) el.scrollTop = el.scrollHeight; });
                }
            } catch(e) { this.chatBody = body; }
        },

        async deleteChat(msgId) {
            await fetch(`/api/board-messages/${msgId}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
            });
            this.chatMessages = this.chatMessages.filter(m => m.id !== msgId);
        },

        receiveChatMessage(msg) {
            if (!this.chatMessages.find(m => m.id === msg.id)) {
                this.chatMessages.push(msg);
                if (this.panel === 'chat' && this.open) {
                    this.$nextTick(() => { const el = document.getElementById('chat-scroll'); if (el) el.scrollTop = el.scrollHeight; });
                } else {
                    this.chatUnread++;
                }
            }
        },

        removeChatMessage(msgId) {
            this.chatMessages = this.chatMessages.filter(m => m.id !== msgId);
        }
    }"
    @open-board-menu.window="open = true; panel = 'menu'; _startPolling()"
    @open-board-members.window="open = true; panel = 'members'; loadMembers(); _startPolling()"
    @open-board-chat.window="open = true; panel = 'chat'; loadChat(); _startPolling()"
    @chat-incoming.window="receiveChatMessage($event.detail)"
    @chat-deleted.window="removeChatMessage($event.detail.id)"
>
    <!-- Backdrop -->
    <div x-show="open" style="display:none" @click="open = false; _startPolling()" class="fixed inset-0 lg:left-[220px] bg-black/40 z-40"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Sidebar -->
    <div x-show="open" style="display:none" class="fixed top-0 right-0 bottom-0 w-80 bg-[#0E0E1C] shadow-2xl border-l border-white/[0.06] z-50 flex flex-col"
        :class="panel === 'chat' ? '' : 'overflow-y-auto scrollbar-thin'"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

        <!-- ===== MENU PANEL ===== -->
        <div x-show="panel === 'menu'">
            <div class="flex items-center justify-between p-4 border-b border-white/5">
                <h2 class="font-semibold text-white/80 text-sm">Menu</h2>
                <button @click="open = false" class="p-1.5 rounded-lg hover:bg-white/10 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-3 space-y-1">
                <!-- Members -->
                <button @click="panel = 'members'; loadMembers()" class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-white/5 text-sm text-white/60 flex items-center gap-3 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Members
                    <span class="ml-auto text-xs text-white/30 bg-white/5 px-2 py-0.5 rounded-full">{{ $board->members->count() }}</span>
                </button>
                <!-- Chat -->
                <button @click="panel = 'chat'; loadChat()" class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-white/5 text-sm text-white/60 flex items-center gap-3 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Board Chat
                    <span x-show="chatUnread > 0" x-text="chatUnread" class="ml-auto text-[10px] font-bold text-white bg-red-500 px-1.5 py-0.5 rounded-full min-w-[18px] text-center"></span>
                </button>
                <!-- Edit Board -->
                <button @click="panel = 'edit'" class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-white/5 text-sm text-white/60 flex items-center gap-3 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Board
                </button>
                <!-- Background -->
                <button @click="panel = 'background'" class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-white/5 text-sm text-white/60 flex items-center gap-3 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Change Background
                </button>
                <hr class="my-2 border-white/5">
                <form method="POST" action="{{ route('boards.archive', $board) }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-white/5 text-sm text-white/60 flex items-center gap-3 transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        Archive Board
                    </button>
                </form>
                <form method="POST" action="{{ route('boards.destroy', $board) }}" onsubmit="return confirm('Permanently delete this board and all its data?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full text-left px-4 py-2.5 rounded-lg hover:bg-red-500/10 text-sm text-red-400 flex items-center gap-3 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Board
                    </button>
                </form>
            </div>
        </div>

        <!-- ===== MEMBERS PANEL ===== -->
        <div x-show="panel === 'members'" style="display:none">
            <div class="flex items-center gap-2 p-4 border-b border-white/5">
                <button @click="panel = 'menu'" class="p-1.5 rounded-lg hover:bg-white/10 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h2 class="font-semibold text-white/80">Board Members</h2>
            </div>

            <div class="p-4 space-y-4">
                <!-- Invite Form -->
                <div class="bg-white/5 rounded-xl p-4 border border-white/5">
                    <h3 class="text-sm font-semibold text-white/70 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Invite to Board
                    </h3>
                    <div class="space-y-2">
                        <input
                            type="email"
                            x-model="inviteEmail"
                            placeholder="Enter email address..."
                            @keydown.enter="inviteMember()"
                            class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white/90 placeholder-white/30 focus:border-white/20 focus:ring-1 focus:ring-white/10 transition-all"
                        />
                        <div class="flex gap-2">
                            <select x-model="inviteRole" class="flex-1 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/70 focus:border-white/20">
                                <option value="normal">Member</option>
                                <option value="admin">Admin</option>
                            </select>
                            <button
                                @click="inviteMember()"
                                :disabled="inviteLoading || !inviteEmail.trim()"
                                class="px-4 py-2 rounded-lg bg-white/10 text-white text-sm font-semibold hover:bg-white/15 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <template x-if="inviteLoading">
                                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                </template>
                                Invite
                            </button>
                        </div>
                        <!-- Success message -->
                        <p x-show="inviteSuccess" x-text="inviteSuccess" x-transition class="text-sm text-success-600 mt-1 flex items-center gap-1">
                        </p>
                        <!-- Error message -->
                        <p x-show="inviteError" x-text="inviteError" class="text-sm text-danger-500 mt-1"></p>
                    </div>
                </div>

                <!-- Members List -->
                <div class="space-y-1">
                    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        Board members (<span x-text="members.length"></span>)
                    </h3>

                    <!-- Loading skeleton -->
                    <template x-if="loadingMembers">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 animate-pulse" x-for="i in 3" :key="i">
                                <div class="w-10 h-10 rounded-full bg-white/10"></div>
                                <div class="flex-1"><div class="h-3 bg-white/10 rounded w-24 mb-1"></div><div class="h-2 bg-gray-100 dark:bg-gray-800 rounded w-32"></div></div>
                            </div>
                        </div>
                    </template>

                    <!-- Member rows -->
                    <template x-if="!loadingMembers">
                        <div class="space-y-1">
                            <template x-for="member in members" :key="member.id">
                                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-white/5 transition-colors group">
                                    <!-- Avatar -->
                                    <div class="shrink-0">
                                        <template x-if="member.avatar_url">
                                            <img :src="member.avatar_url" :alt="member.name" class="w-10 h-10 rounded-full object-cover ring-1 ring-neutral-900" />
                                        </template>
                                        <template x-if="!member.avatar_url && member.type === 'member'">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white font-bold flex items-center justify-center text-sm ring-1 ring-neutral-900" x-text="member.name.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase()"></div>
                                        </template>
                                        <template x-if="member.type === 'pending'">
                                            <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center ring-1 ring-neutral-900">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate" :class="member.type === 'pending' ? 'text-white/40' : 'text-white/80'" x-text="member.name"></p>
                                        <template x-if="member.type === 'pending'">
                                            <div class="flex items-center gap-1.5">
                                                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-sunny-600 bg-sunny-50 px-1.5 py-0.5 rounded-full">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Pending
                                                </span>
                                                <span class="text-[10px] text-gray-400" x-text="'Expires ' + member.expires_at"></span>
                                            </div>
                                        </template>
                                        <template x-if="member.type === 'member'">
                                            <p class="text-xs text-gray-500 truncate" x-text="member.email"></p>
                                        </template>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1 shrink-0">
                                        <!-- Owner badge -->
                                        <template x-if="member.type === 'member' && member.id == {{ $board->created_by }}">
                                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-primary-100 text-primary-700">Owner</span>
                                        </template>

                                        <!-- Role + remove for regular members -->
                                        <template x-if="member.type === 'member' && member.id != {{ $board->created_by }}">
                                            <div class="flex items-center gap-1">
                                                <select :value="member.role" @change="changeRole(member.id, $event.target.value)" class="text-xs rounded-lg border border-gray-200 dark:border-gray-600 py-1 pl-2 pr-6 text-white/50 bg-neutral-800 focus:border-primary-500 cursor-pointer">
                                                    <option value="admin">Admin</option>
                                                    <option value="normal">Member</option>
                                                </select>
                                                <button @click="removeMember(member.id)" class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg hover:bg-red-500/10 text-white/20 hover:text-red-400 transition-all" title="Remove from board">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </template>

                                        <!-- Resend / Cancel for pending invitations -->
                                        <template x-if="member.type === 'pending'">
                                            <div class="flex items-center gap-1">
                                                <button @click="resendInvite(member.invite_id)" class="p-1.5 rounded-lg hover:bg-white/5 text-white/20 hover:text-white/60 transition-all" title="Resend invitation">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                </button>
                                                <button @click="cancelInvite(member.invite_id)" class="p-1.5 rounded-lg hover:bg-red-500/10 text-white/20 hover:text-red-400 transition-all" title="Cancel invitation">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ===== EDIT BOARD PANEL ===== -->
        <div x-show="panel === 'edit'" style="display:none">
            <div class="flex items-center gap-2 p-4 border-b border-white/5">
                <button @click="panel = 'menu'" class="p-1.5 rounded-lg hover:bg-white/10 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h2 class="font-semibold text-white/80">Edit Board</h2>
            </div>
            <form method="POST" action="{{ route('boards.update', $board) }}" class="p-4 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-white/60 mb-1">Board Name</label>
                    <input type="text" name="name" value="{{ $board->name }}" required class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white/90 focus:border-white/20 focus:ring-1 focus:ring-white/10 transition-all" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/60 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white/90 focus:border-white/20 focus:ring-1 focus:ring-white/10 transition-all">{{ $board->description }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-white/60 mb-1">Visibility</label>
                    <select name="visibility" class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white/90 focus:border-white/20 focus:ring-1 focus:ring-white/10">
                        <option value="private" {{ $board->visibility === 'private' ? 'selected' : '' }}>Private — Only board members</option>
                        <option value="workspace" {{ $board->visibility === 'workspace' ? 'selected' : '' }}>Workspace — All workspace members</option>
                        <option value="public" {{ $board->visibility === 'public' ? 'selected' : '' }}>Public — Anyone with link</option>
                    </select>
                </div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-lg bg-white/10 text-white text-sm font-semibold hover:bg-white/15 transition-all">Save Changes</button>
            </form>
        </div>

        <!-- ===== CHAT PANEL ===== -->
        <div x-show="panel === 'chat'" style="display:none" class="flex flex-col flex-1 min-h-0">
            <div class="flex items-center gap-2 p-4 border-b border-white/5 shrink-0">
                <button @click="panel = 'menu'" class="p-1.5 rounded-lg hover:bg-white/10 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h2 class="font-semibold text-white/80">Board Chat</h2>
                <span class="text-xs text-gray-400 ml-auto" x-text="chatMessages.length + ' messages'"></span>
            </div>

            <!-- Messages -->
            <div id="chat-scroll" class="flex-1 overflow-y-auto p-4 space-y-3 scrollbar-thin">
                <!-- Load more -->
                <template x-if="chatHasMore">
                    <button @click="loadOlderChat()" class="w-full text-center text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 py-2 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors">Load older messages</button>
                </template>

                <!-- Loading -->
                <template x-if="chatLoading">
                    <div class="text-center py-8">
                        <svg class="animate-spin h-5 w-5 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    </div>
                </template>

                <!-- Empty state -->
                <template x-if="!chatLoading && chatMessages.length === 0">
                    <div class="text-center py-12">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="text-sm text-gray-400">No messages yet</p>
                        <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">Start the conversation!</p>
                    </div>
                </template>

                <!-- Message list -->
                <template x-for="msg in chatMessages" :key="msg.id">
                    <div class="group" :class="msg.user_id == {{ auth()->id() }} ? 'flex flex-col items-end' : ''">
                        <!-- Other user's message -->
                        <template x-if="msg.user_id != {{ auth()->id() }}">
                            <div class="flex gap-2 max-w-[85%]">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-400 to-gray-500 text-white text-[9px] font-bold flex items-center justify-center shrink-0 mt-0.5" x-text="msg.user?.name?.split(' ').map(w=>w[0]).join('').substring(0,2).toUpperCase()"></div>
                                <div>
                                    <div class="flex items-baseline gap-2 mb-0.5">
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="msg.user?.name"></span>
                                        <span class="text-[10px] text-gray-400" x-text="new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})"></span>
                                    </div>
                                    <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl rounded-tl-sm px-3 py-2 text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words" x-text="msg.body"></div>
                                </div>
                            </div>
                        </template>
                        <!-- Own message -->
                        <template x-if="msg.user_id == {{ auth()->id() }}">
                            <div class="max-w-[85%]">
                                <div class="flex items-baseline gap-2 mb-0.5 justify-end">
                                    <span class="text-[10px] text-gray-400" x-text="new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})"></span>
                                    <button @click="if(confirm('Delete this message?')) deleteChat(msg.id)" class="opacity-0 group-hover:opacity-100 text-gray-300 hover:text-red-400 transition-all">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                                <div class="bg-blue-500 text-white rounded-2xl rounded-tr-sm px-3 py-2 text-sm whitespace-pre-wrap break-words" x-text="msg.body"></div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Input -->
            <div class="p-3 border-t border-gray-200 dark:border-gray-700 shrink-0">
                <form @submit.prevent="sendChat()" class="flex gap-2">
                    <input
                        type="text"
                        x-model="chatBody"
                        placeholder="Type a message..."
                        @keydown.enter.prevent="sendChat()"
                        class="flex-1 rounded-xl border border-gray-200 dark:border-gray-600 px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500"
                    />
                    <button type="submit" :disabled="!chatBody.trim()" class="px-3 py-2 rounded-xl bg-blue-500 text-white hover:bg-blue-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- ===== BACKGROUND PANEL ===== -->
        <div x-show="panel === 'background'" style="display:none">
            <div class="flex items-center gap-2 p-4 border-b border-white/5">
                <button @click="panel = 'menu'" class="p-1.5 rounded-lg hover:bg-white/10 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <h2 class="font-semibold text-white/80">Background</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-3 gap-2">
                    @php
                        $bgs = [
                            'linear-gradient(135deg, #7c3aed, #d946ef)',
                            'linear-gradient(135deg, #06b6d4, #3b82f6)',
                            'linear-gradient(135deg, #f59e0b, #f97316)',
                            'linear-gradient(135deg, #84cc16, #10b981)',
                            'linear-gradient(135deg, #f43f5e, #ec4899)',
                            'linear-gradient(135deg, #6366f1, #8b5cf6)',
                            'linear-gradient(135deg, #14b8a6, #06b6d4)',
                            'linear-gradient(135deg, #f97316, #ef4444)',
                            'linear-gradient(135deg, #1e293b, #334155)',
                        ];
                    @endphp
                    @foreach($bgs as $bg)
                        <button
                            @click="fetch('{{ route('boards.update', $board) }}', {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                                body: JSON.stringify({ background_value: '{{ $bg }}', background_type: 'gradient' })
                            }).then(() => { document.querySelector('.fixed.inset-0.z-0').style.background = '{{ $bg }}' })"
                            class="h-16 rounded-xl transition-all hover:scale-105 {{ $board->background_value === $bg ? 'ring-2 ring-offset-2 ring-white' : '' }}"
                            style="background: {{ $bg }};">
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
