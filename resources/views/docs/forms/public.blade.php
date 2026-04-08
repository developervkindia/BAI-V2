<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $document->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full bg-[#0F0F18] text-white/90 font-sans antialiased">

<div class="max-w-[640px] mx-auto px-4 py-10 sm:py-16">
    {{-- Form header --}}
    <div class="bg-[#151520] rounded-2xl border border-violet-500/20 p-8 mb-4" style="border-top: 4px solid #A855F7">
        <h1 class="text-2xl font-bold text-white/90 mb-2">{{ $document->title }}</h1>
        @if($document->description)
            <p class="text-sm text-white/50">{{ $document->description }}</p>
        @endif
    </div>

    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-[13px] text-red-200">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('docs.forms.submit', $document->slug) }}" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf

        @php $questions = ($document->body_json ?? [])['questions'] ?? []; @endphp
        @php $settings = ($document->body_json ?? [])['settings'] ?? []; @endphp

        {{-- Collect email --}}
        @if(!empty($settings['collect_email']))
            <div class="bg-[#151520] rounded-2xl border border-white/[0.06] p-6 mb-4">
                <label class="block text-sm font-medium text-white/80 mb-2">Email address <span class="text-red-400">*</span></label>
                <input type="email" name="respondent_email" required class="w-full bg-transparent border-b border-white/20 focus:border-violet-500 outline-none text-sm text-white/80 py-2" placeholder="Your email">
            </div>
        @endif

        @foreach($questions as $question)
            @if(($question['type'] ?? '') === 'section_header')
                <div class="bg-[#151520] rounded-2xl border border-white/[0.06] p-6 mb-4" style="border-top: 4px solid #A855F7">
                    <h2 class="text-lg font-semibold text-white/85">{{ $question['title'] ?? '' }}</h2>
                    @if(!empty($question['description']))
                        <p class="text-sm text-white/40 mt-1">{{ $question['description'] }}</p>
                    @endif
                </div>
                @continue
            @endif

            <div class="bg-[#151520] rounded-2xl border border-white/[0.06] p-6 mb-4">
                <label class="block text-sm font-medium text-white/80 mb-1">
                    {{ $question['title'] ?? 'Question' }}
                    @if(!empty($question['required'])) <span class="text-red-400">*</span> @endif
                </label>
                @if(!empty($question['description']))
                    <p class="text-[12px] text-white/35 mb-3">{{ $question['description'] }}</p>
                @endif

                @switch($question['type'] ?? 'short_text')
                    @case('short_text')
                        <input type="text" name="responses[{{ $question['id'] }}]" class="w-full bg-transparent border-b border-white/20 focus:border-violet-500 outline-none text-sm text-white/80 py-2" placeholder="Your answer" {{ !empty($question['required']) ? 'required' : '' }}>
                        @break
                    @case('long_text')
                        <textarea name="responses[{{ $question['id'] }}]" rows="4" class="w-full bg-white/[0.03] border border-white/10 focus:border-violet-500 rounded-lg outline-none text-sm text-white/80 p-3 resize-y" placeholder="Your answer" {{ !empty($question['required']) ? 'required' : '' }}></textarea>
                        @break
                    @case('multiple_choice')
                        <div class="space-y-2 mt-2">
                            @foreach(($question['options'] ?? []) as $opt)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="responses[{{ $question['id'] }}]" value="{{ $opt['label'] }}" class="w-4 h-4 text-violet-500 bg-transparent border-white/30 focus:ring-violet-500" {{ !empty($question['required']) ? 'required' : '' }}>
                                    <span class="text-sm text-white/60 group-hover:text-white/80">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                            @if(!empty($question['other_option']))
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" name="responses[{{ $question['id'] }}]" value="__other__">
                                    <input type="text" placeholder="Other..." class="bg-transparent border-b border-white/20 text-sm text-white/60 py-1 outline-none focus:border-violet-500">
                                </label>
                            @endif
                        </div>
                        @break
                    @case('checkboxes')
                        <div class="space-y-2 mt-2">
                            @foreach(($question['options'] ?? []) as $opt)
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="checkbox" name="responses[{{ $question['id'] }}][]" value="{{ $opt['label'] }}" class="w-4 h-4 rounded text-violet-500 bg-transparent border-white/30 focus:ring-violet-500">
                                    <span class="text-sm text-white/60 group-hover:text-white/80">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @break
                    @case('dropdown')
                        <select name="responses[{{ $question['id'] }}]" class="w-full bg-[#0F0F18] border border-white/10 rounded-lg text-sm text-white/80 py-2.5 px-3 outline-none focus:border-violet-500" {{ !empty($question['required']) ? 'required' : '' }}>
                            <option value="">Choose</option>
                            @foreach(($question['options'] ?? []) as $opt)
                                <option value="{{ $opt['label'] }}">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                        @break
                    @case('date')
                        <input type="date" name="responses[{{ $question['id'] }}]" class="bg-[#0F0F18] border border-white/10 rounded-lg text-sm text-white/80 py-2 px-3 outline-none focus:border-violet-500" {{ !empty($question['required']) ? 'required' : '' }}>
                        @break
                    @case('time')
                        <input type="time" name="responses[{{ $question['id'] }}]" class="bg-[#0F0F18] border border-white/10 rounded-lg text-sm text-white/80 py-2 px-3 outline-none focus:border-violet-500" {{ !empty($question['required']) ? 'required' : '' }}>
                        @break
                    @case('rating')
                        <div class="flex items-center gap-1 mt-2" x-data="{ rating: 0 }">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-white/20'" class="text-2xl hover:text-amber-400 transition-colors">&#9733;</button>
                            @endfor
                            <input type="hidden" name="responses[{{ $question['id'] }}]" :value="rating">
                        </div>
                        @break
                    @case('linear_scale')
                        @php $scale = $question['scale'] ?? ['min' => 1, 'max' => 5, 'min_label' => '', 'max_label' => '']; @endphp
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-[11px] text-white/30 mb-2">
                                <span>{{ $scale['min_label'] ?? $scale['min'] }}</span>
                                <span>{{ $scale['max_label'] ?? $scale['max'] }}</span>
                            </div>
                            <div class="flex items-center gap-2" x-data="{ selected: null }">
                                @for($i = ($scale['min'] ?? 1); $i <= ($scale['max'] ?? 5); $i++)
                                    <button type="button" @click="selected = {{ $i }}" :class="selected === {{ $i }} ? 'bg-violet-500 text-white border-violet-500' : 'bg-transparent text-white/50 border-white/20 hover:border-violet-400'" class="w-10 h-10 rounded-full border text-sm font-medium transition-all">{{ $i }}</button>
                                @endfor
                                <input type="hidden" name="responses[{{ $question['id'] }}]" :value="selected">
                            </div>
                        </div>
                        @break
                    @default
                        <input type="text" name="responses[{{ $question['id'] }}]" class="w-full bg-transparent border-b border-white/20 focus:border-violet-500 outline-none text-sm text-white/80 py-2" placeholder="Your answer">
                @endswitch
            </div>
        @endforeach

        <div class="flex items-center justify-between mt-6">
            <button type="submit" :disabled="submitting" class="px-8 py-3 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold shadow-lg shadow-violet-600/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <span x-show="!submitting">Submit</span>
                <span x-show="submitting" x-cloak>Submitting...</span>
            </button>
            <a href="{{ route('docs.forms.public', $document->slug) }}" class="text-[12px] text-white/30 hover:text-white/50">Clear form</a>
        </div>
    </form>

    <p class="text-center text-[11px] text-white/15 mt-10">Powered by BAI Docs</p>
</div>

</body>
</html>
