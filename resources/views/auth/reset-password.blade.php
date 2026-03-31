<x-layouts.guest title="Reset Password">
    <div class="min-h-full flex items-center justify-center p-8">
        <div class="w-full max-w-md space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white font-heading tracking-tight">Reset your password</h1>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Enter your new password below.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-ui.input label="Email" type="email" name="email" :value="old('email', request()->email)" required :error="$errors->first('email')" />
                <x-ui.input label="New Password" type="password" name="password" required :error="$errors->first('password')" placeholder="Min. 8 characters" />
                <x-ui.input label="Confirm Password" type="password" name="password_confirmation" required placeholder="Repeat your password" />
                <x-ui.button type="submit" variant="primary" size="lg" class="w-full">Reset Password</x-ui.button>
            </form>
        </div>
    </div>
</x-layouts.guest>
