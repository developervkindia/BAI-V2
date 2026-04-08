<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Client portal — Sign in</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#0A0A10] flex flex-col items-center justify-center px-4">
    <div class="w-full max-w-sm">
        <p class="text-center text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-6">Client portal</p>
        <div class="rounded-2xl border border-white/[0.08] bg-white/[0.03] p-6">
            <h1 class="text-lg font-bold text-white/90 text-center mb-1">Sign in</h1>
            <p class="text-[12px] text-white/35 text-center mb-6">Access shared documents and project status.</p>

            @if ($errors->any())
                <div class="mb-4 rounded-xl bg-red-500/10 border border-red-500/20 px-3 py-2 text-[12px] text-red-400">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('client-portal.login.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[11px] text-white/40 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.06] border border-white/[0.1] text-white/85 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none"/>
                </div>
                <div>
                    <label class="block text-[11px] text-white/40 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white/[0.06] border border-white/[0.1] text-white/85 text-[13px] focus:ring-1 focus:ring-orange-500/40 focus:outline-none"/>
                </div>
                <label class="flex items-center gap-2 text-[12px] text-white/40">
                    <input type="checkbox" name="remember" value="1" class="rounded border-white/20 bg-white/5"/>
                    Remember me
                </label>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white text-[13px] font-semibold transition-colors">
                    Sign in
                </button>
            </form>
        </div>
        <p class="text-center text-[11px] text-white/20 mt-6">{{ config('app.name') }}</p>
    </div>
</body>
</html>
