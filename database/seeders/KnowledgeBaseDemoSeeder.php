<?php

namespace Database\Seeders;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeTag;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Sample Knowledge Hub content: 5 categories and 10 articles per organization.
 * Safe to run multiple times (upserts by organization + slug).
 * Runs for every org so impersonation / multi-tenant views see the same demo set.
 */
class KnowledgeBaseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Organization::query()->orderBy('id')->get();
        if ($orgs->isEmpty()) {
            $this->command?->warn('KnowledgeBaseDemoSeeder skipped: no organization exists.');

            return;
        }

        foreach ($orgs as $org) {
            $author = User::find($org->owner_id) ?? $org->members()->first();
            if (! $author) {
                $this->command?->warn("KnowledgeBaseDemoSeeder: skipped org #{$org->id} ({$org->name}) — no owner or member to use as author.");

                continue;
            }

            $categories = $this->seedCategories($org->id);
            $tagsBySlug = $this->seedTags($org->id);
            $this->seedArticles($org->id, $author->id, $categories, $tagsBySlug);
            $this->command?->info("KnowledgeBaseDemoSeeder: 5 categories, 10 articles for org #{$org->id} ({$org->name}).");
        }
    }

    /**
     * @param  array<string, KnowledgeCategory>  $categories
     * @param  array<string, int>  $tagsBySlug
     */
    private function seedArticles(int $organizationId, int $authorId, array $categories, array $tagsBySlug): void
    {
        foreach ($this->articleDefinitions() as $row) {
            $category = $categories[$row['category']] ?? null;
            if (! $category) {
                continue;
            }

            $publishedAt = isset($row['published_days_ago']) && $row['published_days_ago'] !== null
                ? Carbon::now()->subDays($row['published_days_ago'])
                : null;

            $article = KnowledgeArticle::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'slug' => $row['slug'],
                ],
                [
                    'knowledge_category_id' => $category->id,
                    'author_id' => $authorId,
                    'title' => $row['title'],
                    'excerpt' => $row['excerpt'],
                    'body_html' => $row['body_html'],
                    'status' => $row['status'],
                    'published_at' => $row['status'] === 'published' ? ($publishedAt ?? Carbon::now()->subDays(1)) : null,
                    'pinned' => $row['pinned'],
                ]
            );

            $tagIds = collect($row['tag_slugs'] ?? [])
                ->map(fn (string $slug) => $tagsBySlug[$slug] ?? null)
                ->filter()
                ->values()
                ->all();
            $article->tags()->sync($tagIds);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function articleDefinitions(): array
    {
        return [
            [
                'slug' => 'welcome-to-the-knowledge-hub',
                'category' => 'getting-started',
                'title' => 'Welcome to the Knowledge Hub',
                'excerpt' => 'How this space is organized and how to get the most from it.',
                'pinned' => true,
                'status' => 'published',
                'published_days_ago' => 14,
                'tag_slugs' => ['onboarding', 'handbook'],
                'body_html' => <<<'HTML'
<h2>What you will find here</h2>
<p>Internal documentation lives in one place: policies, how-tos, and team rituals. Use the home page to browse categories or search when you know what you need.</p>
<h2>Contributing</h2>
<p>If you have <strong>knowledge.contribute</strong>, you can draft and publish articles. Prefer clear titles, a short summary, and headings so others can scan quickly.</p>
<ul>
<li>Pick the right category before publishing.</li>
<li>Add tags that match how people search (e.g. <em>security</em>, <em>onboarding</em>).</li>
<li>Link related articles in the body when it helps.</li>
</ul>
HTML
            ],
            [
                'slug' => 'requesting-time-off',
                'category' => 'hr-people',
                'title' => 'Requesting time off',
                'excerpt' => 'Steps and lead times for PTO and leave requests.',
                'pinned' => false,
                'status' => 'published',
                'published_days_ago' => 30,
                'tag_slugs' => ['handbook', 'remote'],
                'body_html' => <<<'HTML'
<h2>Before you request</h2>
<p>Check your team calendar and project milestones. For blocks longer than three days, give your manager at least two weeks’ notice when possible.</p>
<h2>How to submit</h2>
<ol>
<li>Open your HR profile and choose <strong>Time off</strong>.</li>
<li>Select dates and leave type (PTO, sick, unpaid, etc.).</li>
<li>Add a short note for your manager if context helps.</li>
</ol>
<h2>After approval</h2>
<p>Update your calendar and Slack status. If you are on call, coordinate coverage with your team lead.</p>
HTML
            ],
            [
                'slug' => 'code-review-guidelines',
                'category' => 'engineering',
                'title' => 'Code review guidelines',
                'excerpt' => 'What we optimize for in reviews: clarity, safety, and speed.',
                'pinned' => false,
                'status' => 'published',
                'published_days_ago' => 7,
                'tag_slugs' => ['engineering', 'handbook'],
                'body_html' => <<<'HTML'
<h2>Goals</h2>
<p>Reviews should catch defects, spread context, and keep the codebase consistent—without becoming a bottleneck.</p>
<h2>For authors</h2>
<ul>
<li>Keep pull requests small when you can; link tickets and add screenshots for UI changes.</li>
<li>Describe <strong>what</strong> changed and <strong>why</strong>, not only the diff.</li>
<li>Respond to feedback within one business day.</li>
</ul>
<h2>For reviewers</h2>
<ul>
<li>Approve when requirements are met; use <em>request changes</em> only when merging would be risky.</li>
<li>Distinguish blocking issues from preferences; use comments for nits.</li>
</ul>
<blockquote>When in doubt, pair for ten minutes instead of ten comment rounds.</blockquote>
HTML
            ],
            [
                'slug' => 'api-versioning-policy',
                'category' => 'engineering',
                'title' => 'API versioning policy',
                'excerpt' => 'How we version public HTTP APIs and deprecate old surfaces.',
                'pinned' => false,
                'status' => 'published',
                'published_days_ago' => 45,
                'tag_slugs' => ['api', 'engineering'],
                'body_html' => <<<'HTML'
<h2>Version in the URL</h2>
<p>We use a path prefix such as <code>/v1/</code> and <code>/v2/</code>. Avoid breaking changes within a major version.</p>
<h2>Deprecation</h2>
<p>Announce deprecations in release notes and the developer changelog. Maintain the previous major version for at least <strong>six months</strong> after the successor is generally available.</p>
<table>
<thead><tr><th>Change type</th><th>Requires new major?</th></tr></thead>
<tbody>
<tr><td>New optional field</td><td>No</td></tr>
<tr><td>Removing a field</td><td>Yes</td></tr>
<tr><td>Changing semantics</td><td>Often yes</td></tr>
</tbody>
</table>
HTML
            ],
            [
                'slug' => 'password-and-mfa-basics',
                'category' => 'security-compliance',
                'title' => 'Password and MFA basics',
                'excerpt' => 'Minimum expectations for accounts that touch company or customer data.',
                'pinned' => false,
                'status' => 'published',
                'published_days_ago' => 60,
                'tag_slugs' => ['security', 'handbook'],
                'body_html' => <<<'HTML'
<h2>Passwords</h2>
<p>Use the company password manager. Do not reuse personal passwords for work systems.</p>
<h2>Multi-factor authentication</h2>
<p>MFA is required on email, SSO, cloud providers, and production access. Prefer an app-based TOTP or hardware keys over SMS when available.</p>
<h2>If you suspect compromise</h2>
<ol>
<li>Change the affected password and revoke sessions.</li>
<li>Notify IT Security in the incident channel.</li>
<li>Do not delete logs; preservation helps investigation.</li>
</ol>
HTML
            ],
            [
                'slug' => 'design-system-overview',
                'category' => 'product-design',
                'title' => 'Design system overview',
                'excerpt' => 'Tokens, components, and where to contribute design specs.',
                'pinned' => false,
                'status' => 'published',
                'published_days_ago' => 21,
                'tag_slugs' => ['handbook'],
                'body_html' => <<<'HTML'
<h2>Principles</h2>
<p>We optimize for accessibility, consistency, and speed of delivery. New UI should compose from shared components before introducing one-off styles.</p>
<h2>Sources of truth</h2>
<ul>
<li><strong>Tokens:</strong> color, type, spacing, elevation.</li>
<li><strong>Components:</strong> buttons, forms, navigation patterns.</li>
<li><strong>Documentation:</strong> usage, do/don’t, and code links.</li>
</ul>
<h2>Contributing</h2>
<p>Open a proposal for net-new patterns. Include rationale, accessibility notes, and engineering impact.</p>
HTML
            ],
            [
                'slug' => 'remote-first-meeting-etiquette',
                'category' => 'getting-started',
                'title' => 'Remote-first meeting etiquette',
                'excerpt' => 'Small habits that keep hybrid meetings fair and efficient.',
                'pinned' => false,
                'status' => 'published',
                'published_days_ago' => 10,
                'tag_slugs' => ['remote', 'onboarding'],
                'body_html' => <<<'HTML'
<h2>Defaults</h2>
<p>Assume at least one participant is remote. Use a single video link; avoid side conversations in the room that remote attendees cannot hear.</p>
<h2>Agenda and notes</h2>
<p>Share an agenda in the invite. Designate a note-taker; link notes in the calendar event afterward.</p>
<h2>Action items</h2>
<p>End with owners and dates. Prefer writing tasks in your work tracker over verbal-only commitments.</p>
HTML
            ],
            [
                'slug' => 'draft-example-release-checklist',
                'category' => 'engineering',
                'title' => 'Release checklist (draft)',
                'excerpt' => 'Work in progress—do not rely on this for production yet.',
                'pinned' => false,
                'status' => 'draft',
                'published_days_ago' => null,
                'tag_slugs' => ['engineering'],
                'body_html' => <<<'HTML'
<p><strong>Draft:</strong> This checklist is being reviewed by the platform team.</p>
<h2>Planned sections</h2>
<ul>
<li>Pre-deploy verification</li>
<li>Database migrations</li>
<li>Rollback plan</li>
<li>Customer communications</li>
</ul>
HTML
            ],
            [
                'slug' => 'benefits-and-perks-overview',
                'category' => 'hr-people',
                'title' => 'Benefits and perks overview',
                'excerpt' => 'Where to find health, retirement, and wellness programs.',
                'pinned' => false,
                'status' => 'published',
                'published_days_ago' => 18,
                'tag_slugs' => ['handbook'],
                'body_html' => <<<'HTML'
<h2>Enrollment windows</h2>
<p>Open enrollment runs once per year; life events (marriage, birth, loss of coverage) allow mid-year changes within 30 days.</p>
<h2>Core programs</h2>
<ul>
<li>Medical, dental, and vision through the carrier portal linked from your HR profile.</li>
<li>401(k) or equivalent retirement plan with employer match—see the summary plan description.</li>
<li>Wellness stipend for gym or mental health apps where offered.</li>
</ul>
<h2>Questions</h2>
<p>Contact People Ops for eligibility, dependents, and COBRA transitions.</p>
HTML
            ],
            [
                'slug' => 'data-classification-handling',
                'category' => 'security-compliance',
                'title' => 'Data classification and handling',
                'excerpt' => 'Public, internal, confidential, and restricted data—minimum handling rules.',
                'pinned' => false,
                'status' => 'published',
                'published_days_ago' => 25,
                'tag_slugs' => ['security', 'handbook'],
                'body_html' => <<<'HTML'
<h2>Labels</h2>
<ul>
<li><strong>Public</strong> — safe to share externally after comms review.</li>
<li><strong>Internal</strong> — default for day-to-day work; no public posting.</li>
<li><strong>Confidential</strong> — customer or financial data; need-to-know only.</li>
<li><strong>Restricted</strong> — credentials, keys, health data; encrypted storage and strict access lists.</li>
</ul>
<h2>Storage and sharing</h2>
<p>Keep confidential and restricted data in approved systems only. Do not copy customer exports to personal devices or unapproved cloud drives.</p>
<h2>Incidents</h2>
<p>Mis-send or suspected leak: report immediately to Security; speed matters more than blame.</p>
HTML
            ],
        ];
    }

    /**
     * @return array<string, KnowledgeCategory>
     */
    private function seedCategories(int $organizationId): array
    {
        $defs = [
            ['key' => 'getting-started', 'slug' => 'getting-started', 'name' => 'Getting started', 'description' => 'Onboarding, tools, and how we work together.', 'sort' => 0],
            ['key' => 'engineering', 'slug' => 'engineering', 'name' => 'Engineering', 'description' => 'Build, APIs, reviews, and technical standards.', 'sort' => 1],
            ['key' => 'hr-people', 'slug' => 'hr-people', 'name' => 'HR & people', 'description' => 'Policies, benefits, and people programs.', 'sort' => 2],
            ['key' => 'security-compliance', 'slug' => 'security-compliance', 'name' => 'Security & compliance', 'description' => 'Access, data handling, and regulatory expectations.', 'sort' => 3],
            ['key' => 'product-design', 'slug' => 'product-design', 'name' => 'Product & design', 'description' => 'Product process, research, and design system.', 'sort' => 4],
        ];

        $map = [];
        foreach ($defs as $d) {
            $map[$d['key']] = KnowledgeCategory::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'slug' => $d['slug'],
                ],
                [
                    'name' => $d['name'],
                    'description' => $d['description'],
                    'sort_order' => $d['sort'],
                ]
            );
        }

        return $map;
    }

    /**
     * @return array<string, int> slug => id
     */
    private function seedTags(int $organizationId): array
    {
        $names = [
            'onboarding' => 'Onboarding',
            'handbook' => 'Handbook',
            'security' => 'Security',
            'api' => 'API',
            'engineering' => 'Engineering',
            'remote' => 'Remote',
        ];

        $map = [];
        foreach ($names as $slug => $name) {
            $tag = KnowledgeTag::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'slug' => $slug,
                ],
                [
                    'name' => $name,
                ]
            );
            $map[$slug] = $tag->id;
        }

        return $map;
    }
}
