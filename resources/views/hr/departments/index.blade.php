<x-layouts.hr title="Departments" currentView="departments">

<div class="p-5 lg:p-7 space-y-5"
     x-data="departmentsPage()"
     x-init="init()">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white/85">Departments</h1>
            <p class="text-sm text-white/45 mt-0.5">{{ $departments->count() }} departments</p>
        </div>
        <button @click="openCreateModal()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium prod-bg text-white hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Department
        </button>
    </div>

    {{-- Search --}}
    <div class="relative max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" x-model="search" placeholder="Search departments..."
               class="w-full pl-9 pr-4 py-2 rounded-lg bg-white/[0.06] border border-white/[0.07] text-sm text-white/70 placeholder-white/30 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:bg-white/[0.08] transition-colors"/>
    </div>

    {{-- Department Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($departments as $dept)
            <div x-show="!search || '{{ strtolower(addslashes($dept->name)) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($dept->code ?? '')) }}'.includes(search.toLowerCase())"
                 class="bg-[#17172A] border border-white/[0.07] rounded-xl p-5 hover:border-white/[0.12] transition-all group">

                {{-- Header --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-white/85 truncate">{{ $dept->name }}</h3>
                            @if($dept->is_active)
                                <span class="w-2 h-2 rounded-full bg-emerald-400 shrink-0"></span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-red-400 shrink-0"></span>
                            @endif
                        </div>
                        @if($dept->code)
                            <p class="text-xs text-white/35 font-mono mt-0.5">{{ $dept->code }}</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                        <button @click="openEditModal({{ json_encode([
                            'id' => $dept->id,
                            'name' => $dept->name,
                            'code' => $dept->code,
                            'parent_id' => $dept->parent_id,
                            'head_id' => $dept->head_id,
                            'is_active' => $dept->is_active,
                        ]) }})"
                                class="p-1.5 rounded-md hover:bg-white/[0.08] text-white/35 hover:text-white/65 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button @click="confirmDelete({{ $dept->id }}, '{{ addslashes($dept->name) }}')"
                                class="p-1.5 rounded-md hover:bg-red-500/10 text-white/35 hover:text-red-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Details --}}
                <div class="space-y-2.5">
                    {{-- Head --}}
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-white/[0.06] flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span class="text-xs text-white/55 truncate">
                            {{ $dept->head->name ?? 'No head assigned' }}
                        </span>
                    </div>

                    {{-- Employee Count --}}
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-white/[0.06] flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="text-xs text-white/55">
                            {{ $dept->employees_count ?? 0 }} employee{{ ($dept->employees_count ?? 0) !== 1 ? 's' : '' }}
                        </span>
                    </div>

                    {{-- Parent Department --}}
                    @if($dept->parent)
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-white/[0.06] flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                            </div>
                            <span class="text-xs text-white/40 truncate">
                                Under {{ $dept->parent->name }}
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Status Badge --}}
                <div class="mt-4 pt-3 border-t border-white/[0.05]">
                    @if($dept->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-500/15 text-red-400 border border-red-500/20">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Empty State --}}
    @if($departments->count() === 0)
        <div class="text-center py-20">
            <svg class="w-14 h-14 text-white/10 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-white/35 text-sm">No departments yet</p>
            <p class="text-white/25 text-xs mt-1">Click "Add Department" to create your first department</p>
        </div>
    @endif

    {{-- ADD / EDIT MODAL --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showModal = false">

        {{-- Backdrop --}}
        <div x-show="showModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="showModal = false"
             class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

        {{-- Modal Content --}}
        <div x-show="showModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-lg bg-[#17172A] border border-white/[0.1] rounded-2xl shadow-2xl shadow-black/50">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.07]">
                <h2 class="text-base font-semibold text-white/85" x-text="editingId ? 'Edit Department' : 'Add Department'"></h2>
                <button @click="showModal = false" class="p-1.5 rounded-lg hover:bg-white/[0.07] text-white/35 hover:text-white/65 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form @submit.prevent="submitForm()" class="px-6 py-5 space-y-4">

                {{-- Error messages --}}
                <div x-show="errors.length > 0" class="bg-red-500/10 border border-red-500/20 rounded-lg p-3">
                    <template x-for="error in errors" :key="error">
                        <p class="text-xs text-red-400" x-text="error"></p>
                    </template>
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-medium text-white/45 mb-1.5">Department Name <span class="text-red-400">*</span></label>
                    <input type="text" x-model="form.name" required
                           class="w-full px-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.08] text-sm text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 transition-colors"
                           placeholder="e.g. Engineering">
                </div>

                {{-- Code --}}
                <div>
                    <label class="block text-xs font-medium text-white/45 mb-1.5">Department Code</label>
                    <input type="text" x-model="form.code"
                           class="w-full px-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.08] text-sm text-white/80 placeholder-white/25 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 transition-colors font-mono uppercase"
                           placeholder="e.g. ENG">
                </div>

                {{-- Parent Department --}}
                <div>
                    <label class="block text-xs font-medium text-white/45 mb-1.5">Parent Department</label>
                    <select x-model="form.parent_id"
                            class="w-full px-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.08] text-sm text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 appearance-none cursor-pointer transition-colors">
                        <option value="">None (Top Level)</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Head --}}
                <div>
                    <label class="block text-xs font-medium text-white/45 mb-1.5">Department Head</label>
                    <select x-model="form.head_id"
                            class="w-full px-3 py-2 rounded-lg bg-white/[0.06] border border-white/[0.08] text-sm text-white/70 focus:outline-none focus:ring-1 focus:ring-cyan-500/40 focus:border-cyan-500/30 appearance-none cursor-pointer transition-colors">
                        <option value="">No head assigned</option>
                        @foreach($departments->pluck('head')->filter()->unique('id') as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Active Toggle --}}
                <div class="flex items-center justify-between py-1">
                    <label class="text-sm text-white/65">Active</label>
                    <button type="button" @click="form.is_active = !form.is_active"
                            :class="form.is_active ? 'bg-cyan-500' : 'bg-white/[0.12]'"
                            class="relative w-10 h-5 rounded-full transition-colors">
                        <span :class="form.is_active ? 'translate-x-5' : 'translate-x-0.5'"
                              class="absolute top-0.5 left-0 w-4 h-4 rounded-full bg-white shadow-sm transform transition-transform"></span>
                    </button>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-white/[0.07]">
                    <button type="button" @click="showModal = false"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-white/50 hover:text-white/70 hover:bg-white/[0.06] transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-wait' : ''"
                            class="px-5 py-2 rounded-lg text-sm font-medium prod-bg text-white hover:opacity-90 transition-opacity shadow-lg shadow-cyan-500/10">
                        <span x-show="!submitting" x-text="editingId ? 'Update Department' : 'Create Department'"></span>
                        <span x-show="submitting" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- DELETE CONFIRMATION MODAL --}}
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="showDeleteModal = false">

        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="showDeleteModal = false"
             class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

        <div x-show="showDeleteModal" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-sm bg-[#17172A] border border-white/[0.1] rounded-2xl shadow-2xl shadow-black/50 p-6 text-center">

            <div class="w-12 h-12 rounded-full bg-red-500/15 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>

            <h3 class="text-base font-semibold text-white/85 mb-1">Delete Department</h3>
            <p class="text-sm text-white/45 mb-6">
                Are you sure you want to delete <span class="text-white/70 font-medium" x-text="deleteName"></span>? This action cannot be undone.
            </p>

            <div class="flex items-center justify-center gap-3">
                <button @click="showDeleteModal = false"
                        class="px-4 py-2 rounded-lg text-sm font-medium text-white/50 hover:text-white/70 hover:bg-white/[0.06] transition-colors">
                    Cancel
                </button>
                <button @click="executeDelete()"
                        :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-wait' : ''"
                        class="px-5 py-2 rounded-lg text-sm font-medium bg-red-500/20 text-red-400 border border-red-500/25 hover:bg-red-500/30 transition-colors">
                    <span x-show="!submitting">Delete</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Deleting...
                    </span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function departmentsPage() {
    return {
        search: '',
        showModal: false,
        showDeleteModal: false,
        editingId: null,
        deleteId: null,
        deleteName: '',
        submitting: false,
        errors: [],
        form: {
            name: '',
            code: '',
            parent_id: '',
            head_id: '',
            is_active: true,
        },

        init() {},

        resetForm() {
            this.form = { name: '', code: '', parent_id: '', head_id: '', is_active: true };
            this.errors = [];
            this.editingId = null;
        },

        openCreateModal() {
            this.resetForm();
            this.showModal = true;
        },

        openEditModal(dept) {
            this.resetForm();
            this.editingId = dept.id;
            this.form.name = dept.name || '';
            this.form.code = dept.code || '';
            this.form.parent_id = dept.parent_id || '';
            this.form.head_id = dept.head_id || '';
            this.form.is_active = dept.is_active ? true : false;
            this.showModal = true;
        },

        async submitForm() {
            this.errors = [];
            this.submitting = true;

            if (!this.form.name.trim()) {
                this.errors.push('Department name is required.');
                this.submitting = false;
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const isEdit = !!this.editingId;
            const url = isEdit
                ? `{{ url('/hr/departments') }}/${this.editingId}`
                : `{{ route('hr.departments.store') }}`;

            try {
                const response = await fetch(url, {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        name: this.form.name,
                        code: this.form.code || null,
                        parent_id: this.form.parent_id || null,
                        head_id: this.form.head_id || null,
                        is_active: this.form.is_active,
                    }),
                });

                if (response.ok) {
                    window.location.reload();
                } else if (response.status === 422) {
                    const data = await response.json();
                    if (data.errors) {
                        this.errors = Object.values(data.errors).flat();
                    } else {
                        this.errors = ['Validation failed. Please check your input.'];
                    }
                } else {
                    this.errors = ['Something went wrong. Please try again.'];
                }
            } catch (e) {
                this.errors = ['Network error. Please check your connection.'];
            } finally {
                this.submitting = false;
            }
        },

        confirmDelete(id, name) {
            this.deleteId = id;
            this.deleteName = name;
            this.showDeleteModal = true;
        },

        async executeDelete() {
            this.submitting = true;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch(`{{ url('/hr/departments') }}/${this.deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json().catch(() => ({}));
                    alert(data.message || 'Failed to delete department.');
                }
            } catch (e) {
                alert('Network error. Please check your connection.');
            } finally {
                this.submitting = false;
                this.showDeleteModal = false;
            }
        }
    };
}
</script>

</x-layouts.hr>
