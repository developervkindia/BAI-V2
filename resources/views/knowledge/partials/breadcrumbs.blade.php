@props(['items' => []])

<nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[12px] text-white/40 mb-6">
    @foreach($items as $i => $item)
        @if($i > 0)
            <span class="text-white/20">/</span>
        @endif
        @if(!empty($item['url']) && $i < count($items) - 1)
            <a href="{{ $item['url'] }}" class="hover:text-sky-300 transition-colors">{{ $item['label'] }}</a>
        @else
            <span class="text-white/55 font-medium">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
