<x-layouts.org-management :organization="$organization" activeTab="roles">

<div x-data="roleForm()">

    {{-- Header --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <a href="{{ route('roles.index', $organization) }}" class="text-white/30 hover:text-white/55 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-[20px] font-bold text-white/85">
                {{ isset($role) ? 'Edit Role' : 'Create Role' }}
            </h1>
        </div>
        <p class="text-[13px] text-white/35">
            {{ isset($role) ? 'Modify role details and permissions' : 'Define a new role with specific permissions' }}
        </p>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3">
            <ul class="list-disc list-inside text-red-400 text-[13px] space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ isset($role) ? route('roles.update', [$organization, $role]) : route('roles.store', $organization) }}"
          method="POST">
        @csrf
        @if(isset($role)) @method('PUT') @endif

        {{-- Role Details --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6 mb-6">
            <h2 class="text-[14px] font-semibold text-white/75 mb-5">Role Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">
                        Role Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $role->name ?? '') }}" required
                           placeholder="e.g. Project Manager"
                           @if(isset($role) && $role->is_system) readonly @endif
                           class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/25 transition @if(isset($role) && $role->is_system) opacity-50 cursor-not-allowed @endif">
                    @if(isset($role) && $role->is_system)
                        <p class="text-white/25 text-[11px] mt-1.5">System role names cannot be changed</p>
                    @endif
                </div>
                <div>
                    <label for="description" class="block text-[11px] font-semibold text-white/38 uppercase tracking-wider mb-2">Description</label>
                    <textarea id="description" name="description" rows="1" placeholder="Brief description of this role..."
                              class="w-full bg-white/[0.05] border border-white/[0.1] text-white/75 rounded-xl px-3.5 py-2.5 text-[13px] placeholder-white/20 focus:outline-none focus:border-indigo-500/50 focus:ring-1 focus:ring-indigo-500/25 transition resize-none">{{ old('description', $role->description ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="bg-[#111120] border border-white/[0.07] rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-[14px] font-semibold text-white/75">Permissions</h2>
                    <p class="text-white/35 text-[12px] mt-0.5">Select which permissions this role should have</p>
                </div>
                <span class="text-[12px] text-white/30" x-text="selectedCount + ' selected'"></span>
            </div>

            @php
                $rolePermissionIds = isset($role) ? $role->permissions->pluck('id')->toArray() : [];
                $productLabels = ['global' => 'Global', 'board' => 'BAI Board', 'projects' => 'BAI Projects'];
            @endphp

            <div x-data="{ activeProductTab: 'global' }" class="space-y-4">
                <div class="flex gap-2 border-b border-white/[0.06] pb-0">
                    @foreach($permissions as $productKey => $productPerms)
                        <button type="button"
                            @click="activeProductTab = '{{ $productKey }}'"
                            :class="activeProductTab === '{{ $productKey }}' ? 'border-indigo-500 text-white/85' : 'border-transparent text-white/35 hover:text-white/60'"
                            class="px-4 py-2.5 text-[13px] font-medium border-b-2 transition-colors">
                            {{ $productLabels[$productKey] ?? ucfirst($productKey) }}
                            <span class="text-[10px] ml-1 text-white/25">({{ $productPerms->count() }})</span>
                        </button>
                    @endforeach
                </div>

                @foreach($permissions as $productKey => $productPerms)
                    <div x-show="activeProductTab === '{{ $productKey }}'" x-cloak class="space-y-3">
                        @foreach($productPerms->groupBy('group') as $group => $groupPerms)
                            <div class="border border-white/[0.05] rounded-xl overflow-hidden" x-data="{ expanded: true }">
                                <button type="button" @click="expanded = !expanded"
                                        class="w-full flex items-center justify-between px-4 py-3 bg-white/[0.02] hover:bg-white/[0.04] transition">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-white/30 transition-transform duration-200" :class="{ 'rotate-90': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        <span class="text-[13px] font-semibold text-white/70 capitalize">{{ str_replace('_', ' ', $group) }}</span>
                                        <span class="text-[11px] text-white/25 bg-white/[0.05] px-2 py-0.5 rounded-md">{{ $groupPerms->count() }}</span>
                                    </div>
                                </button>
                                <div x-show="expanded" x-collapse>
                                    <div class="px-4 py-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                                        @foreach($groupPerms as $permission)
                                            <label class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-white/[0.03] transition cursor-pointer">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                       @checked(in_array($permission->id, old('permissions', $rolePermissionIds)))
                                                       class="mt-0.5 w-3.5 h-3.5 rounded border-white/20 bg-white/[0.05] text-indigo-500 focus:ring-indigo-500/25 focus:ring-offset-0">
                                                <div>
                                                    <span class="text-[13px] text-white/60">{{ $permission->name }}</span>
                                                    @if($permission->description)
                                                        <p class="text-[11px] text-white/25 mt-0.5">{{ $permission->description }}</p>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('roles.index', $organization) }}"
               class="inline-flex items-center gap-2 border border-white/[0.1] text-white/50 hover:text-white/70 rounded-xl px-4 py-2.5 text-[13px] font-medium transition">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-500 hover:bg-indigo-400 text-white rounded-xl px-5 py-2.5 text-[13px] font-semibold transition shadow-lg shadow-indigo-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ isset($role) ? 'Update Role' : 'Create Role' }}
            </button>
        </div>
    </form>

</div>

<script>
    function roleForm() {
        const existingPermissions = @json(old('permissions', $rolePermissionIds ?? [])).map(String);
        return {
            selectedPermissions: existingPermissions,
            get selectedCount() {
                return this.selectedPermissions.length;
            },
            toggleGroup(group, checked) {
                const checkboxes = document.querySelectorAll(`input[data-group="${group}"]`);
                checkboxes.forEach(cb => {
                    const val = cb.value;
                    if (checked) {
                        if (!this.selectedPermissions.includes(val)) this.selectedPermissions.push(val);
                    } else {
                        this.selectedPermissions = this.selectedPermissions.filter(id => id !== val);
                    }
                });
            },
            isGroupSelected(group) {
                const checkboxes = document.querySelectorAll(`input[data-group="${group}"]`);
                if (checkboxes.length === 0) return false;
                return Array.from(checkboxes).every(cb => this.selectedPermissions.includes(cb.value));
            }
        };
    }
</script>

</x-layouts.org-management>
