<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Tests\TestCase;

class AccountTokenTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The navbar calls hasPermissionTo('admin'), which throws when the
        // permission row is missing, so every authenticated page needs it.
        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();
    }

    public function test_the_account_page_lists_the_tokens_of_the_user(): void
    {
        $user = User::factory()->create();
        $user->createToken('mon-token');

        $response = $this->actingAs($user)->get(route('account'));

        $response->assertOk();
        $response->assertSee('mon-token');
    }

    public function test_an_unverified_user_is_redirected_to_the_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('account'))->assertRedirect(route('verification.notice'));
    }

    public function test_a_user_can_create_a_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('account.tokens.store'), ['name' => 'ci-token']);

        $response->assertRedirect();
        $response->assertSessionHas('new_token');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'ci-token',
        ]);
    }

    public function test_the_token_name_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('account.tokens.store'), [])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_a_user_can_revoke_their_own_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('ci-token')->accessToken;

        $this->actingAs($user)
            ->delete(route('account.tokens.destroy', ['token' => $token->id]))
            ->assertRedirect();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_a_user_cannot_revoke_the_token_of_someone_else(): void
    {
        $user = User::factory()->create();
        $victim = User::factory()->create();
        $token = $victim->createToken('token-de-la-victime')->accessToken;

        $this->actingAs($user)
            ->delete(route('account.tokens.destroy', ['token' => $token->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('personal_access_tokens', ['id' => $token->id]);
    }

    public function test_a_token_grants_access_to_the_api(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create();
        $plainTextToken = $user->createToken('ci-token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$plainTextToken)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }
}
