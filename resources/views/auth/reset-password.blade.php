<x-layouts.guest title="Reset Password">
    <div class="auth-wrapper" x-data="{ loading: false, showPwd: false, showPwdConfirm: false }">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI">
                </a>
            </div>

            <!-- Heading -->
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <h1 class="auth-title">Reset your password</h1>
                <p class="auth-subtitle" style="margin-bottom: 0;">Choose a new password for your account.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" @submit="loading = true">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="auth-field">
                    <label for="email" class="auth-label">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', request()->email) }}" required
                        class="auth-input {{ $errors->has('email') ? 'has-error' : '' }}">
                    @error('email') <p class="auth-error">{{ $message }}</p> @enderror
                </div>

                <div class="auth-field">
                    <label for="password" class="auth-label">New Password</label>
                    <div class="auth-password-wrap">
                        <input id="password" :type="showPwd ? 'text' : 'password'" name="password" required
                            class="auth-input {{ $errors->has('password') ? 'has-error' : '' }}"
                            placeholder="Min. 8 characters">
                        <button type="button" class="auth-password-toggle" @click="showPwd = !showPwd" tabindex="-1">
                            <svg x-show="!showPwd" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPwd" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    @error('password') <p class="auth-error">{{ $message }}</p> @enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation" class="auth-label">Confirm Password</label>
                    <div class="auth-password-wrap">
                        <input id="password_confirmation" :type="showPwdConfirm ? 'text' : 'password'" name="password_confirmation" required
                            class="auth-input" placeholder="Repeat your password">
                        <button type="button" class="auth-password-toggle" @click="showPwdConfirm = !showPwdConfirm" tabindex="-1">
                            <svg x-show="!showPwdConfirm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showPwdConfirm" x-cloak fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-submit" :class="loading && 'is-loading'" :disabled="loading">
                    <span x-show="!loading">Reset Password</span>
                    <span x-show="loading" x-cloak><span class="auth-spinner"></span> Resetting...</span>
                </button>
            </form>
        </div>
    </div>
</x-layouts.guest>
