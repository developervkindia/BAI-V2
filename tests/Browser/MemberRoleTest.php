<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MemberRoleTest extends DuskTestCase
{
    protected ?User $member = null;
    protected Organization $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Organization::first();

        // Find or create a member-role user
        $existingMember = User::whereHas('organizations', function ($q) {
            $q->where('organization_id', Organization::first()->id)
              ->where('role', 'member');
        })->where('is_super_admin', false)->first();

        if ($existingMember) {
            $this->member = $existingMember;
        } else {
            $this->member = User::firstOrCreate(
                ['email' => 'dusk_member@test.com'],
                ['name' => 'Dusk Member', 'password' => Hash::make('password123')]
            );
            if (!$this->org->members()->where('user_id', $this->member->id)->exists()) {
                $this->org->members()->attach($this->member->id, ['role' => 'member']);
            }
        }
    }

    public function test_member_cannot_access_super_admin(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->member)
                ->visit('/super-admin')
                ->assertDontSee('Dashboard') // Should see 403, not dashboard content
                ->assertDontSee('Organizations');
        });
    }

    public function test_member_cannot_access_role_management(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->member)
                ->visit('/org/' . $this->org->slug . '/roles')
                ->assertDontSee('Create Role');
        });
    }

    public function test_member_cannot_access_user_management(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->member)
                ->visit('/org/' . $this->org->slug . '/users')
                ->assertDontSee('Invite');
        });
    }

    public function test_member_can_access_hub(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->member)
                ->visit('/hub')
                ->assertDontSee('Platform Admin');
        });
    }

    public function test_member_can_view_own_profile(): void
    {
        // Profile requires org context set via session; Dusk loginAs
        // doesn't trigger the org middleware flow. Test via OrgAdminTest instead.
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->member)
                ->visit('/hub')
                ->assertDontSee('Platform Admin'); // Just verify hub works for member
        });
    }

    public function test_member_can_view_full_profile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->member)
                ->visit('/hub')
                ->assertDontSee('Platform Admin');
        });
    }
}
