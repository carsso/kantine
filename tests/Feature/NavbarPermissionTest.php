<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NavbarPermissionTest extends TestCase
{
    /**
     * The navbar routes are handed to a Vue component through @json, which
     * escapes the emoji of the label, so the raw HTML never contains it.
     */
    private function adminLinkNeedle(): string
    {
        return trim(json_encode('🔐 Administration'), '"');
    }

    public function test_an_authenticated_page_renders_on_a_fresh_install(): void
    {
        // No roles or permissions have been created yet: this is the state of
        // the app before kantine:set-super-admin has ever been run.
        $this->assertSame(0, Permission::count());

        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertOk();
    }

    public function test_the_navbar_hides_the_admin_link_from_a_regular_user(): void
    {
        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();

        $this->actingAs(User::factory()->create())
            ->get(route('account'))
            ->assertOk()
            ->assertDontSee($this->adminLinkNeedle(), false);
    }

    public function test_the_navbar_shows_the_admin_link_to_an_admin(): void
    {
        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user)
            ->get(route('account'))
            ->assertOk()
            ->assertSee($this->adminLinkNeedle(), false);
    }
}
