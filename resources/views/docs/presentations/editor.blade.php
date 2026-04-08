<x-layouts.docs-editor
    :title="$document->title"
    :document="$document"
    :canEdit="$canEdit"
>

@push('editor-head')
<style>
    /* Slide canvas */
    .slide-canvas-wrapper {
        background: #0a0a14;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .slide-canvas {
        position: relative;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        box-shadow: 0 8px 40px rgba(0,0,0,0.5);
    }
    .slide-element {
        position: absolute;
        cursor: move;
        user-select: none;
        transition: box-shadow 0.15s ease;
    }
    .slide-element.selected {
        outline: 2px dashed #38bdf8;
        outline-offset: 2px;
    }
    .slide-element .resize-handle {
        position: absolute;
        width: 8px;
        height: 8px;
        background: #38bdf8;
        border: 1px solid #0284c7;
        border-radius: 50%;
        z-index: 10;
        display: none;
    }
    .slide-element.selected .resize-handle {
        display: block;
    }
    .resize-handle.nw { top: -4px; left: -4px; cursor: nwse-resize; }
    .resize-handle.n  { top: -4px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
    .resize-handle.ne { top: -4px; right: -4px; cursor: nesw-resize; }
    .resize-handle.e  { top: 50%; right: -4px; transform: translateY(-50%); cursor: ew-resize; }
    .resize-handle.se { bottom: -4px; right: -4px; cursor: nwse-resize; }
    .resize-handle.s  { bottom: -4px; left: 50%; transform: translateX(-50%); cursor: ns-resize; }
    .resize-handle.sw { bottom: -4px; left: -4px; cursor: nesw-resize; }
    .resize-handle.w  { top: 50%; left: -4px; transform: translateY(-50%); cursor: ew-resize; }

    /* Thumbnail strip */
    .thumb-strip { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.15) transparent; }
    .thumb-strip::-webkit-scrollbar { width: 4px; }
    .thumb-strip::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }

    /* Properties panel scrollbar */
    .props-panel { scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.15) transparent; }
    .props-panel::-webkit-scrollbar { width: 4px; }
    .props-panel::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 2px; }

    /* Context menu */
    .ctx-menu {
        position: fixed;
        z-index: 999;
        min-width: 160px;
        background: #1a1a28;
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 10px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.6);
        padding: 4px;
        overflow: hidden;
    }
    .ctx-menu button {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        padding: 7px 12px;
        font-size: 12px;
        color: rgba(255,255,255,0.7);
        border-radius: 6px;
        transition: background 0.1s, color 0.1s;
    }
    .ctx-menu button:hover { background: rgba(255,255,255,0.07); color: #fff; }
    .ctx-menu button.danger { color: #f87171; }
    .ctx-menu button.danger:hover { background: rgba(239,68,68,0.12); }

    /* Editable text elements */
    .element-text[contenteditable="true"]:focus {
        outline: none;
        cursor: text;
    }
</style>
@endpush

{{-- ================================================================== --}}
{{-- SLIDE EDITOR — Alpine component                                     --}}
{{-- ================================================================== --}}
<div class="flex flex-col h-full" x-data="slideEditor" @keydown.delete.window="deleteElement()" @keydown.escape.window="selectedElementId = null" @click.self="selectedElementId = null">

    {{-- ── Toolbar ──────────────────────────────────────────────── --}}
    <div class="shrink-0 h-11 bg-[#0D0D16]/90 border-b border-white/[0.06] flex items-center px-3 gap-1">
        {{-- Insert group --}}
        <span class="text-[10px] uppercase tracking-wider text-white/25 font-semibold mr-1.5">Insert</span>

        {{-- Text box --}}
        <button @click="addElement('text')" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12px] text-white/60 hover:bg-white/[0.07] hover:text-white/90 transition-colors" title="Add text box">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/></svg>
            Text
        </button>

        {{-- Image --}}
        <button @click="triggerImageUpload()" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12px] text-white/60 hover:bg-white/[0.07] hover:text-white/90 transition-colors" title="Add image">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Image
        </button>
        <input type="file" x-ref="imageInput" class="hidden" accept="image/*" @change="handleImageUpload($event)">

        {{-- Shape --}}
        <div class="relative" x-data="{ shapeOpen: false }">
            <button @click="shapeOpen = !shapeOpen" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12px] text-white/60 hover:bg-white/[0.07] hover:text-white/90 transition-colors" title="Add shape">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/></svg>
                Shape
                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="shapeOpen" x-cloak @click.away="shapeOpen = false"
                 x-transition class="absolute top-full left-0 mt-1 bg-[#1a1a28] border border-white/[0.1] rounded-lg shadow-2xl z-50 p-1 min-w-[120px]">
                <button @click="addShape('rectangle'); shapeOpen = false" class="w-full text-left px-3 py-1.5 text-[12px] text-white/60 hover:bg-white/[0.07] hover:text-white/90 rounded-md flex items-center gap-2">
                    <span class="w-4 h-3 border border-current rounded-sm inline-block"></span> Rectangle
                </button>
                <button @click="addShape('circle'); shapeOpen = false" class="w-full text-left px-3 py-1.5 text-[12px] text-white/60 hover:bg-white/[0.07] hover:text-white/90 rounded-md flex items-center gap-2">
                    <span class="w-4 h-4 border border-current rounded-full inline-block"></span> Circle
                </button>
            </div>
        </div>

        <div class="w-px h-5 bg-white/[0.08] mx-2"></div>

        {{-- Theme selector --}}
        <div class="relative" x-data="{ themeOpen: false }">
            <button @click="themeOpen = !themeOpen" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12px] text-white/60 hover:bg-white/[0.07] hover:text-white/90 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                <span x-text="theme.charAt(0).toUpperCase() + theme.slice(1)"></span>
                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="themeOpen" x-cloak @click.away="themeOpen = false"
                 x-transition class="absolute top-full left-0 mt-1 bg-[#1a1a28] border border-white/[0.1] rounded-lg shadow-2xl z-50 p-1 min-w-[130px]">
                <template x-for="t in ['dark', 'light', 'midnight', 'ocean', 'sunset']" :key="t">
                    <button @click="theme = t; themeOpen = false; scheduleAutoSave()"
                            class="w-full text-left px-3 py-1.5 text-[12px] rounded-md transition-colors"
                            :class="theme === t ? 'bg-sky-500/15 text-sky-400' : 'text-white/60 hover:bg-white/[0.07] hover:text-white/90'">
                        <span x-text="t.charAt(0).toUpperCase() + t.slice(1)"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Transition selector --}}
        <div class="relative" x-data="{ transOpen: false }">
            <button @click="transOpen = !transOpen" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[12px] text-white/60 hover:bg-white/[0.07] hover:text-white/90 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                <span x-text="transition.charAt(0).toUpperCase() + transition.slice(1)"></span>
                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="transOpen" x-cloak @click.away="transOpen = false"
                 x-transition class="absolute top-full left-0 mt-1 bg-[#1a1a28] border border-white/[0.1] rounded-lg shadow-2xl z-50 p-1 min-w-[130px]">
                <template x-for="tr in ['none', 'slide', 'fade', 'convex', 'concave', 'zoom']" :key="tr">
                    <button @click="transition = tr; transOpen = false; scheduleAutoSave()"
                            class="w-full text-left px-3 py-1.5 text-[12px] rounded-md transition-colors"
                            :class="transition === tr ? 'bg-sky-500/15 text-sky-400' : 'text-white/60 hover:bg-white/[0.07] hover:text-white/90'">
                        <span x-text="tr.charAt(0).toUpperCase() + tr.slice(1)"></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="flex-1"></div>

        {{-- Present button --}}
        <a href="{{ route('docs.presentations.present', $document) }}" target="_blank"
           class="flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-[12px] font-semibold bg-amber-500/20 text-amber-400 hover:bg-amber-500/30 transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            Present
        </a>
    </div>

    {{-- ── Three-panel layout ───────────────────────────────────── --}}
    <div class="flex flex-1 min-h-0">

        {{-- LEFT: Slide thumbnails ─────────────────────────────── --}}
        <div class="shrink-0 w-[180px] bg-[#0B0B14] border-r border-white/[0.06] flex flex-col">
            <div class="flex-1 overflow-y-auto thumb-strip p-2.5 space-y-2" x-ref="thumbStrip">
                <template x-for="(slide, idx) in slides" :key="slide.id">
                    <div class="group relative"
                         @click="selectSlide(idx)"
                         @contextmenu.prevent="openSlideContextMenu($event, idx)">
                        {{-- Slide number badge --}}
                        <div class="absolute -top-1 -left-0.5 z-10 bg-[#0B0B14] px-1.5 py-0.5 rounded text-[9px] font-bold"
                             :class="currentSlideIndex === idx ? 'text-sky-400' : 'text-white/30'">
                            <span x-text="idx + 1"></span>
                        </div>
                        {{-- Thumbnail card --}}
                        <div class="relative w-full aspect-video rounded-lg overflow-hidden cursor-pointer border-2 transition-all"
                             :class="currentSlideIndex === idx ? 'border-sky-500 shadow-lg shadow-sky-500/20' : 'border-transparent hover:border-white/[0.15]'"
                             :style="'background:' + getSlideBackground(slide)">
                            {{-- Mini element previews --}}
                            <template x-for="el in slide.elements" :key="el.id">
                                <div class="absolute overflow-hidden"
                                     :style="`left:${el.x}%; top:${el.y}%; width:${el.width}%; height:${el.height}%;`">
                                    <template x-if="el.type === 'text'">
                                        <div class="text-[3px] leading-tight truncate"
                                             :style="`color:${el.style?.color || '#fff'}; font-weight:${el.style?.fontWeight || 'normal'}; text-align:${el.style?.textAlign || 'left'};`"
                                             x-text="el.content"></div>
                                    </template>
                                    <template x-if="el.type === 'image'">
                                        <img :src="el.src" class="w-full h-full object-cover rounded-sm" alt="">
                                    </template>
                                    <template x-if="el.type === 'shape'">
                                        <div class="w-full h-full"
                                             :style="`background-color:${el.style?.backgroundColor || '#0EA5E9'}; border-radius:${el.shape === 'circle' ? '50%' : (el.style?.borderRadius || 0) + 'px'};`"></div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Add slide button --}}
            @if($canEdit)
            <div class="shrink-0 p-2.5 border-t border-white/[0.06]">
                <button @click="addSlide()"
                        class="w-full flex items-center justify-center gap-1.5 py-2 rounded-lg text-[11px] font-medium text-white/40 hover:text-white/80 bg-white/[0.04] hover:bg-white/[0.08] border border-dashed border-white/[0.1] hover:border-white/[0.2] transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add slide
                </button>
            </div>
            @endif
        </div>

        {{-- CENTER: Main canvas ────────────────────────────────── --}}
        <div class="flex-1 slide-canvas-wrapper p-6" @click.self="selectedElementId = null">
            <div class="slide-canvas w-full max-w-[960px] rounded-lg"
                 x-ref="slideCanvas"
                 :style="'background:' + (currentSlide ? getSlideBackground(currentSlide) : '#1e293b')"
                 @click.self="selectedElementId = null">

                <template x-if="currentSlide">
                    <div class="relative w-full h-full">
                        <template x-for="el in currentSlide.elements" :key="el.id">
                            <div class="slide-element"
                                 :class="{ 'selected': selectedElementId === el.id }"
                                 :style="`left:${el.x}%; top:${el.y}%; width:${el.width}%; height:${el.height}%;`"
                                 @click.stop="selectedElementId = el.id"
                                 @mousedown.stop="startDrag($event, el)">

                                {{-- Text element --}}
                                <template x-if="el.type === 'text'">
                                    <div class="element-text w-full h-full flex items-center"
                                         :contenteditable="canEdit ? 'true' : 'false'"
                                         :style="`font-size:${scaleFont(el.style?.fontSize || 24)}px; font-weight:${el.style?.fontWeight || 'normal'}; color:${el.style?.color || '#ffffff'}; text-align:${el.style?.textAlign || 'left'}; line-height:1.2;`"
                                         @blur="el.content = $event.target.innerText; scheduleAutoSave()"
                                         @click.stop="selectedElementId = el.id"
                                         @mousedown.stop
                                         x-text="el.content"></div>
                                </template>

                                {{-- Image element --}}
                                <template x-if="el.type === 'image'">
                                    <div class="w-full h-full">
                                        <template x-if="el.src">
                                            <img :src="el.src" class="w-full h-full object-contain rounded" draggable="false" alt="">
                                        </template>
                                        <template x-if="!el.src">
                                            <div class="w-full h-full flex items-center justify-center bg-white/5 rounded border border-dashed border-white/20 text-white/30 text-xs">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Shape element --}}
                                <template x-if="el.type === 'shape'">
                                    <div class="w-full h-full"
                                         :style="`background-color:${el.style?.backgroundColor || '#0EA5E9'}; border-radius:${el.shape === 'circle' ? '50%' : (el.style?.borderRadius || 0) + 'px'};`"></div>
                                </template>

                                {{-- Resize handles --}}
                                <template x-if="canEdit && selectedElementId === el.id">
                                    <div>
                                        <div class="resize-handle nw" @mousedown.stop.prevent="startResize($event, el, 'nw')"></div>
                                        <div class="resize-handle n"  @mousedown.stop.prevent="startResize($event, el, 'n')"></div>
                                        <div class="resize-handle ne" @mousedown.stop.prevent="startResize($event, el, 'ne')"></div>
                                        <div class="resize-handle e"  @mousedown.stop.prevent="startResize($event, el, 'e')"></div>
                                        <div class="resize-handle se" @mousedown.stop.prevent="startResize($event, el, 'se')"></div>
                                        <div class="resize-handle s"  @mousedown.stop.prevent="startResize($event, el, 's')"></div>
                                        <div class="resize-handle sw" @mousedown.stop.prevent="startResize($event, el, 'sw')"></div>
                                        <div class="resize-handle w"  @mousedown.stop.prevent="startResize($event, el, 'w')"></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- RIGHT: Properties panel ────────────────────────────── --}}
        <div class="shrink-0 bg-[#0B0B14] border-l border-white/[0.06] overflow-y-auto props-panel transition-all duration-200"
             :class="propsOpen ? 'w-[260px]' : 'w-0 overflow-hidden'"
             x-show="propsOpen" x-cloak>
            <div class="w-[260px]">

                {{-- Panel header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-white/[0.06]">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-white/40" x-text="selectedElement ? 'Element Properties' : 'Slide Properties'"></span>
                    <button @click="propsOpen = false" class="p-1 rounded hover:bg-white/[0.07] text-white/30 hover:text-white/70 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- ELEMENT PROPERTIES --}}
                <template x-if="selectedElement">
                    <div class="p-4 space-y-4">

                        {{-- Element type badge --}}
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full"
                                  :class="{
                                      'bg-sky-500/20 text-sky-400': selectedElement.type === 'text',
                                      'bg-emerald-500/20 text-emerald-400': selectedElement.type === 'image',
                                      'bg-violet-500/20 text-violet-400': selectedElement.type === 'shape',
                                  }"
                                  x-text="selectedElement.type"></span>
                            @if($canEdit)
                            <button @click="deleteElement()" class="p-1 rounded hover:bg-red-500/10 text-white/30 hover:text-red-400 transition-colors" title="Delete element">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endif
                        </div>

                        {{-- Position inputs --}}
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-white/30 mb-2 block">Position</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="text-[10px] text-white/40 mb-0.5 block">X (%)</label>
                                    <input type="number" step="0.5" min="0" max="100" x-model.number="selectedElement.x" @input="scheduleAutoSave()"
                                           class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                </div>
                                <div>
                                    <label class="text-[10px] text-white/40 mb-0.5 block">Y (%)</label>
                                    <input type="number" step="0.5" min="0" max="100" x-model.number="selectedElement.y" @input="scheduleAutoSave()"
                                           class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                </div>
                                <div>
                                    <label class="text-[10px] text-white/40 mb-0.5 block">Width (%)</label>
                                    <input type="number" step="0.5" min="1" max="100" x-model.number="selectedElement.width" @input="scheduleAutoSave()"
                                           class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                </div>
                                <div>
                                    <label class="text-[10px] text-white/40 mb-0.5 block">Height (%)</label>
                                    <input type="number" step="0.5" min="1" max="100" x-model.number="selectedElement.height" @input="scheduleAutoSave()"
                                           class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                </div>
                            </div>
                        </div>

                        {{-- TEXT properties --}}
                        <template x-if="selectedElement.type === 'text'">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[10px] font-semibold uppercase tracking-wider text-white/30 mb-2 block">Text Style</label>
                                    <div class="space-y-2">
                                        {{-- Font size --}}
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-0.5 block">Font Size</label>
                                            <input type="number" min="8" max="120" x-model.number="selectedElement.style.fontSize" @input="scheduleAutoSave()"
                                                   class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                        </div>
                                        {{-- Font weight --}}
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-0.5 block">Font Weight</label>
                                            <select x-model="selectedElement.style.fontWeight" @change="scheduleAutoSave()"
                                                    class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                                <option value="normal">Normal</option>
                                                <option value="bold">Bold</option>
                                                <option value="lighter">Light</option>
                                            </select>
                                        </div>
                                        {{-- Text color --}}
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-0.5 block">Color</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" x-model="selectedElement.style.color" @input="scheduleAutoSave()"
                                                       class="w-8 h-8 rounded border border-white/[0.1] cursor-pointer bg-transparent">
                                                <input type="text" x-model="selectedElement.style.color" @input="scheduleAutoSave()"
                                                       class="flex-1 bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 font-mono focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                            </div>
                                        </div>
                                        {{-- Text align --}}
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-1 block">Alignment</label>
                                            <div class="flex gap-1">
                                                <template x-for="align in ['left', 'center', 'right']" :key="align">
                                                    <button @click="selectedElement.style.textAlign = align; scheduleAutoSave()"
                                                            class="flex-1 py-1.5 rounded-md text-[11px] font-medium transition-colors"
                                                            :class="selectedElement.style.textAlign === align ? 'bg-sky-500/20 text-sky-400' : 'bg-white/[0.04] text-white/40 hover:bg-white/[0.08]'"
                                                            x-text="align.charAt(0).toUpperCase() + align.slice(1)"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Content editor --}}
                                <div>
                                    <label class="text-[10px] font-semibold uppercase tracking-wider text-white/30 mb-2 block">Content</label>
                                    <textarea x-model="selectedElement.content" @input="scheduleAutoSave()" rows="3"
                                              class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 resize-none focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40"></textarea>
                                </div>
                            </div>
                        </template>

                        {{-- IMAGE properties --}}
                        <template x-if="selectedElement.type === 'image'">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[10px] font-semibold uppercase tracking-wider text-white/30 mb-2 block">Image</label>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-0.5 block">Source URL</label>
                                            <input type="text" x-model="selectedElement.src" @input="scheduleAutoSave()" placeholder="/storage/docs/..."
                                                   class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-0.5 block">Opacity</label>
                                            <input type="range" min="0" max="1" step="0.05"
                                                   x-model.number="selectedElement.style.opacity"
                                                   @input="scheduleAutoSave()"
                                                   class="w-full accent-sky-500">
                                            <span class="text-[10px] text-white/40" x-text="(selectedElement.style.opacity ?? 1)"></span>
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-0.5 block">Border Radius (px)</label>
                                            <input type="number" min="0" max="999"
                                                   x-model.number="selectedElement.style.borderRadius"
                                                   @input="scheduleAutoSave()"
                                                   class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- SHAPE properties --}}
                        <template x-if="selectedElement.type === 'shape'">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[10px] font-semibold uppercase tracking-wider text-white/30 mb-2 block">Shape</label>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-0.5 block">Type</label>
                                            <select x-model="selectedElement.shape" @change="scheduleAutoSave()"
                                                    class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                                <option value="rectangle">Rectangle</option>
                                                <option value="circle">Circle</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-[10px] text-white/40 mb-0.5 block">Background Color</label>
                                            <div class="flex items-center gap-2">
                                                <input type="color" x-model="selectedElement.style.backgroundColor" @input="scheduleAutoSave()"
                                                       class="w-8 h-8 rounded border border-white/[0.1] cursor-pointer bg-transparent">
                                                <input type="text" x-model="selectedElement.style.backgroundColor" @input="scheduleAutoSave()"
                                                       class="flex-1 bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 font-mono focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                            </div>
                                        </div>
                                        <template x-if="selectedElement.shape !== 'circle'">
                                            <div>
                                                <label class="text-[10px] text-white/40 mb-0.5 block">Border Radius (px)</label>
                                                <input type="number" min="0" max="999" x-model.number="selectedElement.style.borderRadius" @input="scheduleAutoSave()"
                                                       class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- SLIDE PROPERTIES (when no element selected) --}}
                <template x-if="!selectedElement && currentSlide">
                    <div class="p-4 space-y-4">
                        {{-- Background --}}
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-white/30 mb-2 block">Background</label>
                            <div class="space-y-2">
                                {{-- Background type --}}
                                <div>
                                    <label class="text-[10px] text-white/40 mb-0.5 block">Type</label>
                                    <select x-model="currentSlide.background.type" @change="scheduleAutoSave()"
                                            class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                        <option value="solid">Solid Color</option>
                                        <option value="gradient">Gradient</option>
                                        <option value="image">Image</option>
                                    </select>
                                </div>

                                {{-- Solid color --}}
                                <template x-if="currentSlide.background.type === 'solid'">
                                    <div>
                                        <label class="text-[10px] text-white/40 mb-0.5 block">Color</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" x-model="currentSlide.background.value" @input="scheduleAutoSave()"
                                                   class="w-8 h-8 rounded border border-white/[0.1] cursor-pointer bg-transparent">
                                            <input type="text" x-model="currentSlide.background.value" @input="scheduleAutoSave()"
                                                   class="flex-1 bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 font-mono focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                        </div>
                                        {{-- Quick color presets --}}
                                        <div class="flex gap-1.5 mt-2">
                                            <template x-for="c in ['#1e293b','#0f172a','#18181b','#1a1a2e','#0d1b2a','#2d1b69','#1b3a4b','#0a0a0a','#ffffff']" :key="c">
                                                <button @click="currentSlide.background.value = c; scheduleAutoSave()"
                                                        class="w-6 h-6 rounded-md border transition-all"
                                                        :class="currentSlide.background.value === c ? 'border-sky-400 scale-110' : 'border-white/[0.15] hover:border-white/[0.3]'"
                                                        :style="'background:' + c"></button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Gradient --}}
                                <template x-if="currentSlide.background.type === 'gradient'">
                                    <div>
                                        <label class="text-[10px] text-white/40 mb-0.5 block">CSS Gradient</label>
                                        <input type="text" x-model="currentSlide.background.value" @input="scheduleAutoSave()"
                                               placeholder="linear-gradient(135deg, #1e293b 0%, #0f172a 100%)"
                                               class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                        {{-- Gradient presets --}}
                                        <div class="flex gap-1.5 mt-2 flex-wrap">
                                            <template x-for="g in [
                                                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                                'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                                                'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                                                'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
                                                'linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%)',
                                                'linear-gradient(135deg, #232526 0%, #414345 100%)',
                                            ]" :key="g">
                                                <button @click="currentSlide.background.value = g; scheduleAutoSave()"
                                                        class="w-6 h-6 rounded-md border border-white/[0.15] hover:border-white/[0.3] transition-all"
                                                        :style="'background:' + g"></button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Image background --}}
                                <template x-if="currentSlide.background.type === 'image'">
                                    <div>
                                        <label class="text-[10px] text-white/40 mb-0.5 block">Image URL</label>
                                        <input type="text" x-model="currentSlide.background.value" @input="scheduleAutoSave()"
                                               placeholder="/storage/docs/bg.jpg"
                                               class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-2 py-1.5 text-[12px] text-white/80 focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40">
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Speaker notes --}}
                        <div>
                            <label class="text-[10px] font-semibold uppercase tracking-wider text-white/30 mb-2 block">Speaker Notes</label>
                            <textarea x-model="currentSlide.notes" @input="scheduleAutoSave()" rows="5"
                                      placeholder="Add speaker notes..."
                                      class="w-full bg-white/[0.05] border border-white/[0.1] rounded-md px-3 py-2 text-[12px] text-white/70 resize-none focus:ring-1 focus:ring-sky-500/40 focus:border-sky-500/40 placeholder-white/20"></textarea>
                        </div>

                        {{-- Slide info --}}
                        <div class="pt-2 border-t border-white/[0.06]">
                            <p class="text-[10px] text-white/25">
                                Slide <span x-text="currentSlideIndex + 1"></span> of <span x-text="slides.length"></span>
                                &middot; <span x-text="currentSlide.elements.length"></span> element<span x-text="currentSlide.elements.length !== 1 ? 's' : ''"></span>
                            </p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Props panel toggle (when closed) --}}
        <template x-if="!propsOpen">
            <button @click="propsOpen = true"
                    class="shrink-0 w-8 bg-[#0B0B14] border-l border-white/[0.06] flex items-center justify-center text-white/30 hover:text-white/60 hover:bg-white/[0.04] transition-colors"
                    title="Open properties panel">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
        </template>

    </div>{{-- end three-panel --}}

    {{-- ── Context menu ─────────────────────────────────────────── --}}
    <div x-show="ctxMenu.show" x-cloak
         class="ctx-menu"
         :style="`top:${ctxMenu.y}px; left:${ctxMenu.x}px;`"
         @click.away="ctxMenu.show = false">
        <button @click="duplicateSlide(ctxMenu.slideIdx); ctxMenu.show = false">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            Duplicate slide
        </button>
        <button @click="deleteSlide(ctxMenu.slideIdx); ctxMenu.show = false" class="danger">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Delete slide
        </button>
    </div>

    @if(!$canEdit)
        <div class="fixed top-14 right-4 z-40">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium bg-amber-500/15 text-amber-400 border border-amber-500/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View only
            </span>
        </div>
    @endif
