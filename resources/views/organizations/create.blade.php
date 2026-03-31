<x-layouts.guest title="Create Your Organization">
    <div class="min-h-screen bg-black flex items-center justify-center p-8">
        <div class="w-full max-w-md space-y-8">

            {{-- Logo --}}
            <div class="text-center">
                <div class="flex items-center justify-center gap-2 mb-8">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M13 2L4.09 12.97H11L10 22l8.91-10.97H13L14 2z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-white/60">BAI</span>
                </div>
                <h1 class="text-2xl font-bold text-white/80 mb-2">Create your organization</h1>
                <p class="text-sm text-white/30 max-w-xs mx-auto leading-relaxed">
                    An organization is the home for all your BAI products and team members.
                </p>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('organizations.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1.5">Organization name</label>
                    <input
                        type="text"
                        name="name"
                        required
                        autofocus
                        placeholder="Acme Corp"
                        value="{{ old('name') }}"
                        class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/80 placeholder-white/20 focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500/50 focus:outline-none text-sm transition-colors"
                    />
                    @error('name')
                        <p class="text-xs text-red-400 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-white/50 mb-1.5">Description <span class="text-white/25">(optional)</span></label>
                    <input
                        type="text"
                        name="description"
                        placeholder="What does your team work on?"
                        value="{{ old('description') }}"
                        class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/80 placeholder-white/20 focus:ring-1 focus:ring-indigo-500/50 focus:border-indigo-500/50 focus:outline-none text-sm transition-colors"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full py-3 rounded-xl bg-white text-black text-sm font-semibold hover:bg-white/90 transition-colors mt-2"
                >
                    Create Organization
                </button>
            </form>

            <p class="text-center text-xs text-white/20">
                You can create more organizations or invite team members later.
            </p>
        </div>
    </div>
</x-layouts.guest>
