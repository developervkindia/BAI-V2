<x-layouts.guest title="Create Account">
    <div class="min-h-full flex">
        <!-- Left: Decorative Panel -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-accent-500 via-primary-500 to-secondary-500 relative overflow-hidden items-center justify-center p-12">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-16 right-16 w-64 h-64 bg-white rounded-full" style="animation: float 7s ease-in-out infinite;"></div>
                <div class="absolute bottom-16 left-16 w-40 h-40 bg-white rounded-full" style="animation: float 5s ease-in-out infinite 1s;"></div>
                <div class="absolute top-1/3 right-1/3 w-24 h-24 bg-white rounded-full" style="animation: float 6s ease-in-out infinite 2s;"></div>
            </div>
            <div class="relative z-10 text-center">
                <div class="w-20 h-20 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center mx-auto mb-8">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h2 class="text-4xl font-extrabold text-white font-heading tracking-tight mb-4">Start Building Today</h2>
                <p class="text-white/80 text-lg max-w-md">Create boards, organize tasks, and collaborate with your team in a beautiful workspace.</p>
            </div>
            <style>
                @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
            </style>
        </div>

        <!-- Right: Register Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
            <div class="w-full max-w-md space-y-8">
                <div class="lg:hidden text-center">
                    <div class="w-12 h-12 rounded-xl gradient-primary flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                    </div>
                </div>

                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white font-heading tracking-tight">Create your account</h1>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Free forever. No credit card required.</p>
                </div>

                @if(!empty($invitationBoard))
                    <div class="bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 rounded-2xl p-4 flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl gradient-primary flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-primary-800 dark:text-primary-300">You've been invited!</p>
                            <p class="text-sm text-primary-600 dark:text-primary-400 mt-0.5">
                                <strong>{{ $invitationInviter }}</strong> invited you to the board <strong>"{{ $invitationBoard }}"</strong>.
                                Create your account to join.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- OAuth Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('social.redirect', 'google') }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-medium text-gray-700 transition-all">
                        <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google
                    </a>
                    <a href="{{ route('social.redirect', 'github') }}" class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-sm font-medium text-gray-700 transition-all">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        GitHub
                    </a>
                </div>

                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200 dark:border-gray-700"></div></div>
                    <div class="relative flex justify-center text-sm"><span class="px-4 bg-white dark:bg-gray-900 text-gray-400">or continue with email</span></div>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
                    @csrf
                    <x-ui.input label="Full Name" type="text" name="name" :value="old('name')" required autofocus :error="$errors->first('name')" placeholder="John Doe" />

                    @if(!empty($invitationEmail))
                        {{-- Locked email for invitation --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                            <div class="flex items-center gap-2 w-full rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-3">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span class="text-gray-800 dark:text-gray-200 text-sm">{{ $invitationEmail }}</span>
                            </div>
                            <input type="hidden" name="email" value="{{ $invitationEmail }}" />
                            <p class="text-xs text-gray-400 mt-1">This email was used for your invitation and cannot be changed.</p>
                        </div>
                    @else
                        <x-ui.input label="Email" type="email" name="email" :value="old('email')" required :error="$errors->first('email')" placeholder="you@example.com" />
                    @endif
                    <x-ui.input label="Password" type="password" name="password" required :error="$errors->first('password')" placeholder="Min. 8 characters" />
                    <x-ui.input label="Confirm Password" type="password" name="password_confirmation" required placeholder="Repeat your password" />

                    <x-ui.button type="submit" variant="primary" size="lg" class="w-full" ::class="loading && 'opacity-75 cursor-wait'">
                        <span x-show="!loading">Create Account</span>
                        <span x-show="loading" x-cloak class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Creating account...
                        </span>
                    </x-ui.button>
                </form>

                <p class="text-center text-sm text-gray-500">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-semibold">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.guest>
