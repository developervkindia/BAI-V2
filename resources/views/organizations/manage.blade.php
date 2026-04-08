<x-layouts.org-management :organization="$organization" activeTab="overview">

    <div class="max-w-3xl space-y-8">
        <div>
            <h1 class="text-[20px] font-bold text-white/85">Organization management</h1>
            <p class="text-[13px] text-white/35 mt-1">
                Settings, people, roles, and billing for <span class="text-white/55">{{ $organization->name }}</span> — separate from any product workspace.
            </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <a href="{{ route('organizations.show', $organization) }}"
               class="group flex gap-4 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03] hover:bg-white/[0.05] hover:border-white/[0.12] transition-all">
                <div class="w-11 h-11 rounded-xl bg-indigo-500/15 border border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-[14px] font-semibold text-white/85 group-hover:text-white transition-colors">Organization settings</h2>
                    <p class="text-[12px] text-white/30 mt-1 leading-relaxed">Name, description, and product overview.</p>
                </div>
                <svg class="w-4 h-4 text-white/20 group-hover:text-white/40 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            <a href="{{ route('users.index', $organization) }}"
               class="group flex gap-4 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03] hover:bg-white/[0.05] hover:border-white/[0.12] transition-all">
                <div class="w-11 h-11 rounded-xl bg-violet-500/15 border border-violet-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-[14px] font-semibold text-white/85 group-hover:text-white transition-colors">Members</h2>
                    <p class="text-[12px] text-white/30 mt-1 leading-relaxed">Invite users, profiles, and access for this organization.</p>
                </div>
                <svg class="w-4 h-4 text-white/20 group-hover:text-white/40 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            <a href="{{ route('roles.index', $organization) }}"
               class="group flex gap-4 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03] hover:bg-white/[0.05] hover:border-white/[0.12] transition-all">
                <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-[14px] font-semibold text-white/85 group-hover:text-white transition-colors">Roles &amp; permissions</h2>
                    <p class="text-[12px] text-white/30 mt-1 leading-relaxed">Define roles and what each role can do across products.</p>
                </div>
                <svg class="w-4 h-4 text-white/20 group-hover:text-white/40 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            <a href="{{ route('subscriptions.index') }}"
               class="group flex gap-4 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03] hover:bg-white/[0.05] hover:border-white/[0.12] transition-all">
                <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-[14px] font-semibold text-white/85 group-hover:text-white transition-colors">Subscriptions</h2>
                    <p class="text-[12px] text-white/30 mt-1 leading-relaxed">Plans and billing for organization products.</p>
                </div>
                <svg class="w-4 h-4 text-white/20 group-hover:text-white/40 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>

            @if($organization->hasProduct('projects') && app(\App\Services\PermissionService::class)->userCan(auth()->user(), 'org.clients.view', $organization))
            <a href="{{ route('org.clients.index', $organization) }}"
               class="group flex gap-4 p-5 rounded-2xl border border-white/[0.07] bg-white/[0.03] hover:bg-white/[0.05] hover:border-white/[0.12] transition-all sm:col-span-2">
                <div class="w-11 h-11 rounded-xl bg-orange-500/15 border border-orange-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="text-[14px] font-semibold text-white/85 group-hover:text-white transition-colors">Clients &amp; client portal</h2>
                    <p class="text-[12px] text-white/30 mt-1 leading-relaxed">Pre-sales pipeline, documents, invite external contacts to the portal, and create delivery projects.</p>
                </div>
                <svg class="w-4 h-4 text-white/20 group-hover:text-white/40 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
        </div>
    </div>

</x-layouts.org-management>
