<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Client portal — {{ $client->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans bg-[#0A0A10] text-white/85">
    <header class="border-b border-white/[0.08] px-4 py-4 flex items-center justify-between gap-4">
        <div>
            <p class="text-[10px] font-semibold text-white/25 uppercase tracking-widest">{{ $client->organization->name }}</p>
            <h1 class="text-[16px] font-bold text-white/90">{{ $client->name }}</h1>
            <p class="text-[12px] text-white/35 mt-0.5">Signed in as {{ $user->name }}</p>
        </div>
        <form method="POST" action="{{ route('client-portal.logout') }}">
            @csrf
            <button type="submit" class="text-[12px] text-white/40 hover:text-white/65 transition-colors">Sign out</button>
        </form>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8 space-y-8">
        <section>
            <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-3">Status</h2>
            <div class="rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3">
                <p class="text-[13px] text-white/55">{{ $client->stageLabel() }}</p>
                @if($client->hiredProject)
                    <p class="text-[12px] text-white/35 mt-2">Delivery project is active. Your team may share updates in BAI Projects.</p>
                @endif
            </div>
        </section>

        <section>
            <h2 class="text-[11px] font-semibold text-white/25 uppercase tracking-widest mb-3">Shared documents</h2>
            @if($documents->isEmpty())
                <p class="text-[13px] text-white/30">No documents shared with the portal yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach($documents as $doc)
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-white/[0.07] bg-white/[0.03] px-4 py-3">
                        <span class="text-[13px] text-white/70 truncate">{{ $doc->original_name }}</span>
                        <a href="{{ route('client-portal.documents.download', $doc) }}" class="text-[12px] text-orange-400/90 hover:text-orange-400 shrink-0">Download</a>
                    </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </main>
</body>
</html>
