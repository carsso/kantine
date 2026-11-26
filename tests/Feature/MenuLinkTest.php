<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Tests\TestCase;

class MenuLinkTest extends TestCase
{
    public function test_it_redirects_to_the_link_stored_on_the_tenant(): void
    {
        config(['ip.allow_list' => []]);
        $tenant = Tenant::factory()->withLink('https://example.test/sandwich')->create();

        $this->get(route('menus.link', ['tenantSlug' => $tenant->slug]))
            ->assertRedirect('https://example.test/sandwich');
    }

    public function test_it_returns_404_when_the_tenant_has_no_link(): void
    {
        config(['ip.allow_list' => []]);
        $tenant = Tenant::factory()->create();

        $this->get(route('menus.link', ['tenantSlug' => $tenant->slug]))->assertNotFound();
    }

    public function test_it_is_forbidden_from_outside_the_allowed_networks(): void
    {
        config(['ip.allow_list' => ['10.0.0.0/8']]);
        $tenant = Tenant::factory()->withLink('https://example.test/sandwich')->create();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->get(route('menus.link', ['tenantSlug' => $tenant->slug]))
            ->assertForbidden();
    }

    public function test_it_is_allowed_from_inside_the_allowed_networks(): void
    {
        config(['ip.allow_list' => ['10.0.0.0/8']]);
        $tenant = Tenant::factory()->withLink('https://example.test/sandwich')->create();

        $this->withServerVariables(['REMOTE_ADDR' => '10.1.2.3'])
            ->get(route('menus.link', ['tenantSlug' => $tenant->slug]))
            ->assertRedirect('https://example.test/sandwich');
    }
}
