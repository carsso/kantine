<?php

namespace Tests\Feature;

use App\Models\DishCategory;
use App\Models\Information;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use BuildsMenus;

    public function test_a_tenant_slug_is_generated_from_its_name(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Villeneuve d\'Ascq']);

        $this->assertSame('villeneuve-dascq', $tenant->slug);
    }

    public function test_two_tenants_cannot_share_a_slug(): void
    {
        Tenant::factory()->create(['name' => 'Lille']);
        $second = Tenant::factory()->create(['name' => 'Lille']);

        $this->assertNotSame('lille', $second->slug);
    }

    public function test_a_tenant_hides_its_secrets_from_serialization(): void
    {
        $tenant = Tenant::factory()->withWebex()->create();

        $array = $tenant->toArray();

        $this->assertArrayNotHasKey('webex_bearer_token', $array);
        $this->assertArrayNotHasKey('meta', $array);
        $this->assertArrayNotHasKey('id', $array);
        $this->assertArrayHasKey('slug', $array);
    }

    public function test_the_tenant_meta_is_cast_to_an_array(): void
    {
        $tenant = Tenant::factory()->create(['meta' => ['api_type' => 'api-restauration']]);

        $this->assertSame('api-restauration', $tenant->fresh()->meta['api_type']);
    }

    public function test_a_tenant_exposes_its_dishes_categories_and_information(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti');
        Information::factory()->create(['tenant_id' => $tenant->id, 'date' => '2026-03-02']);

        $this->assertCount(1, $tenant->dishes);
        $this->assertCount(2, $tenant->categories);
        $this->assertCount(1, $tenant->information);
    }

    public function test_a_category_slug_is_generated_and_may_be_duplicated_across_tenants(): void
    {
        $lille = Tenant::factory()->create(['name' => 'Lille']);
        $lens = Tenant::factory()->create(['name' => 'Lens']);

        $first = DishCategory::factory()->create(['tenant_id' => $lille->id, 'name' => 'Pôle Chaud']);
        $second = DishCategory::factory()->create(['tenant_id' => $lens->id, 'name' => 'Pôle Chaud']);

        $this->assertSame('pole-chaud', $first->name_slug);
        $this->assertSame('pole-chaud', $second->name_slug);
    }

    public function test_a_category_links_to_its_parent_and_children(): void
    {
        $tenant = Tenant::factory()->create();
        [$parent, $child] = $this->createCategoryTree($tenant);

        $this->assertTrue($child->parent->is($parent));
        $this->assertTrue($parent->children->first()->is($child));
        $this->assertNull($parent->parent);
    }

    public function test_the_dish_tags_survive_a_round_trip(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);

        $dish = $this->createDish($child, '2026-03-02', 'Poulet rôti', ['halal', 'france']);

        $this->assertSame(['halal', 'france'], $dish->fresh()->tags);
    }

    public function test_a_dish_exposes_its_date_as_a_carbon_instance(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);

        $dish = $this->createDish($child, '2026-03-02', 'Poulet rôti');

        $this->assertInstanceOf(Carbon::class, $dish->date_carbon);
        $this->assertSame('2026-03-02', $dish->date_carbon->format('Y-m-d'));
    }

    public function test_a_dish_hides_its_internal_keys(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);

        $array = $this->createDish($child, '2026-03-02', 'Poulet rôti')->toArray();

        $this->assertSame(['name', 'tags', 'date'], array_values(array_intersect(
            ['name', 'tags', 'date'],
            array_keys($array)
        )));
        $this->assertArrayNotHasKey('tenant_id', $array);
        $this->assertArrayNotHasKey('dishes_category_id', $array);
    }

    public function test_a_dish_links_back_to_its_category_and_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $dish = $this->createDish($child, '2026-03-02', 'Poulet rôti');

        $this->assertTrue($dish->category->is($child));
        $this->assertTrue($dish->tenant->is($tenant));
    }

    public function test_the_information_html_escapes_and_keeps_the_line_breaks(): void
    {
        $information = Information::factory()->create([
            'information' => "Ligne 1\n<script>alert(1)</script>",
        ]);

        $html = $information->information_html;

        $this->assertStringContainsString('<br>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_a_user_password_is_hashed_on_write(): void
    {
        $user = User::factory()->create(['password' => 'motdepasse']);

        $this->assertNotSame('motdepasse', $user->password);
        $this->assertTrue(password_verify('motdepasse', $user->password));
    }

    public function test_a_user_exposes_a_gravatar_url_and_hides_its_password(): void
    {
        $user = User::factory()->create(['email' => 'Alice@Example.test ']);

        $array = $user->toArray();

        $this->assertSame(
            'https://www.gravatar.com/avatar/'.md5('alice@example.test').'?d=identicon',
            $array['gravatar_url']
        );
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }
}
