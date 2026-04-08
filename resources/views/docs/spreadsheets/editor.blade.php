<x-layouts.docs-editor
    :title="$document->title"
    :document="$document"
    :canEdit="$canEdit"
>

@push('editor-head')
    @vite('resources/js/docs-spreadsheet.js')
    <style>
        /* ── Google Sheets-like theme using correct jss_ class names ── */
        .jss_spreadsheet { font-family: Arial, sans-serif !important; }
        .jss_content { background: #fff !important; }
        .jss_worksheet { background-color: #fff !important; font-size: 13px !important; }

        /* Data cells */
        .jss_worksheet > tbody > tr > td {
            background-color: #fff !important;
            color: #000 !important;
            border-right: 1px solid #e2e3e3 !important;
            border-bottom: 1px solid #e2e3e3 !important;
        }

        /* Column headers (A, B, C...) */
        .jss_worksheet > thead > tr > td {
            background-color: #f8f9fa !important;
            color: #444 !important;
            font-size: 11px !important;
            border-top: 1px solid #e2e3e3 !important;
            border-left: 1px solid #e2e3e3 !important;
            border-bottom: 2px solid #c0c0c0 !important;
        }

        /* Row numbers (1, 2, 3...) */
        .jss_worksheet > tbody > tr > td:first-child {
            background-color: #f8f9fa !important;
            color: #444 !important;
            font-size: 11px !important;
            text-align: center !important;
            border-right: 2px solid #c0c0c0 !important;
        }

        /* Top-left corner cell */
        .jss_worksheet > thead > tr > td:first-child {
            background-color: #f8f9fa !important;
        }

        /* Selection highlight */
        .jss_worksheet > tbody > tr > td.highlight {
            background-color: #e8f0fe !important;
        }
        .jss_worksheet > thead > tr > td.selected {
            background-color: #d3e3fd !important;
            color: #1a73e8 !important;
        }
        .jss_worksheet > tbody > tr > td:first-child.selected {
            background-color: #d3e3fd !important;
            color: #1a73e8 !important;
        }

        /* Selection border */
        .jss_corner { background-color: #1a73e8 !important; border-color: #1a73e8 !important; }

        /* Editor */
        .jss_worksheet .editor textarea,
        .jss_worksheet .editor {
            background: #fff !important;
            color: #000 !important;
        }

        /* Make container fill available space */
        #spreadsheet-container .jss_container,
        #spreadsheet-container .jss_content {
            width: 100% !important;
            max-height: calc(100vh - 160px) !important;
            overflow: auto !important;
        }
    </style>
@endpush

{{-- ================================================================ --}}
{{-- SPREADSHEET EDITOR UI                                             --}}
{{-- ================================================================ --}}
<div class="flex flex-col h-[calc(100vh-3rem)]"
     x-data="spreadsheetApp()"
     x-init="init()">

    {{-- ── TOOLBAR ── --}}
    <div class="shrink-0 bg-[#f0f0f0] border-b border-gray-300 px-3 py-1.5 flex items-center gap-1 flex-wrap">

        {{-- Bold --}}
        <button @click="applyStyle('font-weight', 'bold')" title="Bold (Ctrl+B)"
                class="toolbar-btn" :class="{ 'toolbar-btn-active': activeStyles['font-weight'] === 'bold' }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/>
            </svg>
        </button>

        {{-- Italic --}}
        <button @click="applyStyle('font-style', 'italic')" title="Italic (Ctrl+I)"
                class="toolbar-btn" :class="{ 'toolbar-btn-active': activeStyles['font-style'] === 'italic' }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <line x1="19" y1="4" x2="10" y2="4" stroke-width="2" stroke-linecap="round"/>
                <line x1="14" y1="20" x2="5" y2="20" stroke-width="2" stroke-linecap="round"/>
                <line x1="15" y1="4" x2="9" y2="20" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>

        {{-- Strikethrough --}}
        <button @click="applyStyle('text-decoration', 'line-through')" title="Strikethrough"
                class="toolbar-btn">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="M6 12h12"/>
                <path d="M7 5.5C7 4.67 8.34 4 10 4h2.5c2.5 0 4.5 1 4.5 3.5 0 1.5-1 2.5-2 3"/>
                <path d="M17 18.5c0 .83-1.34 1.5-3 1.5h-2.5C9 20 7 19 7 16.5c0-1.5 1-2.5 2-3"/>
            </svg>
        </button>

        <div class="w-px h-5 bg-white/[0.08] mx-1"></div>

        {{-- Text Color --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" title="Text Color" class="toolbar-btn flex items-center gap-0.5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 20h16"/>
                    <path d="M9.5 4L5 16h2l1.2-3.2h7.6L17 16h2L14.5 4h-5z" fill="currentColor" stroke="none"/>
                </svg>
                <span class="w-4 h-1 rounded-full block" :style="'background:' + (textColor || '#e2e2ee')"></span>
            </button>
            <div x-show="open" x-cloak x-transition
                 class="absolute top-full left-0 mt-1 z-50 bg-white border border-gray-200 rounded-lg shadow-2xl p-2 grid grid-cols-6 gap-1 w-40">
                <template x-for="c in colorPalette" :key="c">
                    <button @click="textColor = c; applyStyle('color', c); open = false"
                            class="w-5 h-5 rounded border border-white/10 hover:scale-110 transition-transform"
                            :style="'background:' + c"></button>
                </template>
            </div>
        </div>

        {{-- Background Color --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" title="Background Color" class="toolbar-btn flex items-center gap-0.5">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M16.56 8.94L7.62 0 6.21 1.41l2.38 2.38-5.15 5.15a1.49 1.49 0 000 2.12l5.5 5.5c.29.29.68.44 1.06.44s.77-.15 1.06-.44l5.5-5.5c.59-.58.59-1.53 0-2.12zM5.21 10L10 5.21 14.79 10H5.21zM19 11.5s-2 2.17-2 3.5c0 1.1.9 2 2 2s2-.9 2-2c0-1.33-2-3.5-2-3.5z"/>
                </svg>
                <span class="w-4 h-1 rounded-full block" :style="'background:' + (bgColor || '#17172a')"></span>
            </button>
            <div x-show="open" x-cloak x-transition
                 class="absolute top-full left-0 mt-1 z-50 bg-white border border-gray-200 rounded-lg shadow-2xl p-2 grid grid-cols-6 gap-1 w-40">
                <template x-for="c in bgPalette" :key="c">
                    <button @click="bgColor = c; applyStyle('background-color', c); open = false"
                            class="w-5 h-5 rounded border border-white/10 hover:scale-110 transition-transform"
                            :style="'background:' + c"></button>
                </template>
            </div>
        </div>

        <div class="w-px h-5 bg-white/[0.08] mx-1"></div>

        {{-- Borders --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" title="Borders" class="toolbar-btn">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M3 3h18v18H3V3zm16 16V5H5v14h14zM11 7h2v2h-2V7zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2zM7 7h2v2H7V7zm0 4h2v2H7v-2zm0 4h2v2H7v-2zm8-8h2v2h-2V7zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition
                 class="absolute top-full left-0 mt-1 z-50 bg-white border border-gray-200 rounded-lg shadow-2xl p-2 w-44">
                <button @click="applyBorder('border-top', '1px solid #555'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded">Top border</button>
                <button @click="applyBorder('border-bottom', '1px solid #555'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded">Bottom border</button>
                <button @click="applyBorder('border-left', '1px solid #555'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded">Left border</button>
                <button @click="applyBorder('border-right', '1px solid #555'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded">Right border</button>
                <div class="border-t border-gray-300 my-1"></div>
                <button @click="applyBorder('border', '1px solid #555'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded">All borders</button>
                <button @click="applyBorder('border', ''); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded">Clear borders</button>
            </div>
        </div>

        {{-- Merge Cells --}}
        <button @click="toggleMerge()" title="Merge Cells" class="toolbar-btn">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M9 3v18M3 9h18" stroke-dasharray="2 2" opacity="0.4"/>
                <path d="M8 12l3-3m0 0l3 3m-3-3v6" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>

        <div class="w-px h-5 bg-white/[0.08] mx-1"></div>

        {{-- Alignment --}}
        <button @click="applyStyle('text-align', 'left')" title="Align Left"
                class="toolbar-btn" :class="{ 'toolbar-btn-active': activeStyles['text-align'] === 'left' }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" d="M3 6h18M3 12h12M3 18h16"/>
            </svg>
        </button>
        <button @click="applyStyle('text-align', 'center')" title="Align Center"
                class="toolbar-btn" :class="{ 'toolbar-btn-active': activeStyles['text-align'] === 'center' }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" d="M3 6h18M6 12h12M4 18h16"/>
            </svg>
        </button>
        <button @click="applyStyle('text-align', 'right')" title="Align Right"
                class="toolbar-btn" :class="{ 'toolbar-btn-active': activeStyles['text-align'] === 'right' }">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" d="M3 6h18M9 12h12M5 18h16"/>
            </svg>
        </button>

        <div class="w-px h-5 bg-white/[0.08] mx-1"></div>

        {{-- Number Format --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" title="Number Format" class="toolbar-btn flex items-center gap-1 px-2">
                <span class="text-[11px] font-mono">#</span>
                <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition
                 class="absolute top-full left-0 mt-1 z-50 bg-white border border-gray-200 rounded-lg shadow-2xl p-1 w-48">
                <button @click="applyNumberFormat('text'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded flex items-center justify-between">
                    Plain text <span class="text-gray-400">Abc</span>
                </button>
                <button @click="applyNumberFormat('number'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded flex items-center justify-between">
                    Number <span class="text-gray-400">1,000.00</span>
                </button>
                <button @click="applyNumberFormat('percent'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded flex items-center justify-between">
                    Percent <span class="text-gray-400">10.00%</span>
                </button>
                <button @click="applyNumberFormat('currency'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded flex items-center justify-between">
                    Currency <span class="text-gray-400">$1,000.00</span>
                </button>
                <button @click="applyNumberFormat('date'); open = false"
                        class="w-full text-left px-2.5 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 rounded flex items-center justify-between">
                    Date <span class="text-gray-400">04/05/2026</span>
                </button>
            </div>
        </div>

        @if(!$canEdit)
            <div class="ml-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[11px] font-medium bg-amber-500/15 text-amber-400 border border-amber-500/20">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View only
                </span>
            </div>
        @endif
    </div>

    {{-- ── FORMULA BAR ── --}}
    <div class="shrink-0 bg-[#f0f0f0] border-b border-gray-300 px-3 py-1 flex items-center gap-2">
        {{-- Cell reference --}}
        <div class="shrink-0 w-16 text-center">
            <span class="text-[12px] font-mono font-semibold text-emerald-400/80 bg-emerald-500/10 px-2 py-0.5 rounded"
                  x-text="selectedCell">A1</span>
        </div>

        <div class="w-px h-5 bg-white/[0.08]"></div>

        {{-- fx icon --}}
        <span class="shrink-0 text-gray-400 text-xs font-mono italic">fx</span>

        {{-- Formula / value input --}}
        <input type="text"
               x-model="formulaBarValue"
               @keydown.enter="updateCellFromFormulaBar()"
               @focus="formulaBarFocused = true"
               @blur="formulaBarFocused = false"
               class="flex-1 bg-transparent border-0 outline-none text-gray-800 text-[13px] font-mono placeholder-white/20 px-1 py-0.5 rounded focus:ring-1 focus:ring-emerald-500/30"
               placeholder="Enter value or formula"
               @if(!$canEdit) readonly @endif
        />
    </div>

    {{-- ── SPREADSHEET CONTAINER ── --}}
    <div class="flex-1 overflow-hidden relative bg-white">
        <div id="spreadsheet-container" class="w-full h-full"></div>
    </div>

    {{-- ── SHEET TABS ── --}}
    <div class="shrink-0 bg-[#f0f0f0] border-t border-gray-300 px-2 py-1 flex items-center gap-1 overflow-x-auto"
         @contextmenu.prevent="">

        {{-- Add sheet button --}}
        <button @click="addSheet()" title="Add new sheet"
                class="shrink-0 w-7 h-7 flex items-center justify-center rounded-md hover:bg-gray-200 text-gray-400 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
        </button>

        <div class="w-px h-5 bg-white/[0.08] mx-0.5"></div>

        {{-- Sheet tabs --}}
        <template x-for="(sheet, idx) in sheets" :key="idx">
            <div class="relative shrink-0" @contextmenu.prevent="openSheetMenu($event, idx)">
                <button @click="switchSheet(idx)"
                        class="px-3 py-1 rounded-md text-[12px] font-medium transition-colors whitespace-nowrap"
                        :class="idx === activeSheetIdx
                            ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25'
                            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 border border-transparent'">
                    <span x-text="sheet.name"></span>
                </button>
            </div>
        </template>

        {{-- Right-click context menu for sheet tabs --}}
        <div x-show="sheetMenu.visible" x-cloak x-transition
             @click.away="sheetMenu.visible = false"
             :style="'position:fixed; left:' + sheetMenu.x + 'px; top:' + sheetMenu.y + 'px; z-index:100;'"
             class="bg-white border border-gray-200 rounded-lg shadow-2xl py-1 w-40">
            <button @click="renameSheet(sheetMenu.idx)" class="w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path stroke-linecap="round" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Rename
            </button>
            <button @click="duplicateSheet(sheetMenu.idx)" class="w-full text-left px-3 py-1.5 text-xs text-gray-700 hover:bg-gray-100 hover:text-gray-900 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2"/>
                    <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                </svg>
                Duplicate
            </button>
            <div class="border-t border-gray-300 my-1" x-show="sheets.length > 1"></div>
            <button @click="deleteSheet(sheetMenu.idx)"
                    x-show="sheets.length > 1"
                    class="w-full text-left px-3 py-1.5 text-xs text-red-500 hover:bg-red-500/10 hover:text-red-600 flex items-center gap-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
        </div>

        {{-- Sheet count --}}
        <div class="ml-auto shrink-0 text-[10px] text-gray-400 pr-2">
            <span x-text="sheets.length"></span> sheet<span x-show="sheets.length !== 1">s</span>
        </div>
    </div>
</div>

{{-- ── TOOLBAR BUTTON STYLES ── --}}
<style>
    .toolbar-btn {
        @apply shrink-0 p-1.5 rounded-md text-gray-600 hover:text-gray-800 hover:bg-gray-200 transition-colors cursor-pointer;
    }
    .toolbar-btn-active {
        @apply bg-blue-100 text-blue-700 !important;
    }
</style>

@push('editor-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    const docId = {{ $document->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const canEdit = @json($canEdit);

    // ── Sheet data from server ──
    const sheetData = @json($document->body_json ?? ['sheets' => [['name' => 'Sheet1', 'data' => [[]]]], 'activeSheet' => 0]);

    // Ensure sheets array exists
    if (!sheetData.sheets || !sheetData.sheets.length) {
        sheetData.sheets = [{ name: 'Sheet1', data: [[]], style: {}, colWidths: [], merged: {}, frozen: { rows: 0, cols: 0 } }];
    }
    if (typeof sheetData.activeSheet === 'undefined') {
        sheetData.activeSheet = 0;
    }

    // ── Auto-save state ──
    let saveTimer = null;
    let saving = false;
    let currentVersion = {{ $document->version }};

    function updateSaveStatus(status) {
        const el = document.getElementById('save-status');
        if (!el) return;
        switch(status) {
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
                el.className = 'text-gray-500 text-xs';
                break;
            case 'error':
                el.textContent = 'Save failed — retrying...';
                el.className = 'text-red-400 text-xs';
                break;
        }
    }

    function scheduleAutoSave() {
        if (!canEdit) return;
        updateSaveStatus('unsaved');
        clearTimeout(saveTimer);
        saveTimer = setTimeout(autoSave, 2000);
    }

    function getSpreadsheetData() {
        const ws = window._spreadsheet;
        const currentSheets = sheetData.sheets.map((sheet, idx) => {
            if (idx === window._activeSheetIdx && ws) {
                return {
                    name: sheet.name,
                    data: ws.getData ? ws.getData() : (sheet.data || [[]]),
                    style: ws.getStyle ? ws.getStyle() : (sheet.style || {}),
                    colWidths: sheet.colWidths || [],
                    merged: ws.getMerge ? ws.getMerge() : (sheet.merged || {}),
                    frozen: sheet.frozen || { rows: 0, cols: 0 },
                };
            }
            return sheet;
        });
        return {
            sheets: currentSheets,
            activeSheet: window._activeSheetIdx || 0,
        };
    }

    async function autoSave() {
        if (saving || !canEdit) return;
        saving = true;
        updateSaveStatus('saving');
        try {
            const res = await fetch(`/api/docs/documents/${docId}/auto-save`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    body_json: getSpreadsheetData(),
                    title: document.getElementById('doc-title-input')?.value || '',
                    version: currentVersion,
                }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                currentVersion = data.version;
                updateSaveStatus('saved');
            } else if (res.status === 409) {
                updateSaveStatus('error');
                alert('This spreadsheet was modified by someone else. Please reload to get the latest version.');
            } else {
                updateSaveStatus('error');
                setTimeout(autoSave, 5000);
            }
        } catch (e) {
            updateSaveStatus('error');
            setTimeout(autoSave, 5000);
        } finally {
            saving = false;
        }
    }

    // ── Build columns from sheet config ──
    function buildColumns(sheet) {
        const colCount = Math.max(26, (sheet.data && sheet.data[0]) ? sheet.data[0].length : 26);
        const cols = [];
        for (let i = 0; i < colCount; i++) {
            cols.push({
                width: (sheet.colWidths && sheet.colWidths[i]) ? sheet.colWidths[i] : 120,
            });
        }
        return cols;
    }

    // ── Initialize jspreadsheet ──
    let activeSheetIdx = sheetData.activeSheet || 0;
    const activeSheet = sheetData.sheets[activeSheetIdx];
    window._activeSheetIdx = activeSheetIdx;

    // jspreadsheet-ce v5 uses 'worksheets' array
    const spreadsheet = jspreadsheet(document.getElementById('spreadsheet-container'), {
        worksheets: [{
            data: (activeSheet.data && activeSheet.data.length) ? activeSheet.data : [[]],
            columns: buildColumns(activeSheet),
            style: activeSheet.style || {},
            minDimensions: [26, 50],
            tableOverflow: true,
            tableWidth: '100%',
            tableHeight: (window.innerHeight - 160) + 'px',
            columnSorting: true,
            columnResize: true,
            rowResize: true,
            allowComments: false,
            search: true,
            editable: canEdit,
        }],
        onchange: () => scheduleAutoSave(),
        oninsertrow: () => scheduleAutoSave(),
        oninsertcolumn: () => scheduleAutoSave(),
        ondeleterow: () => scheduleAutoSave(),
        ondeletecolumn: () => scheduleAutoSave(),
        onmoverow: () => scheduleAutoSave(),
        onsort: () => scheduleAutoSave(),
        onresizecolumn: (el, col, width) => {
            const sheet = sheetData.sheets[window._activeSheetIdx];
            if (!sheet.colWidths) sheet.colWidths = [];
            sheet.colWidths[col] = width;
            scheduleAutoSave();
        },
        onmerge: () => scheduleAutoSave(),
        onselection: (worksheet, px, py, ux, uy, origin) => {
            const cellName = jspreadsheet.helpers.getColumnNameFromCoords(px, py);
            const ws = spreadsheet[0];
            const value = ws.getValueFromCoords(px, py);
            window.dispatchEvent(new CustomEvent('cell-selected', {
                detail: { cell: cellName, value: value || '', col: px, row: py }
            }));
        },
        oneditionstart: (worksheet, cell, x, y) => {
            const ws = spreadsheet[0];
            const value = ws.getValueFromCoords(x, y);
            window.dispatchEvent(new CustomEvent('cell-editing', {
                detail: { value: value || '', col: x, row: y }
            }));
        },
        oneditionend: (worksheet, cell, x, y, value) => {
            window.dispatchEvent(new CustomEvent('cell-edited', {
                detail: { value: value || '', col: x, row: y }
            }));
        },
    });

    // In v5, spreadsheet is an array; first element is the active worksheet
    window._spreadsheet = spreadsheet[0] || spreadsheet;

    // ── Helper: get selected cells ──
    function getSelectedCells() {
        const ws = window._spreadsheet;
        const selected = ws.getSelected ? ws.getSelected(true) : [];
        if (!selected || !selected.length) return [];
        return selected;
    }

    function getSelectedCellNames() {
        const sel = spreadsheet.getSelectedColumns ? null : null;
        // Use highlighted property
        const highlighted = spreadsheet.highlighted || [];
        if (highlighted.length) {
            return highlighted.map(cell => {
                const id = cell.getAttribute('data-x') + '-' + cell.getAttribute('data-y');
                return jspreadsheet.getColumnNameFromId(id.split('-'));
            });
        }
        return [];
    }

    // Expose functions for Alpine
    window._scheduleAutoSave = scheduleAutoSave;
    window._getSelectedCells = getSelectedCells;
    window._getSelectedCellNames = getSelectedCellNames;
    window._sheetData = sheetData;

    // ── Save before leaving ──
    window.addEventListener('beforeunload', (e) => {
        const status = document.getElementById('save-status')?.textContent;
        if (status === 'Unsaved changes') {
            e.preventDefault();
        }
    });

    // ── Title change auto-save ──
    window.addEventListener('title-changed', () => {
        scheduleAutoSave();
    });
});

// ── Alpine component ──
function spreadsheetApp() {
    return {
        selectedCell: 'A1',
        formulaBarValue: '',
        formulaBarFocused: false,
        activeStyles: {},
        textColor: '#e2e2ee',
        bgColor: '#17172a',
        sheets: [],
        activeSheetIdx: 0,
        sheetMenu: { visible: false, x: 0, y: 0, idx: 0 },

        colorPalette: [
            '#ffffff', '#e2e2ee', '#a0a0b8', '#666680', '#333348', '#000000',
            '#ef4444', '#f97316', '#eab308', '#22c55e', '#10b981', '#06b6d4',
            '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e',
            '#fca5a5', '#fdba74', '#fde047', '#86efac', '#6ee7b7', '#67e8f9',
            '#93c5fd', '#a5b4fc', '#c4b5fd', '#d8b4fe', '#f9a8d4', '#fda4af',
        ],

        bgPalette: [
            'transparent', '#17172a', '#1e1e36', '#2a2a44', '#333355', '#444466',
            '#3b1010', '#3b2010', '#3b3510', '#103b10', '#103b2a', '#103035',
            '#101a3b', '#1a103b', '#25103b', '#2e103b', '#3b1030', '#3b1020',
            '#ef4444', '#f97316', '#eab308', '#22c55e', '#10b981', '#06b6d4',
            '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e',
        ],

        init() {
            const self = this;

            // Initialize sheets from data after DOM is ready
            this.$nextTick(() => {
                const data = window._sheetData;
                if (data && data.sheets) {
                    this.sheets = data.sheets.map(s => ({ name: s.name || 'Sheet1' }));
                    this.activeSheetIdx = data.activeSheet || 0;
                } else {
                    this.sheets = [{ name: 'Sheet1' }];
                }
            });

            // Listen for cell selection
            window.addEventListener('cell-selected', (e) => {
                self.selectedCell = e.detail.cell || 'A1';
                self.formulaBarValue = e.detail.value || '';
                self.updateActiveStyles(e.detail.col, e.detail.row);
            });

            window.addEventListener('cell-editing', (e) => {
                self.formulaBarValue = e.detail.value || '';
            });

            window.addEventListener('cell-edited', (e) => {
                self.formulaBarValue = e.detail.value || '';
            });
        },

        updateActiveStyles(col, row) {
            if (!window._spreadsheet) return;
            const cellName = jspreadsheet.getColumnNameFromId([col, row]);
            const style = window._spreadsheet.getStyle ? window._spreadsheet.getStyle(cellName) : '';
            this.activeStyles = {};
            if (style && typeof style === 'string') {
                style.split(';').forEach(rule => {
                    const [prop, val] = rule.split(':').map(s => s.trim());
                    if (prop && val) this.activeStyles[prop] = val;
                });
            }
        },

        applyStyle(prop, value) {
            if (!window._spreadsheet) return;
            const highlighted = window._spreadsheet.highlighted || [];
            if (!highlighted.length) return;

            highlighted.forEach(cell => {
                const x = cell.getAttribute('data-x');
                const y = cell.getAttribute('data-y');
                const cellName = jspreadsheet.getColumnNameFromId([x, y]);

                // Toggle: if same value already applied, remove it
                const currentStyle = window._spreadsheet.getStyle ? window._spreadsheet.getStyle(cellName) : '';
                let styles = {};
                if (currentStyle && typeof currentStyle === 'string') {
                    currentStyle.split(';').forEach(rule => {
                        const [p, v] = rule.split(':').map(s => s.trim());
                        if (p && v) styles[p] = v;
                    });
                }

                if (styles[prop] === value && (prop === 'font-weight' || prop === 'font-style' || prop === 'text-decoration')) {
                    delete styles[prop];
                } else {
                    styles[prop] = value;
                }

                const styleStr = Object.entries(styles).map(([k, v]) => `${k}:${v}`).join(';');
                window._spreadsheet.setStyle(cellName, styleStr);
            });

            this.activeStyles[prop] = this.activeStyles[prop] === value ? '' : value;
            window._scheduleAutoSave();
        },

        applyBorder(prop, value) {
            if (!window._spreadsheet) return;
            const highlighted = window._spreadsheet.highlighted || [];
            if (!highlighted.length) return;

            highlighted.forEach(cell => {
                const x = cell.getAttribute('data-x');
                const y = cell.getAttribute('data-y');
                const cellName = jspreadsheet.getColumnNameFromId([x, y]);
                const currentStyle = window._spreadsheet.getStyle ? window._spreadsheet.getStyle(cellName) : '';
                let styles = {};
                if (currentStyle && typeof currentStyle === 'string') {
                    currentStyle.split(';').forEach(rule => {
                        const [p, v] = rule.split(':').map(s => s.trim());
                        if (p && v) styles[p] = v;
                    });
                }
                if (value) {
                    styles[prop] = value;
                } else {
                    // Clear all border properties
                    ['border', 'border-top', 'border-bottom', 'border-left', 'border-right'].forEach(b => delete styles[b]);
                }
                const styleStr = Object.entries(styles).map(([k, v]) => `${k}:${v}`).join(';');
                window._spreadsheet.setStyle(cellName, styleStr);
            });
            window._scheduleAutoSave();
        },

        toggleMerge() {
            if (!window._spreadsheet) return;
            const sel = window._spreadsheet.selectedCell;
            if (!sel || !sel.length) return;

            const c1 = sel[0];
            const c2 = sel[sel.length - 1];
            const x1 = parseInt(c1.getAttribute('data-x'));
            const y1 = parseInt(c1.getAttribute('data-y'));
            const x2 = parseInt(c2.getAttribute('data-x'));
            const y2 = parseInt(c2.getAttribute('data-y'));
            const cellName = jspreadsheet.getColumnNameFromId([x1, y1]);
            const colspan = x2 - x1 + 1;
            const rowspan = y2 - y1 + 1;

            if (colspan > 1 || rowspan > 1) {
                // Check if already merged, then unmerge
                const merges = window._spreadsheet.getMerge ? window._spreadsheet.getMerge() : {};
                if (merges[cellName]) {
                    window._spreadsheet.removeMerge(cellName);
                } else {
                    window._spreadsheet.setMerge(cellName, colspan, rowspan);
                }
                window._scheduleAutoSave();
            }
        },

        applyNumberFormat(format) {
            // Number formatting for selected cells (stores as metadata)
            // Since jspreadsheet-ce has limited format support, we apply visual formatting
            if (!window._spreadsheet) return;
            const highlighted = window._spreadsheet.highlighted || [];
            highlighted.forEach(cell => {
                const x = parseInt(cell.getAttribute('data-x'));
                const y = parseInt(cell.getAttribute('data-y'));
                const val = window._spreadsheet.getValueFromCoords(x, y);
                if (val === '' || val === null) return;

                let formatted = val;
                const num = parseFloat(val);

                if (!isNaN(num)) {
                    switch(format) {
                        case 'number':
                            formatted = num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            break;
                        case 'percent':
                            formatted = (num * 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
                            break;
                        case 'currency':
                            formatted = '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            break;
                        case 'date':
                            // Treat as Excel serial date or pass through
                            formatted = val;
                            break;
                    }
                }
                window._spreadsheet.setValueFromCoords(x, y, formatted);
            });
            window._scheduleAutoSave();
        },

        updateCellFromFormulaBar() {
            if (!window._spreadsheet) return;
            const highlighted = window._spreadsheet.highlighted || [];
            if (highlighted.length) {
                const cell = highlighted[0];
                const x = parseInt(cell.getAttribute('data-x'));
                const y = parseInt(cell.getAttribute('data-y'));
                window._spreadsheet.setValueFromCoords(x, y, this.formulaBarValue);
            }
        },

        // ── Sheet tab management ──
        switchSheet(idx) {
            if (idx === this.activeSheetIdx) return;

            // Save current sheet data
            if (window._spreadsheet) {
                const currentSheet = window._sheetData.sheets[this.activeSheetIdx];
                if (currentSheet) {
                    currentSheet.data = window._spreadsheet.getData();
                    currentSheet.style = window._spreadsheet.getStyle ? window._spreadsheet.getStyle() : {};
                    currentSheet.merged = window._spreadsheet.getMerge ? window._spreadsheet.getMerge() : {};
                }
            }

            this.activeSheetIdx = idx;
            window._activeSheetIdx = idx;
            const sheet = window._sheetData.sheets[idx];

            // Destroy and recreate
            const container = document.getElementById('spreadsheet-container');
            container.innerHTML = '';

            const newSpreadsheet = jspreadsheet(container, {
                data: (sheet.data && sheet.data.length) ? sheet.data : [[]],
                columns: this._buildColumns(sheet),
                style: sheet.style || {},
                minDimensions: [26, 100],
                tableOverflow: true,
                tableWidth: '100%',
                tableHeight: 'calc(100vh - 140px)',
                columnSorting: true,
                columnResize: true,
                rowResize: true,
                allowComments: false,
                search: true,
                editable: @json($canEdit),
                onchange: () => window._scheduleAutoSave(),
                oninsertrow: () => window._scheduleAutoSave(),
                oninsertcolumn: () => window._scheduleAutoSave(),
                ondeleterow: () => window._scheduleAutoSave(),
                ondeletecolumn: () => window._scheduleAutoSave(),
                onmoverow: () => window._scheduleAutoSave(),
                onsort: () => window._scheduleAutoSave(),
                onresizecolumn: (el, col, width) => {
                    const s = window._sheetData.sheets[window._activeSheetIdx];
                    if (!s.colWidths) s.colWidths = [];
                    s.colWidths[col] = width;
                    window._scheduleAutoSave();
                },
                onmerge: () => window._scheduleAutoSave(),
                onselection: (el, borderLeft, borderTop) => {
                    const cellName = jspreadsheet.getColumnNameFromId([borderLeft, borderTop]);
                    const value = newSpreadsheet.getValueFromCoords(borderLeft, borderTop);
                    window.dispatchEvent(new CustomEvent('cell-selected', {
                        detail: { cell: cellName, value: value || '', col: borderLeft, row: borderTop }
                    }));
                },
                oneditionstart: (el, cell, x, y) => {
                    const value = newSpreadsheet.getValueFromCoords(x, y);
                    window.dispatchEvent(new CustomEvent('cell-editing', {
                        detail: { value: value || '', col: x, row: y }
                    }));
                },
                oneditionend: (el, cell, x, y, value) => {
                    window.dispatchEvent(new CustomEvent('cell-edited', {
                        detail: { value: value || '', col: x, row: y }
                    }));
                },
            });

            window._spreadsheet = newSpreadsheet;
            window._scheduleAutoSave();
        },

        addSheet() {
            // Save current sheet data first
            if (window._spreadsheet) {
                const currentSheet = window._sheetData.sheets[this.activeSheetIdx];
                if (currentSheet) {
                    currentSheet.data = window._spreadsheet.getData();
                    currentSheet.style = window._spreadsheet.getStyle ? window._spreadsheet.getStyle() : {};
                    currentSheet.merged = window._spreadsheet.getMerge ? window._spreadsheet.getMerge() : {};
                }
            }

            const newName = 'Sheet' + (this.sheets.length + 1);
            const newSheet = {
                name: newName,
                data: [[]],
                style: {},
                colWidths: [],
                merged: {},
                frozen: { rows: 0, cols: 0 },
            };
            window._sheetData.sheets.push(newSheet);
            this.sheets.push({ name: newName });
            this.switchSheet(this.sheets.length - 1);
        },

        openSheetMenu(event, idx) {
            this.sheetMenu = {
                visible: true,
                x: event.clientX,
                y: event.clientY,
                idx: idx,
            };
        },

        renameSheet(idx) {
            this.sheetMenu.visible = false;
            const currentName = this.sheets[idx].name;
            const newName = prompt('Rename sheet:', currentName);
            if (newName && newName.trim()) {
                this.sheets[idx].name = newName.trim();
                window._sheetData.sheets[idx].name = newName.trim();
                window._scheduleAutoSave();
            }
        },

        duplicateSheet(idx) {
            this.sheetMenu.visible = false;

            // Save current data if duplicating the active sheet
            if (idx === this.activeSheetIdx && window._spreadsheet) {
                const currentSheet = window._sheetData.sheets[idx];
                currentSheet.data = window._spreadsheet.getData();
                currentSheet.style = window._spreadsheet.getStyle ? window._spreadsheet.getStyle() : {};
                currentSheet.merged = window._spreadsheet.getMerge ? window._spreadsheet.getMerge() : {};
            }

            const source = window._sheetData.sheets[idx];
            const copy = JSON.parse(JSON.stringify(source));
            copy.name = source.name + ' (Copy)';
            window._sheetData.sheets.push(copy);
            this.sheets.push({ name: copy.name });
            this.switchSheet(this.sheets.length - 1);
        },

        deleteSheet(idx) {
            this.sheetMenu.visible = false;
            if (this.sheets.length <= 1) return;
            if (!confirm('Delete "' + this.sheets[idx].name + '"?')) return;

            window._sheetData.sheets.splice(idx, 1);
            this.sheets.splice(idx, 1);

            // Adjust active index
            if (this.activeSheetIdx >= this.sheets.length) {
                this.switchSheet(this.sheets.length - 1);
            } else if (this.activeSheetIdx === idx) {
                this.switchSheet(Math.max(0, idx - 1));
            } else if (this.activeSheetIdx > idx) {
                this.activeSheetIdx--;
                window._activeSheetIdx = this.activeSheetIdx;
            }

            window._scheduleAutoSave();
        },

        _buildColumns(sheet) {
            const colCount = Math.max(26, (sheet.data && sheet.data[0]) ? sheet.data[0].length : 26);
            const cols = [];
            for (let i = 0; i < colCount; i++) {
                cols.push({
                    width: (sheet.colWidths && sheet.colWidths[i]) ? sheet.colWidths[i] : 120,
                });
            }
            return cols;
        },
    };
}
</script>
@endpush

</x-layouts.docs-editor>
