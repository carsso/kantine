<?php

namespace Tests\Feature;

use App\Models\DishCategory;
use App\Models\Tenant;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class CategoryLinkTest extends TestCase
{
    use BuildsMenus;

    private Tenant $tenant;

    private DishCategory $parent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        [$this->parent] = $this->createCategoryTree($this->tenant);
    }

    private function linkUrl(string $childSlug, string $type = 'mains', string $parentSlug = 'pole-chaud'): string
    {
        return route('menus.categories.link', [
            'tenantSlug' => $this->tenant->slug,
            'date' => '2026-03-02',
            'type' => $type,
            'parentSlug' => $parentSlug,
            'childSlug' => $childSlug,
        ]);
    }

    public function test_it_redirects_to_the_link_stored_on_the_category(): void
    {
        config(['ip.allow_list' => []]);
        DishCategory::factory()
            ->childOf($this->parent)
            ->withLink('https://example.test/carte')
            ->create(['name' => 'Le Grill', 'type' => 'mains']);

        $this->get($this->linkUrl('le-grill'))->assertRedirect('https://example.test/carte');
    }

    public function test_it_returns_404_when_the_category_has_no_link(): void
    {
        config(['ip.allow_list' => []]);

        $this->get($this->linkUrl('plats'))->assertNotFound();
    }

    public function test_it_returns_404_for_an_unknown_category(): void
    {
        config(['ip.allow_list' => []]);

        $this->get($this->linkUrl('categorie-inconnue'))->assertNotFound();
    }

    public function test_it_returns_404_when_the_parent_slug_does_not_match(): void
    {
        config(['ip.allow_list' => []]);
        DishCategory::factory()
            ->childOf($this->parent)
            ->withLink('https://example.test/carte')
            ->create(['name' => 'Le Grill', 'type' => 'mains']);

        $this->get($this->linkUrl('le-grill', 'mains', 'mauvais-parent'))->assertNotFound();
    }

    public function test_it_returns_404_for_a_category_of_another_tenant(): void
    {
        config(['ip.allow_list' => []]);
        $otherTenant = Tenant::factory()->create();
        [$otherParent] = $this->createCategoryTree($otherTenant);
        DishCategory::factory()
            ->childOf($otherParent)
            ->withLink('https://example.test/voisin')
            ->create(['name' => 'Le Grill', 'type' => 'mains']);

        $this->get($this->linkUrl('le-grill'))->assertNotFound();
    }

    public function test_it_is_forbidden_from_outside_the_allowed_networks(): void
    {
        config(['ip.allow_list' => ['10.0.0.0/8']]);
        DishCategory::factory()
            ->childOf($this->parent)
            ->withLink('https://example.test/carte')
            ->create(['name' => 'Le Grill', 'type' => 'mains']);

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
            ->get($this->linkUrl('le-grill'))
            ->assertForbidden();
    }

    public function test_it_is_allowed_from_inside_the_allowed_networks(): void
    {
        config(['ip.allow_list' => ['10.0.0.0/8']]);
        DishCategory::factory()
            ->childOf($this->parent)
            ->withLink('https://example.test/carte')
            ->create(['name' => 'Le Grill', 'type' => 'mains']);

        $this->withServerVariables(['REMOTE_ADDR' => '10.1.2.3'])
            ->get($this->linkUrl('le-grill'))
            ->assertRedirect('https://example.test/carte');
    }
}
