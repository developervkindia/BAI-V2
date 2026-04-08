<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->title }} — Thank You</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-full bg-[#0F0F18] text-white/90 font-sans antialiased flex items-center justify-center">

<div class="max-w-[480px] mx-auto px-4 text-center">
    <div class="bg-[#151520] rounded-2xl border border-violet-500/20 p-10" style="border-top: 4px solid #A855F7">
        <div class="w-16 h-16 rounded-full bg-emerald-500/15 flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-xl font-bold text-white/90 mb-3">{{ $document->title }}</h1>
        <p class="text-sm text-white/50">{{ $message }}</p>
        <a href="{{ route('docs.forms.public', $document->slug) }}" class="inline-block mt-6 text-[12px] text-violet-400 hover:text-violet-300 underline underline-offset-2">Submit another response</a>
    </div>
    <p class="text-[11px] text-white/15 mt-8">Powered by BAI Docs</p>
</div>

</body>
</html>
