<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $document->title }} — Presentation</title>
    @vite('resources/js/docs-presentation.js')
    <style>
        /* Reset & fullscreen */
        html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background: #000; }

        /* Exit button overlay */
        .exit-btn {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .exit-btn:hover {
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
        }
        body:hover .exit-btn { opacity: 1; }

        /* Slide element positioning */
        .slide-element {
            position: absolute;
            box-sizing: border-box;
        }
        .slide-element img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Reveal overrides for our custom slides */
        .reveal .slides section {
            width: 100%;
            height: 100%;
            padding: 0;
            box-sizing: border-box;
        }
        .reveal .slides section .slide-content {
            position: relative;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    {{-- Exit button --}}
    <a href="{{ route('docs.presentations.show', $document) }}" class="exit-btn" title="Exit presentation (Esc)">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Exit
    </a>

    {{-- Reveal.js container --}}
    <div class="reveal">
        <div class="slides">
            @php
                $bodyJson = $document->body_json ?? [];
                $slides = $bodyJson['slides'] ?? [];
                $transition = $bodyJson['transition'] ?? 'slide';
                $theme = $bodyJson['theme'] ?? 'dark';
            @endphp

            @foreach($slides as $slide)
                @php
                    $bg = $slide['background'] ?? ['type' => 'solid', 'value' => '#1e293b'];
                    $bgAttr = '';
                    if (($bg['type'] ?? 'solid') === 'solid') {
                        $bgAttr = 'data-background-color="' . e($bg['value'] ?? '#1e293b') . '"';
                    } elseif (($bg['type'] ?? '') === 'gradient') {
                        $bgAttr = 'data-background-gradient="' . e($bg['value'] ?? 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)') . '"';
                    } elseif (($bg['type'] ?? '') === 'image') {
                        $bgAttr = 'data-background-image="' . e($bg['value'] ?? '') . '" data-background-size="cover"';
                    }
                    $notes = $slide['notes'] ?? '';
                @endphp

                <section {!! $bgAttr !!}>
                    <div class="slide-content">
                        @foreach($slide['elements'] ?? [] as $element)
                            @php
                                $style = $element['style'] ?? [];
                                $posStyle = sprintf(
                                    'left:%s%%; top:%s%%; width:%s%%; height:%s%%;',
                                    $element['x'] ?? 0,
                                    $element['y'] ?? 0,
                                    $element['width'] ?? 50,
                                    $element['height'] ?? 20
                                );
                            @endphp

                            @if(($element['type'] ?? '') === 'text')
                                @php
                                    $textStyle = $posStyle . sprintf(
                                        'font-size:%svw; font-weight:%s; color:%s; text-align:%s; line-height:1.2; display:flex; align-items:center; justify-content:%s; word-break:break-word;',
                                        round(($style['fontSize'] ?? 24) * 0.052, 2),
                                        $style['fontWeight'] ?? 'normal',
                                        $style['color'] ?? '#ffffff',
                                        $style['textAlign'] ?? 'left',
                                        match($style['textAlign'] ?? 'left') {
                                            'center' => 'center',
                                            'right' => 'flex-end',
                                            default => 'flex-start',
                                        }
                                    );
                                @endphp
                                <div class="slide-element" style="{{ $textStyle }}">
                                    {{ $element['content'] ?? '' }}
                                </div>

                            @elseif(($element['type'] ?? '') === 'image')
                                @php
                                    $imgStyle = $posStyle;
                                    if (isset($style['opacity'])) $imgStyle .= 'opacity:' . $style['opacity'] . ';';
                                    if (isset($style['borderRadius'])) $imgStyle .= 'border-radius:' . $style['borderRadius'] . 'px;';
                                    $imgStyle .= 'overflow:hidden;';
                                @endphp
                                <div class="slide-element" style="{{ $imgStyle }}">
                                    @if(!empty($element['src']))
                                        <img src="{{ $element['src'] }}" alt="" draggable="false">
                                    @endif
                                </div>

                            @elseif(($element['type'] ?? '') === 'shape')
                                @php
                                    $shapeStyle = $posStyle;
                                    $shapeStyle .= 'background-color:' . ($style['backgroundColor'] ?? '#0EA5E9') . ';';
                                    if (($element['shape'] ?? 'rectangle') === 'circle') {
                                        $shapeStyle .= 'border-radius:50%;';
                                    } else {
                                        $shapeStyle .= 'border-radius:' . ($style['borderRadius'] ?? 0) . 'px;';
                                    }
                                @endphp
                                <div class="slide-element" style="{{ $shapeStyle }}"></div>
                            @endif
                        @endforeach
                    </div>

                    @if($notes)
                        <aside class="notes">{{ $notes }}</aside>
                    @endif
                </section>
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Reveal.js
            const deck = new Reveal({
                hash: true,
                controls: true,
                progress: true,
                center: false,
                transition: @json($transition),
                width: 1920,
                height: 1080,
                margin: 0,
                minScale: 0.2,
                maxScale: 2.0,
                display: 'flex',
                keyboard: {
                    27: function () {
                        // Escape key — go back to editor
                        window.location.href = @json(route('docs.presentations.show', $document));
                    }
                }
            });

            deck.initialize();
        });
    </script>
</body>
</html>
