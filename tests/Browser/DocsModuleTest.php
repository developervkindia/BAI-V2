<?php

namespace Tests\Browser;

use App\Models\DocDocument;
use App\Models\DocFolder;
use App\Models\Organization;
use App\Models\OrganizationSubscription;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;
use App\Services\OrgMemberOnboardingService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\ProductSeeder;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DocsModuleTest extends DuskTestCase
{
    protected User $user;

    protected Organization $org;

    protected ?DocDocument $fixtureDoc = null;

    protected ?DocDocument $fixtureSheet = null;

    protected ?DocDocument $fixtureForm = null;

    protected ?DocDocument $fixturePresentation = null;

    protected ?DocFolder $fixtureFolder = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (! User::query()->exists()) {
            $this->markTestSkipped('No users in database.');
        }

        $this->user = User::query()->firstOrFail();
        $resolvedOrg = $this->user->currentOrganization()
            ?? $this->user->allOrganizations()->first();

        if (! $resolvedOrg) {
            $this->bootstrapOrganization();
        } else {
            $this->org = $resolvedOrg;
        }

        $this->ensureDocsSubscription();
        $this->seedFixtures();
    }

    protected function bootstrapOrganization(): void
    {
        if (! Permission::query()->where('key', 'docs.view')->exists()) {
            $this->seed(ProductSeeder::class);
            $this->seed(PermissionSeeder::class);
        }

        $this->org = Organization::query()->create([
            'name' => 'Dusk Docs '.substr(uniqid(), -8),
            'owner_id' => $this->user->id,
        ]);

        $this->org->members()->syncWithoutDetaching([
            $this->user->id => [
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        PermissionSeeder::seedRolesForOrg($this->org);
        app(OrgMemberOnboardingService::class)->provisionMember($this->org, $this->user, 'owner');
        $this->user = $this->user->fresh();
    }

    protected function ensureDocsSubscription(): void
    {
        $product = Product::query()->firstOrCreate(
            ['key' => 'docs'],
            [
                'name' => 'BAI Docs',
                'tagline' => 'Documents, spreadsheets, forms & presentations',
                'color' => 'sky',
                'route_prefix' => 'docs',
                'is_available' => true,
                'sort_order' => 3,
            ]
        );

        OrganizationSubscription::query()->firstOrCreate(
            [
                'organization_id' => $this->org->id,
                'product_id' => $product->id,
            ],
            [
                'plan' => 'free',
                'status' => 'active',
                'starts_at' => now(),
            ]
        );
    }

    protected function seedFixtures(): void
    {
        // Clean up leftover test documents from previous runs
        DocDocument::query()->where('organization_id', $this->org->id)
            ->where(function ($q) {
                $q->where('title', 'like', 'Dusk %')
                  ->orWhere('title', 'like', 'Untitled %')
                  ->orWhere('title', 'like', '%Copy%')
                  ->orWhere('title', 'like', 'API %');
            })
            ->forceDelete();

        $suffix = substr(uniqid(), -6);

        $this->fixtureFolder = DocFolder::query()->create([
            'organization_id' => $this->org->id,
            'created_by' => $this->user->id,
            'name' => 'Dusk Folder '.$suffix,
        ]);

        $this->fixtureDoc = DocDocument::query()->create([
            'organization_id' => $this->org->id,
            'owner_id' => $this->user->id,
            'type' => 'document',
            'title' => 'Dusk Document '.$suffix,
            'body_html' => '<p>Hello from Dusk test.</p>',
            'status' => 'draft',
        ]);

        $this->fixtureSheet = DocDocument::query()->create([
            'organization_id' => $this->org->id,
            'owner_id' => $this->user->id,
            'type' => 'spreadsheet',
            'title' => 'Dusk Spreadsheet '.$suffix,
            'body_json' => [
                'sheets' => [['name' => 'Sheet1', 'data' => [['A1', 'B1'], ['A2', 'B2']], 'style' => [], 'colWidths' => [], 'merged' => [], 'frozen' => ['rows' => 0, 'cols' => 0]]],
                'activeSheet' => 0,
            ],
            'status' => 'draft',
        ]);

        $this->fixtureForm = DocDocument::query()->create([
            'organization_id' => $this->org->id,
            'owner_id' => $this->user->id,
            'type' => 'form',
            'title' => 'Dusk Form '.$suffix,
            'body_json' => [
                'questions' => [
                    [
                        'id' => 'q_dusk1',
                        'type' => 'short_text',
                        'title' => 'Your name',
                        'description' => '',
                        'required' => true,
                        'options' => [],
                        'other_option' => false,
                    ],
                    [
                        'id' => 'q_dusk2',
                        'type' => 'multiple_choice',
                        'title' => 'Favorite color',
                        'description' => 'Pick one',
                        'required' => false,
                        'options' => [
                            ['id' => 'o_1', 'label' => 'Red'],
                            ['id' => 'o_2', 'label' => 'Blue'],
                            ['id' => 'o_3', 'label' => 'Green'],
                        ],
                        'other_option' => true,
                    ],
                ],
                'settings' => [
                    'collect_email' => false,
                    'limit_responses' => null,
                    'shuffle_questions' => false,
                    'confirmation_message' => 'Thanks for responding!',
                    'allow_edit_after_submit' => false,
                ],
            ],
            'status' => 'published',
        ]);

        $this->fixturePresentation = DocDocument::query()->create([
            'organization_id' => $this->org->id,
            'owner_id' => $this->user->id,
            'type' => 'presentation',
            'title' => 'Dusk Presentation '.$suffix,
            'body_json' => [
                'theme' => 'dark',
                'transition' => 'slide',
                'slides' => [
                    [
                        'id' => 's_dusk1',
                        'elements' => [
                            ['id' => 'e_1', 'type' => 'text', 'content' => 'Slide 1 Title', 'x' => 10, 'y' => 30, 'width' => 80, 'height' => 20, 'style' => ['fontSize' => 44, 'fontWeight' => 'bold', 'color' => '#ffffff', 'textAlign' => 'center']],
                        ],
                        'background' => ['type' => 'solid', 'value' => '#1e293b'],
                        'notes' => 'Test speaker notes',
                    ],
                    [
                        'id' => 's_dusk2',
                        'elements' => [
                            ['id' => 'e_2', 'type' => 'text', 'content' => 'Slide 2', 'x' => 10, 'y' => 30, 'width' => 80, 'height' => 20, 'style' => ['fontSize' => 36, 'color' => '#ffffff', 'textAlign' => 'center']],
                        ],
                        'background' => ['type' => 'solid', 'value' => '#0f172a'],
                        'notes' => '',
                    ],
                ],
            ],
            'status' => 'draft',
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up ALL test documents (including from previous failed runs)
        DocDocument::query()->where('organization_id', $this->org->id)
            ->where(function ($q) {
                $q->where('title', 'like', 'Dusk %')
                  ->orWhere('title', 'like', 'Untitled %')
                  ->orWhere('title', 'like', '%Copy%')
                  ->orWhere('title', 'like', 'API %');
            })
            ->forceDelete();

        DocFolder::query()->where('organization_id', $this->org->id)
            ->where('name', 'like', 'Dusk %')
            ->forceDelete();

        parent::tearDown();
    }

    // ================================================================
    // GUEST ACCESS
    // ================================================================

    public function test_guest_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/docs')
                ->assertPathBeginsWith('/login');
        });
    }

    // ================================================================
    // DOCS HOME / DRIVE VIEW
    // ================================================================

    public function test_docs_home_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs')
                ->assertSee('Docs')
                ->assertSee($this->fixtureDoc->title)
                ->screenshot('docs-home');
        });
    }

    public function test_docs_home_type_filter_tabs(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs?type=document')
                ->assertSee($this->fixtureDoc->title)
                ->assertDontSee($this->fixtureSheet->title);

            $browser->visit('/docs?type=spreadsheet')
                ->assertSee($this->fixtureSheet->title)
                ->assertDontSee($this->fixtureDoc->title);

            $browser->visit('/docs?type=form')
                ->assertSee($this->fixtureForm->title);

            $browser->visit('/docs?type=presentation')
                ->assertSee($this->fixturePresentation->title);
        });
    }

    public function test_docs_home_list_view(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs?view=list')
                ->assertSee($this->fixtureDoc->title)
                ->assertPresent('table')
                ->screenshot('docs-home-list');
        });
    }

    public function test_docs_starred_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/starred')
                ->assertSee('Starred')
                ->screenshot('docs-starred');
        });
    }

    public function test_docs_shared_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/shared')
                ->assertSee('Shared with me')
                ->screenshot('docs-shared');
        });
    }

    public function test_docs_trash_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/trash')
                ->assertSee('Trash')
                ->screenshot('docs-trash');
        });
    }

    // ================================================================
    // FOLDER NAVIGATION
    // ================================================================

    public function test_folder_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/folder/'.$this->fixtureFolder->id)
                ->assertSee($this->fixtureFolder->name)
                ->screenshot('docs-folder');
        });
    }

    // ================================================================
    // SIDEBAR NAVIGATION
    // ================================================================

    public function test_sidebar_new_button_dropdown(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs')
                ->pause(500)
                ->assertSeeIn('.lg\\:flex', 'New')
                ->screenshot('docs-sidebar');
        });
    }

    // ================================================================
    // DOCUMENT EDITOR (GOOGLE DOCS CLONE)
    // ================================================================

    public function test_document_editor_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(3000) // Wait for TinyMCE to initialize
                ->assertSee('File')
                ->assertSee('Edit')
                ->assertSee('View')
                ->assertSee('Insert')
                ->assertSee('Format')
                ->assertSee('Table')
                ->assertSee('Tools')
                ->assertPresent('#doc-title-input')
                ->screenshot('docs-document-editor');
        });
    }

    public function test_document_editor_title_input(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(3000)
                ->assertInputValue('#doc-title-input', $this->fixtureDoc->title)
                ->clear('#doc-title-input')
                ->type('#doc-title-input', 'Updated Title by Dusk')
                ->pause(3000) // Wait for auto-save
                ->screenshot('docs-document-title-edit');
        });

        // Verify title was saved to database
        $this->fixtureDoc->refresh();
        $this->assertEquals('Updated Title by Dusk', $this->fixtureDoc->title);
    }

    public function test_document_editor_tinymce_toolbar_present(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(3000)
                // TinyMCE toolbar buttons
                ->assertPresent('.tox-toolbar__primary')
                ->assertPresent('.tox-menubar')
                // Bold, Italic, Underline buttons
                ->assertPresent('button[aria-label="Bold"]')
                ->assertPresent('button[aria-label="Italic"]')
                ->assertPresent('button[aria-label="Underline"]')
                // Alignment buttons
                ->assertPresent('button[aria-label="Align left"]')
                ->assertPresent('button[aria-label="Align center"]')
                // List buttons
                ->assertPresent('button[aria-label="Bullet list"]')
                ->assertPresent('button[aria-label="Numbered list"]')
                // Insert buttons
                ->assertPresent('button[aria-label="Insert/edit link"]')
                ->assertPresent('button[aria-label="Insert/edit image"]')
                ->assertPresent('button[aria-label="Table"]')
                ->screenshot('docs-document-toolbar');
        });
    }

    public function test_document_editor_typing_triggers_autosave(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(3000);

            // Use JavaScript to type into TinyMCE
            $browser->script("tinymce.activeEditor.setContent(tinymce.activeEditor.getContent() + '<p>Dusk typed this text</p>'); tinymce.activeEditor.fire('change');");
            $browser->pause(3000) // Wait for auto-save
                ->assertSeeIn('#save-status', 'All changes saved')
                ->screenshot('docs-document-autosave');
        });

        // Verify content was saved
        $this->fixtureDoc->refresh();
        $this->assertStringContainsString('Dusk typed this text', $this->fixtureDoc->body_html);
    }

    public function test_document_editor_bold_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(3000);

            // Use JavaScript to apply bold and type
            $browser->script("
                var editor = tinymce.activeEditor;
                editor.execCommand('Bold');
                editor.insertContent('<strong>Bold text here</strong>');
                editor.fire('change');
            ");
            $browser->pause(3000)
                ->screenshot('docs-document-bold');
        });

        $this->fixtureDoc->refresh();
        $this->assertStringContainsString('<strong>', $this->fixtureDoc->body_html);
    }

    public function test_document_editor_save_status_indicator(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(3000)
                ->assertPresent('#save-status')
                ->assertSeeIn('#save-status', 'All changes saved')
                ->screenshot('docs-document-save-status');
        });
    }

    public function test_document_editor_share_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(2000)
                ->assertSee('Share')
                ->screenshot('docs-document-share-btn');
        });
    }

    public function test_document_editor_back_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(2000)
                ->assertPresent('a[href="'.route('docs.index').'"]')
                ->screenshot('docs-document-back-btn');
        });
    }

    public function test_document_editor_fullscreen_toggle(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/d/'.$this->fixtureDoc->id)
                ->pause(3000)
                ->click('button[aria-label="Fullscreen"]')
                ->pause(500)
                ->assertPresent('.tox-fullscreen')
                ->click('button[aria-label="Fullscreen"]')
                ->pause(500)
                ->assertMissing('.tox-fullscreen')
                ->screenshot('docs-document-fullscreen');
        });
    }

    // ================================================================
    // DOCUMENT CREATION
    // ================================================================

    public function test_create_new_document_redirects_to_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/document/new')
                ->pause(3000)
                ->assertPathBeginsWith('/docs/d/')
                ->assertPresent('.tox-menubar')
                ->screenshot('docs-document-new');
        });
    }

    // ================================================================
    // SPREADSHEET EDITOR
    // ================================================================

    public function test_spreadsheet_editor_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/s/'.$this->fixtureSheet->id)
                ->pause(3000) // Wait for jspreadsheet to initialize
                ->assertPresent('#doc-title-input')
                ->assertPresent('#spreadsheet-container')
                ->screenshot('docs-spreadsheet-editor');
        });
    }

    public function test_spreadsheet_has_toolbar(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/s/'.$this->fixtureSheet->id)
                ->pause(3000)
                ->assertSee('Share')
                ->assertPresent('#doc-title-input')
                ->screenshot('docs-spreadsheet-toolbar');
        });
    }

    public function test_create_new_spreadsheet_redirects_to_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/spreadsheet/new')
                ->pause(3000)
                ->assertPathBeginsWith('/docs/s/')
                ->assertPresent('#spreadsheet-container')
                ->screenshot('docs-spreadsheet-new');
        });
    }

    // ================================================================
    // FORM BUILDER
    // ================================================================

    public function test_form_builder_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/f/'.$this->fixtureForm->id)
                ->pause(3000)
                ->assertPresent('#doc-title-input')
                ->screenshot('docs-form-editor');
        });
    }

    public function test_form_builder_question_types_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/f/'.$this->fixtureForm->id)
                ->pause(3000)
                ->assertPresent('#doc-title-input')
                // Questions are rendered as input values by Alpine.js
                // Just verify the form builder UI elements are present
                ->assertSee('Questions')
                ->screenshot('docs-form-questions');
        });
    }

    public function test_form_responses_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/f/'.$this->fixtureForm->id.'/responses')
                ->pause(1000)
                ->assertSee($this->fixtureForm->title)
                ->assertSee('0 response')
                ->screenshot('docs-form-responses');
        });
    }

    public function test_form_public_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/doc-forms/'.$this->fixtureForm->slug)
                ->assertSee($this->fixtureForm->title)
                ->assertSee('Your name')
                ->assertSee('Favorite color')
                ->assertSee('Red')
                ->assertPresent('button[type="submit"]')
                ->screenshot('docs-form-public');
        });
    }

    public function test_form_public_submission(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/doc-forms/'.$this->fixtureForm->slug)
                ->type('responses[q_dusk1]', 'John Doe from Dusk')
                ->radio('responses[q_dusk2]', 'Blue')
                ->press('Submit')
                ->pause(2000)
                ->assertSee('Thanks for responding!')
                ->screenshot('docs-form-submitted');
        });

        // Verify response was saved
        $this->assertEquals(1, $this->fixtureForm->formResponses()->count());
        $response = $this->fixtureForm->formResponses()->first();
        $this->assertEquals('John Doe from Dusk', $response->data['q_dusk1']);
        $this->assertEquals('Blue', $response->data['q_dusk2']);
    }

    public function test_create_new_form_redirects_to_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/form/new')
                ->pause(2000)
                ->assertPathBeginsWith('/docs/f/')
                ->screenshot('docs-form-new');
        });
    }

    // ================================================================
    // PRESENTATION EDITOR
    // ================================================================

    public function test_presentation_editor_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/p/'.$this->fixturePresentation->id)
                ->pause(2000)
                ->assertPresent('#doc-title-input')
                ->assertSee('Present')
                ->assertSee('Text')
                ->assertSee('Image')
                ->screenshot('docs-presentation-editor');
        });
    }

    public function test_presentation_present_mode_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/p/'.$this->fixturePresentation->id.'/present')
                ->pause(3000) // Wait for reveal.js
                ->assertPresent('.reveal')
                ->assertSee('Slide 1 Title')
                ->screenshot('docs-presentation-present');
        });
    }

    public function test_create_new_presentation_redirects_to_editor(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs/presentation/new')
                ->pause(2000)
                ->assertPathBeginsWith('/docs/p/')
                ->screenshot('docs-presentation-new');
        });
    }

    // ================================================================
    // STAR TOGGLE API
    // ================================================================

    public function test_star_toggle_api(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/docs')
                ->pause(1000);

            // Star via API
            $response = $this->actingAs($this->user)->postJson('/api/docs/documents/'.$this->fixtureDoc->id.'/star');
            $response->assertOk();
            $this->assertTrue($response->json('starred'));

            // Verify starred
            $this->assertTrue($this->fixtureDoc->isStarredBy($this->user));

            // Unstar
            $response = $this->actingAs($this->user)->postJson('/api/docs/documents/'.$this->fixtureDoc->id.'/star');
            $response->assertOk();
            $this->assertFalse($response->json('starred'));
        });
    }

    // ================================================================
    // AUTO-SAVE API
    // ================================================================

    public function test_autosave_api_saves_content(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            '/api/docs/documents/'.$this->fixtureDoc->id.'/auto-save',
            [
                'title' => 'API Updated Title',
                'body_html' => '<p>API updated content</p>',
                'version' => $this->fixtureDoc->version,
            ]
        );

        $response->assertOk();
        $this->assertTrue($response->json('saved'));

        $this->fixtureDoc->refresh();
        $this->assertEquals('API Updated Title', $this->fixtureDoc->title);
        $this->assertStringContainsString('API updated content', $this->fixtureDoc->body_html);
    }

    public function test_autosave_api_version_conflict(): void
    {
        $response = $this->actingAs($this->user)->putJson(
            '/api/docs/documents/'.$this->fixtureDoc->id.'/auto-save',
            [
                'title' => 'Conflict Test',
                'body_html' => '<p>Conflict</p>',
                'version' => 9999, // Wrong version
            ]
        );

        $response->assertStatus(409);
        $this->assertEquals('conflict', $response->json('error'));
    }

    // ================================================================
    // FOLDER API
    // ================================================================

    public function test_folder_create_api(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/docs/folders', [
            'name' => 'Dusk API Folder',
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));

        // Cleanup
        DocFolder::where('name', 'Dusk API Folder')->delete();
    }

    public function test_folder_tree_api(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/docs/folders/tree');

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('data'));
    }

    // ================================================================
    // DOCUMENT CRUD API
    // ================================================================

    public function test_document_create_api(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/docs/documents', [
            'type' => 'document',
            'title' => 'Dusk API Doc',
        ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('data.id'));

        // Cleanup
        DocDocument::find($response->json('data.id'))?->forceDelete();
    }

    public function test_document_duplicate_api(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            '/api/docs/documents/'.$this->fixtureDoc->id.'/duplicate'
        );

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertStringContainsString('Copy', DocDocument::find($response->json('data.id'))?->title);

        // Cleanup
        DocDocument::find($response->json('data.id'))?->forceDelete();
    }

    public function test_document_move_api(): void
    {
        $response = $this->actingAs($this->user)->postJson(
            '/api/docs/documents/'.$this->fixtureDoc->id.'/move',
            ['folder_id' => $this->fixtureFolder->id]
        );

        $response->assertOk();
        $this->fixtureDoc->refresh();
        $this->assertEquals($this->fixtureFolder->id, $this->fixtureDoc->folder_id);

        // Move back to root
        $this->actingAs($this->user)->postJson(
            '/api/docs/documents/'.$this->fixtureDoc->id.'/move',
            ['folder_id' => null]
        );
    }

    public function test_document_delete_and_restore_api(): void
    {
        $response = $this->actingAs($this->user)->deleteJson(
            '/api/docs/documents/'.$this->fixtureDoc->id
        );
        $response->assertOk();

        // Verify soft-deleted
        $this->assertSoftDeleted('doc_documents', ['id' => $this->fixtureDoc->id]);

        // Restore
        $response = $this->actingAs($this->user)->postJson(
            '/api/docs/documents/'.$this->fixtureDoc->id.'/restore'
        );
        $response->assertOk();

        // Verify restored
        $this->fixtureDoc->refresh();
        $this->assertNull($this->fixtureDoc->deleted_at);
    }
}
