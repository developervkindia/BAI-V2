<x-layouts.guest title="Forgot Password">
    <div class="min-h-full flex items-center justify-center p-8">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                <div class="w-16 h-16 rounded-2xl gradient-primary flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white font-heading tracking-tight">Forgot your password?</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">No worries! Enter your email and we'll send you a reset link.</p>
            </div>

            @if(session('status'))
                <div class="bg-success-50 text-success-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->has('email'))
                <div class="bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-xl text-sm">
                    <p class="font-medium">{{ $errors->first('email') }}</p>
                    @if(Str::contains($errors->first('email'), "can't find"))
                        <p class="mt-2 text-danger-600">Don't have an account yet?
                            <a href="{{ route('register') }}" class="font-bold underline hover:text-danger-800">Sign up here</a>
                        </p>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                <x-ui.input label="Email address" type="email" name="email" :value="old('email')" required autofocus placeholder="you@example.com" />
                <x-ui.button type="submit" variant="primary" size="lg" class="w-full">Send Reset Link</x-ui.button>
            </form>

            <div class="text-center space-y-3">
                <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-semibold flex items-center justify-center gap-1 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to sign in
                </a>
                <p class="text-sm text-gray-400">
                    Don't have an account? <a href="{{ route('register') }}" class="text-primary-600 hover:text-primary-700 font-semibold">Sign up free</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.guest>