</div>

@push('editor-scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('slideEditor', () => ({
        slides: @json(($document->body_json ?? [])['slides'] ?? []),
        theme: @json(($document->body_json ?? [])['theme'] ?? 'dark'),
        transition: @json(($document->body_json ?? [])['transition'] ?? 'slide'),
        currentSlideIndex: 0,
        selectedElementId: null,
        propsOpen: true,
        saveTimer: null,
        saving: false,
        canEdit: @json($canEdit),
        docId: @json($document->id),
        currentVersion: @json($document->version),

        // Context menu state
        ctxMenu: { show: false, x: 0, y: 0, slideIdx: 0 },

        // Drag / resize tracking
        _drag: null,
        _resize: null,

        init() {
            // Set up global mouse listeners for drag & resize
            const handleMouseMove = (e) => this.onMouseMove(e);
            const handleMouseUp = (e) => this.onMouseUp(e);
            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', handleMouseUp);

            // Keyboard shortcuts
            this.$watch('slides', () => {}, { deep: true });
        },

        // ── Computed ──────────────────────────────────────────
        get currentSlide() {
            return this.slides[this.currentSlideIndex] || null;
        },
        get selectedElement() {
            if (!this.selectedElementId || !this.currentSlide) return null;
            return this.currentSlide.elements.find(e => e.id === this.selectedElementId) || null;
        },

        // ── Slide operations ──────────────────────────────────
        selectSlide(index) {
            this.currentSlideIndex = index;
            this.selectedElementId = null;
        },

        addSlide() {
            if (!this.canEdit) return;
            this.slides.push({
                id: 's_' + Date.now(),
                elements: [{
                    id: 'e_' + Date.now(),
                    type: 'text',
                    content: 'Click to add title',
                    x: 10, y: 35, width: 80, height: 15,
                    style: { fontSize: 36, fontWeight: 'bold', color: '#ffffff', textAlign: 'center' }
                }],
                background: { type: 'solid', value: '#1e293b' },
                notes: '',
            });
            this.currentSlideIndex = this.slides.length - 1;
            this.selectedElementId = null;
            this.scheduleAutoSave();
        },

        deleteSlide(index) {
            if (!this.canEdit) return;
            if (this.slides.length <= 1) return;
            this.slides.splice(index, 1);
            if (this.currentSlideIndex >= this.slides.length) {
                this.currentSlideIndex = this.slides.length - 1;
            }
            this.selectedElementId = null;
            this.scheduleAutoSave();
        },

        duplicateSlide(index) {
            if (!this.canEdit) return;
            const source = this.slides[index];
            const clone = JSON.parse(JSON.stringify(source));
            clone.id = 's_' + Date.now();
            clone.elements.forEach((el, i) => { el.id = 'e_' + Date.now() + '_' + i; });
            this.slides.splice(index + 1, 0, clone);
            this.currentSlideIndex = index + 1;
            this.selectedElementId = null;
            this.scheduleAutoSave();
        },

        openSlideContextMenu(event, index) {
            if (!this.canEdit) return;
            this.ctxMenu = {
                show: true,
                x: event.clientX,
                y: event.clientY,
                slideIdx: index,
            };
        },

        // ── Element operations ────────────────────────────────
        addElement(type) {
            if (!this.canEdit || !this.currentSlide) return;
            const el = {
                id: 'e_' + Date.now(),
                type,
                x: 20, y: 40, width: 60, height: 15,
                style: {},
            };
            if (type === 'text') {
                el.content = 'Click to edit text';
                el.style = { fontSize: 24, fontWeight: 'normal', color: '#ffffff', textAlign: 'center' };
            } else if (type === 'image') {
                el.src = '';
                el.width = 30;
                el.height = 30;
                el.style = { opacity: 1, borderRadius: 0 };
            } else if (type === 'shape') {
                el.shape = 'rectangle';
                el.style = { backgroundColor: '#0EA5E9', borderRadius: 0 };
                el.width = 20;
                el.height = 15;
            }
            this.currentSlide.elements.push(el);
            this.selectedElementId = el.id;
            this.scheduleAutoSave();
        },

        addShape(shape) {
            if (!this.canEdit || !this.currentSlide) return;
            const el = {
                id: 'e_' + Date.now(),
                type: 'shape',
                shape: shape,
                x: 30, y: 35, width: 20, height: 15,
                style: { backgroundColor: '#0EA5E9', borderRadius: shape === 'circle' ? 9999 : 0 },
            };
            this.currentSlide.elements.push(el);
            this.selectedElementId = el.id;
            this.scheduleAutoSave();
        },

        deleteElement() {
            if (!this.canEdit || !this.selectedElementId || !this.currentSlide) return;
            const idx = this.currentSlide.elements.findIndex(e => e.id === this.selectedElementId);
            if (idx > -1) this.currentSlide.elements.splice(idx, 1);
            this.selectedElementId = null;
            this.scheduleAutoSave();
        },

        // ── Image upload ──────────────────────────────────────
        triggerImageUpload() {
            if (!this.canEdit) return;
            this.$refs.imageInput.click();
        },

        async handleImageUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('file', file);
            formData.append('document_id', this.docId);
            try {
                const res = await fetch('/docs/upload/image', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData,
                });
                const data = await res.json();
                if (data.location) {
                    const el = {
                        id: 'e_' + Date.now(),
                        type: 'image',
                        src: data.location,
                        x: 25, y: 25, width: 50, height: 50,
                        style: { opacity: 1, borderRadius: 0 },
                    };
                    this.currentSlide.elements.push(el);
                    this.selectedElementId = el.id;
                    this.scheduleAutoSave();
                }
            } catch (err) {
                console.error('Image upload failed', err);
            }
            event.target.value = '';
        },

        // ── Drag to move ─────────────────────────────────────
        startDrag(event, el) {
            if (!this.canEdit) return;
            // Don't start drag on text elements (they need click-to-edit)
            if (el.type === 'text' && this.selectedElementId === el.id) return;

            const canvas = this.$refs.slideCanvas;
            if (!canvas) return;
            const rect = canvas.getBoundingClientRect();
            this._drag = {
                el,
                startX: event.clientX,
                startY: event.clientY,
                origX: el.x,
                origY: el.y,
                canvasW: rect.width,
                canvasH: rect.height,
            };
            this.selectedElementId = el.id;
        },

        // ── Resize ───────────────────────────────────────────
        startResize(event, el, handle) {
            if (!this.canEdit) return;
            const canvas = this.$refs.slideCanvas;
            if (!canvas) return;
            const rect = canvas.getBoundingClientRect();
            this._resize = {
                el,
                handle,
                startX: event.clientX,
                startY: event.clientY,
                origX: el.x,
                origY: el.y,
                origW: el.width,
                origH: el.height,
                canvasW: rect.width,
                canvasH: rect.height,
            };
        },

        onMouseMove(event) {
            if (this._drag) {
                const d = this._drag;
                const dx = ((event.clientX - d.startX) / d.canvasW) * 100;
                const dy = ((event.clientY - d.startY) / d.canvasH) * 100;
                d.el.x = Math.max(0, Math.min(100 - d.el.width, d.origX + dx));
                d.el.y = Math.max(0, Math.min(100 - d.el.height, d.origY + dy));
            }
            if (this._resize) {
                const r = this._resize;
                const dx = ((event.clientX - r.startX) / r.canvasW) * 100;
                const dy = ((event.clientY - r.startY) / r.canvasH) * 100;

                let newX = r.origX, newY = r.origY, newW = r.origW, newH = r.origH;

                if (r.handle.includes('e')) { newW = Math.max(3, r.origW + dx); }
                if (r.handle.includes('w')) { newW = Math.max(3, r.origW - dx); newX = r.origX + dx; }
                if (r.handle.includes('s')) { newH = Math.max(3, r.origH + dy); }
                if (r.handle.includes('n')) { newH = Math.max(3, r.origH - dy); newY = r.origY + dy; }

                r.el.x = Math.max(0, newX);
                r.el.y = Math.max(0, newY);
                r.el.width = Math.min(100 - r.el.x, newW);
                r.el.height = Math.min(100 - r.el.y, newH);
            }
        },

        onMouseUp(event) {
            if (this._drag) {
                this._drag = null;
                this.scheduleAutoSave();
            }
            if (this._resize) {
                this._resize = null;
                this.scheduleAutoSave();
            }
        },

        // ── Helpers ──────────────────────────────────────────
        getSlideBackground(slide) {
            if (!slide.background) return '#1e293b';
            const bg = slide.background;
            if (bg.type === 'solid') return bg.value || '#1e293b';
            if (bg.type === 'gradient') return bg.value || 'linear-gradient(135deg, #1e293b 0%, #0f172a 100%)';
            if (bg.type === 'image') return `url('${bg.value}') center/cover no-repeat`;
            return bg.value || '#1e293b';
        },

        scaleFont(basePx) {
            // Scale font size relative to canvas width for proportional rendering
            const canvas = this.$refs.slideCanvas;
            if (!canvas) return basePx;
            const scale = canvas.offsetWidth / 960;
            return Math.round(basePx * scale);
        },

        // ── Auto-save ───────────────────────────────────────
        scheduleAutoSave() {
            if (!this.canEdit) return;
            clearTimeout(this.saveTimer);
            this.updateSaveStatus('unsaved');
            this.saveTimer = setTimeout(() => this.autoSave(), 1500);
        },

        updateSaveStatus(status) {
            const el = document.getElementById('save-status');
            if (!el) return;
            switch (status) {
                case 'saving':
                    el.textContent = 'Saving...';
                    el.className = 'text-yellow-400 text-xs';
                    break;
                case 'saved':
                    el.textContent = 'All changes saved';
                    el.className = 'text-emerald-400 text-xs';
                    break;
                case 'unsaved':
                    el.textContent = 'Unsaved changes';
                    el.className = 'text-white/40 text-xs';
                    break;
                case 'error':
                    el.textContent = 'Save failed — retrying...';
                    el.className = 'text-red-400 text-xs';
                    break;
            }
        },

        async autoSave() {
            if (this.saving) return;
            this.saving = true;
            this.updateSaveStatus('saving');
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                const res = await fetch(`/api/docs/documents/${this.docId}/auto-save`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        body_json: {
                            slides: this.slides,
                            theme: this.theme,
                            transition: this.transition,
                        },
                        title: document.getElementById('doc-title-input')?.value || '',
                        version: this.currentVersion,
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.currentVersion = data.version;
                    this.updateSaveStatus('saved');
                } else if (res.status === 409) {
                    this.updateSaveStatus('error');
                    alert('This presentation was modified by someone else. Please reload to get the latest version.');
                } else {
                    this.updateSaveStatus('error');
                    setTimeout(() => this.autoSave(), 5000);
                }
            } catch (e) {
                this.updateSaveStatus('error');
                setTimeout(() => this.autoSave(), 5000);
            } finally {
                this.saving = false;
            }
        },
    }));
});

// Save before leaving
window.addEventListener('beforeunload', (e) => {
    const status = document.getElementById('save-status')?.textContent;
    if (status === 'Unsaved changes') {
        e.preventDefault();
    }
});
</script>
@endpush

</x-layouts.docs-editor>
