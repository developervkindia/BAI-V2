<x-layouts.docs-editor
    :title="$document->title"
    :document="$document"
    :canEdit="$canEdit"
>

@push('editor-head')
    @vite('resources/js/docs-editor.js')
    <style>
        .tox-tinymce { border: none !important; border-radius: 0 !important; }
        .tox .tox-promotion { display: none !important; }
        /* Google Docs toolbar colors */
        .tox .tox-toolbar-overlord, .tox .tox-toolbar__primary { background: #edf2fa !important; }
        .tox .tox-menubar { background: #fff !important; }
        /* Hide status bar branding */
        .tox .tox-statusbar { border-top: none !important; }
        .tox .tox-statusbar__branding { display: none !important; }
    </style>
@endpush

{{-- TinyMCE fills this entire area --}}
<div style="height: calc(100vh - 3rem); width: 100%;">
    <textarea id="doc-editor">{!! $document->body_html !!}</textarea>
</div>

@if(!$canEdit)
    <div class="fixed top-16 right-4 z-40">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[12px] font-medium bg-white/90 text-gray-600 shadow border border-gray-200">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            Viewing
        </span>
    </div>
@endif

@push('editor-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const docId = {{ $document->id }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let saveTimer = null;
    let saving = false;
    let currentVersion = {{ $document->version }};

    tinymce.init({
        selector: '#doc-editor',
        license_key: 'gpl',
        promotion: false,

        // Skins from public/vendor
        skin_url: '/vendor/tinymce/skins/ui/oxide',
        content_css: '/vendor/tinymce/skins/content/default/content.min.css',

        // Plugins (no autoresize - we want height:100% to fill viewport)
        plugins: 'advlist autolink charmap code codesample emoticons fullscreen image insertdatetime link lists media nonbreaking pagebreak preview quickbars searchreplace table visualblocks wordcount',

        // Menu bar like Google Docs
        menubar: 'file edit view insert format table tools',

        // Compact toolbar matching Google Docs layout
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright | bullist numlist outdent indent | link image table codesample emoticons | removeformat fullscreen',
        toolbar_mode: 'sliding',

        // Quick toolbars on text selection
        quickbars_selection_toolbar: 'bold italic underline | blocks | forecolor backcolor | link',
        quickbars_insert_toolbar: 'image table hr',

        // Fill the container completely
        height: '100%',
        resize: false,

        // Font options
        font_family_formats: 'Arial=arial,helvetica,sans-serif; Comic Sans MS=comic sans ms; Courier New=courier new; Georgia=georgia; Impact=impact; Inter=Inter,sans-serif; Times New Roman=times new roman; Trebuchet MS=trebuchet ms; Verdana=verdana',
        font_size_formats: '8pt 9pt 10pt 11pt 12pt 14pt 18pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt',

        // Behavior
        placeholder: 'Start typing...',
        browser_spellcheck: true,
        contextmenu: 'link image table',
        paste_data_images: true,

        @if(!$canEdit)
        readonly: true,
        @endif

        // Google Docs page appearance
        content_style: `
            html { background: #f8f9fa; height: 100%; }
            body {
                font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #000;
                max-width: 816px; margin: 24px auto; padding: 96px;
                background: #fff; min-height: 1056px;
                box-shadow: 0 0 0 0.75pt #d1d1d1, 0 0 3pt 0.75pt #ccc;
            }
            p { margin: 0 0 8px; }
            h1 { font-size: 20pt; margin: 20pt 0 6pt; font-weight: 400; }
            h2 { font-size: 16pt; margin: 18pt 0 6pt; font-weight: 700; }
            h3 { font-size: 14pt; margin: 16pt 0 4pt; font-weight: 700; color: #434343; }
            table { border-collapse: collapse; width: 100%; }
            td, th { border: 1px solid #000; padding: 5px 8px; }
            img { max-width: 100%; height: auto; }
            blockquote { border-left: 3px solid #ccc; margin: 1em 0; padding: 0 0 0 1em; color: #666; }
            pre { background: #f5f5f5; border: 1px solid #ccc; padding: 8px 12px; font-family: Courier New, monospace; }
            code { font-family: Courier New, monospace; font-size: 0.9em; }
            a { color: #1155cc; }
            ul, ol { padding-left: 36px; }
        `,

        // Image upload
        images_upload_handler: (blobInfo) => new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            formData.append('document_id', docId);
            fetch('/docs/upload/image', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: formData,
            })
            .then(res => res.json())
            .then(data => resolve(data.location))
            .catch(() => reject('Image upload failed'));
        }),

        // Auto-save on changes
        setup: (editor) => {
            editor.on('input change undo redo', () => {
                updateSaveStatus('unsaved');
                clearTimeout(saveTimer);
                saveTimer = setTimeout(() => autoSave(editor), 1500);
            });

            // Expose auto-save globally so title input can trigger it
            editor.on('init', () => {
                window._triggerDocAutoSave = () => {
                    updateSaveStatus('unsaved');
                    clearTimeout(saveTimer);
                    saveTimer = setTimeout(() => autoSave(editor), 500);
                };
            });
        },
    });

    function updateSaveStatus(status) {
        const el = document.getElementById('save-status');
        if (!el) return;
        const map = {
            saving:  ['Saving...', 'text-yellow-400 text-xs'],
            saved:   ['All changes saved', 'text-emerald-400 text-xs'],
            unsaved: ['Unsaved changes', 'text-white/40 text-xs'],
            error:   ['Save failed — retrying...', 'text-red-400 text-xs'],
        };
        const [text, cls] = map[status] || map.unsaved;
        el.textContent = text;
        el.className = cls;
    }

    async function autoSave(editor) {
        if (saving) return;
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
                    body_html: editor.getContent(),
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
                alert('Document modified by someone else. Please reload.');
            } else {
                updateSaveStatus('error');
                setTimeout(() => autoSave(editor), 5000);
            }
        } catch (e) {
            updateSaveStatus('error');
            setTimeout(() => autoSave(editor), 5000);
        } finally {
            saving = false;
        }
    }

    window.addEventListener('beforeunload', (e) => {
        if (document.getElementById('save-status')?.textContent === 'Unsaved changes') {
            e.preventDefault();
        }
    });
});
</script>
@endpush

</x-layouts.docs-editor>
