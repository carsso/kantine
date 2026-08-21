<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class TenantAccessTest extends TestCase
{
    use BuildsMenus;

    public function test_an_unknown_slug_returns_404(): void
    {
        $this->get('/cantine-inconnue')->assertNotFound();
    }

    public function test_an_inactive_tenant_is_hidden_from_anonymous_visitors(): void
    {
        $tenant = Tenant::factory()->inactive()->create();

        $this->get(route('tenant.home', ['tenantSlug' => $tenant->slug]))->assertNotFound();
    }

    public function test_an_inactive_tenant_is_hidden_from_a_user_without_the_permission(): void
    {
        $tenant = Tenant::factory()->inactive()->create();
        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();

        $this->actingAs(User::factory()->create())
            ->get(route('tenant.home', ['tenantSlug' => $tenant->slug]))
            ->assertNotFound();
    }

    public function test_a_tenant_admin_can_browse_their_inactive_tenant(): void
    {
        $tenant = Tenant::factory()->inactive()->create(['name' => 'Lille']);
        $this->createCategoryTree($tenant);
        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();

        $user = User::factory()->create();
        $user->assignRole('Tenant Admin '.$tenant->slug);

        $response = $this->actingAs($user)->get(route('tenant.home', ['tenantSlug' => $tenant->slug]));

        $response->assertOk();
        $response->assertSessionHas('flash_warning');
    }

    public function test_the_resolved_tenant_is_injected_into_the_route(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Plat du tenant');

        $response = $this->getJson(route('api.day', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']));

        $response->assertJsonPath('tenant.slug', $tenant->slug);
        $response->assertJsonPath('tenant.name', $tenant->name);
    }
}
