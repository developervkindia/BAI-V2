<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeArticleRevision;
use App\Services\KnowledgeHtmlSanitizerService;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KnowledgeRevisionController extends Controller
{
    public function __construct(
        protected KnowledgeHtmlSanitizerService $sanitizer,
        protected PlanService $planService,
    ) {}

    public function index(Request $request, KnowledgeArticle $knowledge_article): View
    {
        $this->authorize('view', $knowledge_article);

        $org = $request->user()->currentOrganization();
        if (! $this->planService->canUse($org, 'knowledge_base', 'revision_history') && ! $request->user()->is_super_admin) {
            abort(403, 'Revision history is not available on your plan.');
        }

        $revisions = $knowledge_article->revisions()->with('user')->paginate(25);

        return view('knowledge.articles.revisions', [
            'article' => $knowledge_article,
            'revisions' => $revisions,
        ]);
    }

    public function restore(Request $request, KnowledgeArticle $knowledge_article, KnowledgeArticleRevision $revision): RedirectResponse
    {
        $this->authorize('update', $knowledge_article);

        abort_unless($revision->knowledge_article_id === $knowledge_article->id, 404);

        $org = $request->user()->currentOrganization();
        if (! $this->planService->canUse($org, 'knowledge_base', 'revision_history') && ! $request->user()->is_super_admin) {
            abort(403);
        }

        KnowledgeArticleRevision::create([
            'knowledge_article_id' => $knowledge_article->id,
            'user_id' => $request->user()->id,
            'title' => $knowledge_article->title,
            'body_html' => $knowledge_article->body_html,
            'created_at' => now(),
        ]);

        $knowledge_article->title = $revision->title;
        $knowledge_article->body_html = $this->sanitizer->sanitize($revision->body_html);
        $knowledge_article->excerpt = Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($knowledge_article->body_html))), 280, '…');
        $knowledge_article->save();

        return redirect()->route('knowledge.articles.revisions.index', $knowledge_article)
            ->with('success', 'Revision restored.');
    }
}
