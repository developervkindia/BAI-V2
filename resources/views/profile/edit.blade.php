<x-layouts.app title="Profile" :workspaces="$workspaces ?? collect()">
    <div class="max-w-2xl mx-auto space-y-8">
        <!-- Profile Header -->
        <div class="relative">
            <div class="h-32 bg-gradient-to-r from-primary-600 to-secondary-500 rounded-2xl"></div>
            <div class="flex items-end gap-4 -mt-10 ml-6">
                <div class="relative">
                    <x-ui.avatar :name="$user->name" :src="$user->avatar_url" size="xl" />
                    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" id="avatar-form">
                        @csrf
                        <label class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-white shadow-md flex items-center justify-center cursor-pointer hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <input type="file" name="avatar" class="hidden" accept="image/*" onchange="document.getElementById('avatar-form').submit()" />
                        </label>
                    </form>
                </div>
                <div class="pb-2">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-success-50 text-success-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <!-- Profile Tab -->
        <div x-data="{ tab: 'profile' }">
            <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6">
                @foreach(['profile' => 'Profile', 'security' => 'Security'] as $t => $l)
                    <button @click="tab = '{{ $t }}'" class="px-4 py-3 text-sm font-medium border-b-2 transition-colors" :class="tab === '{{ $t }}' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'">{{ $l }}</button>
                @endforeach
            </div>

            <!-- Profile Form -->
            <div x-show="tab === 'profile'">
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-5 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700">
                    @csrf @method('PUT')
                    <x-ui.input label="Full Name" name="name" :value="$user->name" required :error="$errors->first('name')" />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bio</label>
                        <textarea name="bio" rows="3" class="w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 px-4 py-3 text-gray-800 dark:text-gray-100 dark:bg-gray-800 focus:border-primary-500 focus:ring-4 focus:ring-primary-500/20 transition-all" placeholder="Tell us about yourself...">{{ $user->bio }}</textarea>
                    </div>
                    <x-ui.button type="submit" variant="primary">Save Changes</x-ui.button>
                </form>
            </div>

            <!-- Security Form -->
            <div x-show="tab === 'security'" x-cloak>
                <form method="POST" action="{{ route('profile.password') }}" class="space-y-5 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700">
                    @csrf @method('PUT')
                    @if($user->password)
                        <x-ui.input label="Current Password" type="password" name="current_password" required :error="$errors->first('current_password')" />
                    @endif
                    <x-ui.input label="New Password" type="password" name="password" required :error="$errors->first('password')" />
                    <x-ui.input label="Confirm Password" type="password" name="password_confirmation" required />
                    <x-ui.button type="submit" variant="primary">Update Password</x-ui.button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
