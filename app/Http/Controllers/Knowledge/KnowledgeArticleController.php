<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleRevision;
use App\Models\KnowledgeAttachment;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeTag;
use App\Services\KnowledgeHtmlSanitizerService;
use App\Services\KnowledgeQuotaService;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KnowledgeArticleController extends Controller
{
    public function __construct(
        protected KnowledgeHtmlSanitizerService $sanitizer,
        protected KnowledgeQuotaService $quota,
        protected PlanService $planService,
    ) {}

    public function show(Request $request, KnowledgeArticle $knowledge_article): View
    {
        $this->authorize('view', $knowledge_article);

        $knowledge_article->load(['category', 'author', 'tags', 'attachments']);

        return view('knowledge.articles.show', ['article' => $knowledge_article]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', KnowledgeArticle::class);

        $org = $request->user()->currentOrganization();
        abort_unless($this->quota->canCreateArticle($org), 403, 'Article limit reached for your plan.');

        $categories = KnowledgeCategory::where('organization_id', $org->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($categories->isEmpty()) {
            if ($request->user()->can('create', KnowledgeCategory::class)) {
                return redirect()->route('knowledge.categories.create')
                    ->with('error', 'Create a category first, then you can add articles.');
            }

            return redirect()->route('knowledge.index')
                ->with('error', 'No categories yet. A knowledge moderator must create one before articles can be added.');
        }

        $selectedCategoryId = (int) old('knowledge_category_id', $request->query('knowledge_category_id'));

        return view('knowledge.articles.create', compact('categories', 'selectedCategoryId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', KnowledgeArticle::class);

        $org = $request->user()->currentOrganization();
        abort_unless($this->quota->canCreateArticle($org), 403, 'Article limit reached for your plan.');

        $data = $this->validatedArticle($request, $org->id);
        $bodyHtml = $this->sanitizer->sanitize($data['body_html']);

        $article = new KnowledgeArticle;
        $article->organization_id = $org->id;
        $article->author_id = $request->user()->id;
        $article->knowledge_category_id = $data['knowledge_category_id'];
        $article->title = $data['title'];
        $article->slug = KnowledgeArticle::uniqueSlugForOrg($org->id, $data['title']);
        $article->excerpt = $data['excerpt'] ?? $this->makeExcerpt($bodyHtml);
        $article->body_html = $bodyHtml;
        $article->status = $data['status'];
        $article->pinned = $request->boolean('pinned')
            && $request->user()->can('create', KnowledgeCategory::class);
        if ($article->status === 'published') {
            $article->published_at = now();
        }
        $article->save();

        $this->syncTags($article, $data['tag_input'] ?? '', $org->id);
        $this->linkPendingAttachments($article, $data['pending_attachment_ids'] ?? [], $request->user()->id);

        return redirect()->route('knowledge.articles.show', $article)
            ->with('success', 'Article created.');
    }

    public function edit(Request $request, KnowledgeArticle $knowledge_article): View
    {
        $this->authorize('update', $knowledge_article);

        $org = $request->user()->currentOrganization();
        $categories = KnowledgeCategory::where('organization_id', $org->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $knowledge_article->load(['tags', 'attachments']);

        return view('knowledge.articles.edit', [
            'article' => $knowledge_article,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, KnowledgeArticle $knowledge_article): RedirectResponse
    {
        $this->authorize('update', $knowledge_article);

        $org = $request->user()->currentOrganization();
        $data = $this->validatedArticle($request, $org->id, $knowledge_article->id);
        $bodyHtml = $this->sanitizer->sanitize($data['body_html']);

        $revisionsEnabled = $this->planService->canUse($org, 'knowledge_base', 'revision_history')
            || $request->user()->is_super_admin;

        if ($revisionsEnabled && $knowledge_article->exists
            && ($knowledge_article->title !== $data['title'] || $knowledge_article->body_html !== $bodyHtml)) {
            KnowledgeArticleRevision::create([
                'knowledge_article_id' => $knowledge_article->id,
                'user_id' => $request->user()->id,
                'title' => $knowledge_article->title,
                'body_html' => $knowledge_article->body_html,
                'created_at' => now(),
            ]);
        }

        $knowledge_article->knowledge_category_id = $data['knowledge_category_id'];
        $knowledge_article->title = $data['title'];
        if (! empty($data['slug'])) {
            $knowledge_article->slug = $data['slug'];
        } else {
            $knowledge_article->slug = KnowledgeArticle::uniqueSlugForOrg($org->id, $data['title'], $knowledge_article->id);
        }
        $knowledge_article->excerpt = $data['excerpt'] ?? $this->makeExcerpt($bodyHtml);
        $knowledge_article->body_html = $bodyHtml;

        $wasPublished = $knowledge_article->status === 'published';
        $knowledge_article->status = $data['status'];
        if ($knowledge_article->status === 'published' && ! $wasPublished) {
            $knowledge_article->published_at = now();
        }
        if ($request->user()->can('create', KnowledgeCategory::class)) {
            $knowledge_article->pinned = $request->boolean('pinned');
        }
        $knowledge_article->save();

        $this->syncTags($knowledge_article, $data['tag_input'] ?? '', $org->id);
        $this->linkPendingAttachments($knowledge_article, $data['pending_attachment_ids'] ?? [], $request->user()->id);

        return redirect()->route('knowledge.articles.show', $knowledge_article)
            ->with('success', 'Article updated.');
    }

    public function destroy(Request $request, KnowledgeArticle $knowledge_article): RedirectResponse
    {
        $this->authorize('delete', $knowledge_article);

        $category = $knowledge_article->category;
        $knowledge_article->delete();

        return redirect()->route('knowledge.categories.show', $category)
            ->with('success', 'Article moved to trash.');
    }

    public function trash(Request $request): View
    {
        $this->authorize('create', KnowledgeCategory::class);

        $org = $request->user()->currentOrganization();
        $articles = KnowledgeArticle::onlyTrashed()
            ->where('organization_id', $org->id)
            ->with(['category', 'author'])
            ->orderByDesc('deleted_at')
            ->paginate(30);

        return view('knowledge.articles.trash', compact('articles'));
    }

    public function restore(Request $request, int $trashedArticle): RedirectResponse
    {
        $org = $request->user()->currentOrganization();
        $article = KnowledgeArticle::onlyTrashed()
            ->where('organization_id', $org->id)
            ->whereKey($trashedArticle)
            ->firstOrFail();

        $this->authorize('restore', $article);

        $article->restore();

        return redirect()->route('knowledge.articles.show', $article)
            ->with('success', 'Article restored.');
    }

    public function forceDestroy(Request $request, int $trashedArticle): RedirectResponse
    {
        $org = $request->user()->currentOrganization();
        $article = KnowledgeArticle::onlyTrashed()
            ->where('organization_id', $org->id)
            ->whereKey($trashedArticle)
            ->firstOrFail();

        $this->authorize('forceDelete', $article);

        foreach ($article->attachments as $att) {
            $att->deleteFile();
            $att->delete();
        }
        $article->revisions()->delete();
        $article->tags()->detach();
        $article->forceDelete();

        return redirect()->route('knowledge.trash')->with('success', 'Article permanently deleted.');
    }

    private function validatedArticle(Request $request, int $organizationId, ?int $ignoreArticleId = null): array
    {
        return $request->validate([
            'knowledge_category_id' => [
                'required',
                Rule::exists('knowledge_categories', 'id')->where(fn ($q) => $q->where('organization_id', $organizationId)),
            ],
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', Rule::unique('knowledge_articles', 'slug')->where('organization_id', $organizationId)->ignore($ignoreArticleId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body_html' => ['required', 'string'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'tag_input' => ['nullable', 'string', 'max:2000'],
            'pending_attachment_ids' => ['nullable', 'array'],
            'pending_attachment_ids.*' => ['integer', 'exists:knowledge_attachments,id'],
        ]);
    }

    private function makeExcerpt(string $html): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($html)));

        return Str::limit($plain, 280, '…');
    }

    private function syncTags(KnowledgeArticle $article, string $tagInput, int $organizationId): void
    {
        $names = collect(preg_split('/[,;]+/', $tagInput))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->unique(fn ($n) => Str::lower($n))
            ->take(20);

        $ids = [];
        foreach ($names as $name) {
            if (strlen($name) > 64) {
                continue;
            }
            $existing = KnowledgeTag::where('organization_id', $organizationId)
                ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
                ->first();
            if ($existing) {
                $ids[] = $existing->id;

                continue;
            }
            $tag = KnowledgeTag::create([
                'organization_id' => $organizationId,
                'name' => $name,
            ]);
            $ids[] = $tag->id;
        }
        $article->tags()->sync($ids);
    }

    private function linkPendingAttachments(KnowledgeArticle $article, array $ids, int $userId): void
    {
        if ($ids === []) {
            return;
        }

        KnowledgeAttachment::whereIn('id', $ids)
            ->where('organization_id', $article->organization_id)
            ->where('uploaded_by', $userId)
            ->whereNull('knowledge_article_id')
            ->update(['knowledge_article_id' => $article->id]);
    }
}
