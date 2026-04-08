<x-layouts.guest title="Forgot Password">
    <div class="auth-wrapper" x-data="{ loading: false }">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/bai-logo-nav.svg') }}" alt="BAI">
                </a>
            </div>

            <!-- Icon + Heading -->
            <div style="text-align: center; margin-bottom: 1.75rem;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: var(--auth-gradient); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem;">
                    <svg style="width: 28px; height: 28px; color: #fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <h1 class="auth-title">Forgot your password?</h1>
                <p class="auth-subtitle" style="margin-bottom: 0;">No worries! Enter your email and we'll send you a reset link.</p>
            </div>

            @if(session('status'))
                <div class="auth-alert auth-alert-success">
                    <div class="auth-alert-icon" style="background: rgba(16,185,129,0.15);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->has('email'))
                <div class="auth-alert auth-alert-error">
                    <div class="auth-alert-icon" style="background: rgba(239,68,68,0.15);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span>{{ $errors->first('email') }}</span>
                        @if(Str::contains($errors->first('email'), "can't find"))
                            <p style="margin-top: 4px; font-size: 0.8rem;">
                                Don't have an account? <a href="{{ route('register') }}" class="auth-link" style="font-weight: 700;">Sign up here</a>
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" @submit="loading = true">
                @csrf

                <div class="auth-field">
                    <label for="email" class="auth-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="auth-input" placeholder="you@example.com">
                </div>

                <button type="submit" class="auth-submit" :class="loading && 'is-loading'" :disabled="loading">
                    <span x-show="!loading">Send Reset Link</span>
                    <span x-show="loading" x-cloak><span class="auth-spinner"></span> Sending...</span>
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.75rem;">
                <a href="{{ route('login') }}" class="auth-link" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                    <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to sign in
                </a>
                <p class="auth-footer" style="margin-top: 0.75rem;">
                    Don't have an account? <a href="{{ route('register') }}">Sign up free</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.guest>
