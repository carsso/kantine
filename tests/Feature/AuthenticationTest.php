<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();
    }

    public function test_the_login_page_renders(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_a_user_can_log_in(): void
    {
        $user = User::factory()->create(['email' => 'alice@example.test']);

        $response = $this->post(route('login'), [
            'email' => 'alice@example.test',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'alice@example.test']);

        $this->post(route('login'), [
            'email' => 'alice@example.test',
            'password' => 'mauvais-mot-de-passe',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_user_can_log_out(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect();

        $this->assertGuest();
    }

    public function test_registration_creates_a_user_and_sends_a_verification_email(): void
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'alice@example.test']);
        Notification::assertSentTo(User::where('email', 'alice@example.test')->first(), VerifyEmail::class);
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'alice@example.test']);

        $this->post(route('register'), [
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_requires_a_confirmed_password(): void
    {
        $this->post(route('register'), [
            'name' => 'Alice',
            'email' => 'alice@example.test',
            'password' => 'motdepasse',
            'password_confirmation' => 'autre-chose',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_a_registered_event_triggers_the_verification_notification(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        Event::dispatch(new Registered($user));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_the_password_reset_request_page_renders(): void
    {
        $this->get(route('password.request'))->assertOk();
    }
}
