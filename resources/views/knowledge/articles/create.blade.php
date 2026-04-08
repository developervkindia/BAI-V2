<x-layouts.knowledge title="New article" currentView="create">
    @php
        $org = auth()->user()->currentOrganization();
        $kbAttach = auth()->user()->is_super_admin
            || ($org && app(\App\Services\PlanService::class)->canUse($org, 'knowledge_base', 'attachments'));
    @endphp
    <h1 class="text-[22px] font-bold text-white/90 mb-6">New article</h1>
    <form method="post" action="{{ route('knowledge.articles.store') }}" class="space-y-5 max-w-4xl" onsubmit="if (typeof tinymce !== 'undefined') tinymce.triggerSave();">
        @csrf
        <div>
            <label class="block text-[12px] font-medium text-white/45 mb-1.5">Category</label>
            <select name="knowledge_category_id" required class="w-full max-w-md rounded-lg bg-white/[0.06] border border-white/[0.1] px-3 py-2 text-[14px] text-white/85">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((int) old('knowledge_category_id', $selectedCategoryId) === $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[12px] font-medium text-white/45 mb-1.5">Title</label>
            <input name="title" value="{{ old('title') }}" required class="w-full rounded-lg bg-white/[0.06] border border-white/[0.1] px-3 py-2 text-[14px] text-white/85"/>
        </div>
        <div>
            <label class="block text-[12px] font-medium text-white/45 mb-1.5">Tags <span class="text-white/25 font-normal">(comma-separated)</span></label>
            <input name="tag_input" value="{{ old('tag_input') }}" placeholder="laravel, onboarding, security" class="w-full rounded-lg bg-white/[0.06] border border-white/[0.1] px-3 py-2 text-[14px] text-white/85"/>
        </div>
        <div>
            <label class="block text-[12px] font-medium text-white/45 mb-1.5">Excerpt <span class="text-white/25 font-normal">(optional)</span></label>
            <textarea name="excerpt" rows="2" class="w-full rounded-lg bg-white/[0.06] border border-white/[0.1] px-3 py-2 text-[14px] text-white/85">{{ old('excerpt') }}</textarea>
        </div>
        <div>
            <label class="block text-[12px] font-medium text-white/45 mb-1.5">Content</label>
            <textarea id="kb-body-html" name="body_html" rows="16" class="w-full rounded-lg bg-white/[0.06] border border-white/[0.1] text-[14px] text-white/85">{{ old('body_html', '<p></p>') }}</textarea>
        </div>
        <div class="flex flex-wrap gap-4 items-center">
            <label class="flex items-center gap-2 text-[13px] text-white/55">
                <select name="status" class="rounded-lg bg-white/[0.06] border border-white/[0.1] px-3 py-2 text-[13px] text-white/85">
                    <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status') === 'published')>Published</option>
                </select>
            </label>
            @can_permission('knowledge.moderate')
            <label class="flex items-center gap-2 text-[13px] text-white/55">
                <input type="checkbox" name="pinned" value="1" @checked(old('pinned')) class="rounded border-white/20 bg-white/5"/>
                Pin in category
            </label>
            @endif
        </div>
        @if($kbAttach)
        <div x-data="kbAttachments()" class="space-y-2">
            <label class="block text-[12px] font-medium text-white/45">Attachments</label>
            <input type="file" @change="upload($event)" class="text-[12px] text-white/45"/>
            <ul class="text-[12px] text-white/50 space-y-1">
                <template x-for="id in ids" :key="id">
                    <li x-text="'Attachment #' + id"></li>
                </template>
            </ul>
            <template x-for="id in ids" :key="'h'+id">
                <input type="hidden" :name="'pending_attachment_ids[]'" :value="id"/>
            </template>
        </div>
        @endif
        <div class="flex gap-3">
            <button type="submit" class="rounded-lg bg-sky-500/25 border border-sky-500/35 text-sky-100 px-5 py-2 text-[13px] font-medium">Save</button>
            <a href="{{ route('knowledge.index') }}" class="rounded-lg border border-white/[0.1] text-white/50 px-5 py-2 text-[13px]">Cancel</a>
        </div>
    </form>

    @push('knowledge-scripts')
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof tinymce === 'undefined') return;
        tinymce.init({
            selector: '#kb-body-html',
            license_key: 'gpl',
            promotion: false,
            height: 520,
            menubar: true,
            plugins: 'advlist autolink lists link image charmap anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount codesample emoticons directionality',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table codesample | charmap emoticons | removeformat | anchor searchreplace code fullscreen help',
            relative_urls: false,
            remove_script_host: false,
            document_base_url: @json(rtrim(url('/'), '/').'/'),
            @if($kbAttach)
            images_upload_handler: function (blobInfo) {
                return new Promise(function (resolve, reject) {
                    var fd = new FormData();
                    fd.append('file', blobInfo.blob(), blobInfo.filename());
                    fetch(@json(route('knowledge.upload.image')), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: fd
                    }).then(function (r) { if (!r.ok) throw r; return r.json(); })
                      .then(function (j) { if (j.location) resolve(j.location); else reject('Upload failed'); })
                      .catch(function () { reject('Upload failed'); });
                });
            },
            @endif
        });
    });
    @if($kbAttach)
    function kbAttachments() {
        return {
            ids: [],
            upload(e) {
                var f = e.target.files[0];
                if (!f) return;
                var fd = new FormData();
                fd.append('file', f);
                fetch(@json(route('knowledge.upload.attachment')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                }).then(r => r.json()).then(j => { if (j.id) this.ids.push(j.id); });
                e.target.value = '';
            }
        };
    }
    @endif
    </script>
    @endpush
</x-layouts.knowledge>
