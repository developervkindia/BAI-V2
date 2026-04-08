<x-layouts.smartprojects currentView="clients" :title="$client->name">
<div class="max-w-5xl mx-auto px-4 py-6" x-data="clientShow()">

    <a href="{{ route('org.clients.index', $organization) }}" class="inline-flex items-center gap-1.5 text-[11px] text-white/35 hover:text-white/55 mb-4 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        All clients
    </a>

    <div class="flex items-start gap-4 mb-6">
        <div class="w-12 h-12 rounded-2xl bg-orange-500/15 text-orange-400 text-xl font-bold flex items-center justify-center shrink-0">
            {{ strtoupper(substr($client->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-[18px] font-bold text-white/88">{{ $client->name }}</h1>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-white/[0.08] text-white/45 font-semibold uppercase tracking-wide">{{ $client->stageLabel() }}</span>
                @if($client->company)
                    <span class="px-2 py-0.5 rounded-full bg-white/[0.06] text-white/40 text-[11px]">{{ $client->company }}</span>
                @endif
            </div>
            <div class="flex items-center gap-4 mt-1.5 flex-wrap">
                @if($client->email)
                    <a href="mailto:{{ $client->email }}" class="flex items-center gap-1 text-[12px] text-white/38 hover:text-orange-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $client->email }}
                    </a>
                @endif
                @if($client->phone)
                    <span class="flex items-center gap-1 text-[12px] text-white/38">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ $client->phone }}
                    </span>
                @endif
            </div>
        </div>
        @if($canManage)
        <button type="button" @click="showEdit = true"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/[0.06] hover:bg-white/[0.1] text-white/45 hover:text-white/70 text-xs font-medium transition-colors border border-white/[0.08]">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            Edit
        </button>
        @endif
    </div>

    @if($client->notes)
        <div class="bg-white/[0.03] border border-white/[0.07] rounded-xl px-4 py-3 mb-6 text-[13px] text-white/50 leading-relaxed">
            {{ $client->notes }}
        </div>
    @endif

    {{-- Workflow --}}
    @if($canManage)
    <div class="mb-8 p-4 rounded-xl border border-white/[0.08] bg-white/[0.02] space-y-3">
        <h3 class="text-[11px] font-semibold text-white/28 uppercase tracking-widest">Pre-sales → delivery</h3>
        <div class="flex flex-wrap gap-2">
            @if($client->stage === \App\Models\Client::STAGE_PROSPECT)
                <form method="POST" action="{{ route('org.clients.approve', [$organization, $client]) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500/15 border border-emerald-500/25 text-emerald-400 text-[12px] font-medium hover:bg-emerald-500/25 transition-colors">
                        Approve requirements
                    </button>
                </form>
                <form method="POST" action="{{ route('org.clients.lost', [$organization, $client]) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/40 text-[12px] hover:text-white/60 transition-colors">
                        Mark lost
                    </button>
                </form>
            @elseif($client->stage === \App\Models\Client::STAGE_APPROVED && !$client->hired_project_id)
                <form method="POST" action="{{ route('org.clients.hire', [$organization, $client]) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-orange-500/20 border border-orange-500/30 text-orange-300 text-[12px] font-medium hover:bg-orange-500/30 transition-colors">
                        Create delivery project
                    </button>
                </form>
                <p class="text-[11px] text-white/30 w-full">Creates a project in BAI Projects and links this client.</p>
            @elseif($client->hiredProject)
                <a href="{{ route('projects.overview', $client->hiredProject) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-orange-500/15 border border-orange-500/25 text-orange-300 text-[12px] font-medium hover:bg-orange-500/25 transition-colors">
                    Open delivery project
                </a>
            @endif
        </div>
        @if($client->requirements_approved_at)
            <p class="text-[10px] text-white/25">Requirements approved {{ $client->requirements_approved_at->diffForHumans() }}</p>
        @endif
    </div>
    @endif

    {{-- Portal users --}}
    @if($canManage)
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-[11px] font-semibold text-white/28 uppercase tracking-widest">Client portal access</h3>
            <button type="button" @click="showInvite = true" class="text-[11px] text-orange-400/80 hover:text-orange-400 transition-colors">+ Invite</button>
        </div>
        <p class="text-[11px] text-white/25 mb-3">External contacts sign in at <code class="text-white/40">{{ url('/client-portal/login') }}</code></p>
        @if($client->portalUsers->isEmpty())
            <p class="text-[12px] text-white/25">No portal users yet.</p>
        @else
            <ul class="space-y-2">
                @foreach($client->portalUsers as $pu)
                <li class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg bg-white/[0.03] border border-white/[0.06]">
                    <div>
                        <p class="text-[12px] text-white/65">{{ $pu->name }}</p>
                        <p class="text-[11px] text-white/30">{{ $pu->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('org.clients.portal.revoke', [$organization, $client, $pu]) }}" class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-[11px] text-red-400/60 hover:text-red-400 transition-colors">Revoke</button>
                    </form>
                </li>
                @endforeach
            </ul>
        @endif
    </div>
    @endif

    {{-- Documents --}}
    <div class="mb-8">
        <h3 class="text-[11px] font-semibold text-white/28 uppercase tracking-widest mb-3">Documents</h3>
        @if($canManage)
        <form method="POST" action="{{ route('org.clients.documents.store', [$organization, $client]) }}" enctype="multipart/form-data" class="mb-4 flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label class="block text-[10px] text-white/30 mb-1">File</label>
                <input type="file" name="file" required class="text-[11px] text-white/50 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-white/[0.08] file:text-white/60"/>
            </div>
            <div>
                <label class="block text-[10px] text-white/30 mb-1">Visibility</label>
                <select name="visibility" class="rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/70 text-[12px] px-2 py-1.5">
                    <option value="internal">Internal only</option>
                    <option value="portal">Visible in client portal</option>
                </select>
            </div>
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-orange-500/20 text-orange-400 text-[12px] font-medium border border-orange-500/25">Upload</button>
        </form>
        @endif
        @if($client->documents->isEmpty())
            <p class="text-[12px] text-white/25">No documents yet.</p>
        @else
            <ul class="space-y-2">
                @foreach($client->documents as $doc)
                <li class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg bg-white/[0.03] border border-white/[0.06]">
                    <div class="min-w-0">
                        <p class="text-[12px] text-white/65 truncate">{{ $doc->original_name }}</p>
                        <p class="text-[10px] text-white/25">{{ $doc->visibility === 'portal' ? 'Portal' : 'Internal' }} @if($doc->uploadedBy) · {{ $doc->uploadedBy->name }} @endif</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('org.clients.documents.download', [$organization, $client, $doc]) }}" class="text-[11px] text-orange-400/80 hover:text-orange-400">Download</a>
                        @if($canManage)
                        <form method="POST" action="{{ route('org.clients.documents.destroy', [$organization, $client, $doc]) }}" onsubmit="return confirm('Delete this file?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[11px] text-red-400/50 hover:text-red-400">Delete</button>
                        </form>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Projects --}}
    <div class="mb-8">
        <h3 class="text-[11px] font-semibold text-white/28 uppercase tracking-widest mb-3">Projects ({{ $client->projects->count() }})</h3>
        @if($client->projects->isEmpty())
            <p class="text-sm text-white/25 py-4">No projects linked to this client yet.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($client->projects as $proj)
                <a href="{{ route('projects.overview', $proj) }}"
                   class="group bg-white/[0.03] hover:bg-white/[0.06] border border-white/[0.07] hover:border-white/[0.12] rounded-xl p-3.5 transition-all flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-black/20"
                          style="background: {{ $proj->color ?? '#F97316' }};"></span>
                    <span class="text-[13px] font-medium text-white/72 group-hover:text-white/88 truncate transition-colors">{{ $proj->name }}</span>
                </a>
                @endforeach
            </div>
        @endif
    </div>

    @if($billingWeeks->isNotEmpty())
    <div>
        <h3 class="text-[11px] font-semibold text-white/28 uppercase tracking-widest mb-3">Locked Billing Weeks</h3>
        <div class="space-y-2">
            @foreach($billingWeeks as $week)
            <div class="bg-white/[0.03] border border-white/[0.07] rounded-xl px-4 py-3 flex items-center gap-4">
                <div class="flex-1 min-w-0">
                    <p class="text-[12px] text-white/60">
                        <span class="font-medium text-white/75">{{ $week->project->name }}</span>
                        <span class="text-white/30 mx-1.5">·</span>
                        {{ \Carbon\Carbon::parse($week->week_start)->format('M d') }} – {{ \Carbon\Carbon::parse($week->week_end)->format('M d, Y') }}
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-[13px] font-semibold text-orange-400">${{ number_format($week->total_amount, 2) }}</p>
                    <p class="text-[10px] text-white/28">{{ number_format($week->total_billable_hours, 1) }} hrs</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Invite modal --}}
    @if($canManage)
    <div x-show="showInvite" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showInvite = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div class="relative bg-[#16162A] border border-white/[0.12] rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h2 class="text-[15px] font-bold text-white/85 mb-4">Invite to client portal</h2>
            <form method="POST" action="{{ route('org.clients.portal.invite', [$organization, $client]) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-[11px] text-white/35 mb-1">Name</label>
                    <input type="text" name="name" required value="{{ old('name', $client->name) }}"
                           placeholder="Contact name"
                           class="w-full px-3 py-2 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 text-[13px] placeholder-white/20"/>
                    @error('name', 'portalInvite')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-[11px] text-white/35 mb-1">Email</label>
                    <input type="email" name="email" required value="{{ old('email', $client->email) }}"
                           placeholder="name@company.com"
                           class="w-full px-3 py-2 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 text-[13px] placeholder-white/20"/>
                    @error('email', 'portalInvite')<p class="text-[11px] text-red-400 mt-1">{{ $message }}</p>@enderror
                </div>
                <p class="text-[10px] text-white/25">Defaults use this client’s name and email; change them if the portal login should be for someone else. We email a temporary password — they sign in at the client portal URL.</p>
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="showInvite = false" class="flex-1 py-2 rounded-xl border border-white/[0.1] text-white/40 text-[13px]">Cancel</button>
                    <button type="submit" class="flex-1 py-2 rounded-xl bg-orange-500 text-white text-[13px] font-semibold">Send invite</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit Modal --}}
    @if($canManage)
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showEdit = false">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
        <div class="relative bg-[#16162A] border border-white/[0.12] rounded-2xl w-full max-w-md p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-[15px] font-bold text-white/85">Edit Client</h2>
                <button type="button" @click="showEdit = false" class="w-7 h-7 rounded-lg bg-white/[0.06] text-white/40 flex items-center justify-center">×</button>
            </div>
            <form method="POST" action="{{ route('org.clients.update', [$organization, $client]) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Name <span class="text-red-400/80">*</span></label>
                    <input type="text" name="name" required value="{{ old('name', $client->name) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px]"/>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Company</label>
                    <input type="text" name="company" value="{{ old('company', $client->company) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px]"/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', $client->email) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px]"/>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px]"/>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/82 text-[13px] resize-none">{{ old('notes', $client->notes) }}</textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showEdit = false" class="flex-1 py-2.5 rounded-xl border border-white/[0.1] text-white/40 text-[13px]">Cancel</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white text-[13px] font-semibold">Save</button>
                </div>
            </form>
            <form method="POST" action="{{ route('org.clients.destroy', [$organization, $client]) }}" class="mt-3"
                  x-data x-on:submit.prevent="$dispatch('confirm-modal', { title: 'Delete Client', message: 'Delete this client? Linked projects stay but lose client link.', confirmLabel: 'Delete', variant: 'danger', onConfirm: () => $el.submit() })">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full py-2 rounded-xl text-red-400/70 hover:text-red-400 hover:bg-red-500/10 text-[12px]">Delete Client</button>
            </form>
        </div>
    </div>
    @endif

</div>

<script>
function clientShow() {
    return {
        showEdit: false,
        showInvite: {{ $errors->getBag('portalInvite')->isNotEmpty() ? 'true' : 'false' }},
    };
}
</script>
</x-layouts.smartprojects>
