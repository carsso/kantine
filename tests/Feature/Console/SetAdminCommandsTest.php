<?php

namespace Tests\Feature\Console;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class SetAdminCommandsTest extends TestCase
{
    public function test_it_grants_the_super_admin_role(): void
    {
        Tenant::factory()->create(['name' => 'Lille']);
        $user = User::factory()->create(['email' => 'admin@example.test']);

        $this->artisan('kantine:set-super-admin', ['email' => 'admin@example.test'])
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasRole('Super Admin'));
        $this->assertTrue($user->fresh()->hasPermissionTo('admin'));
    }

    public function test_it_fails_when_the_user_does_not_exist(): void
    {
        $this->artisan('kantine:set-super-admin', ['email' => 'inconnu@example.test'])
            ->expectsOutputToContain('User not found')
            ->assertExitCode(1);
    }

    public function test_it_grants_the_tenant_admin_role(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Lille']);
        $user = User::factory()->create(['email' => 'lille@example.test']);

        $this->artisan('kantine:set-tenant-admin', [
            'tenant_slug' => $tenant->slug,
            'email' => 'lille@example.test',
        ])->assertExitCode(0);

        $user = $user->fresh();
        $this->assertTrue($user->hasRole('Tenant Admin '.$tenant->slug));
        $this->assertTrue($user->hasPermissionTo('tenant-admin-'.$tenant->slug));
    }

    public function test_the_tenant_admin_command_fails_for_an_unknown_tenant(): void
    {
        User::factory()->create(['email' => 'lille@example.test']);

        $this->artisan('kantine:set-tenant-admin', [
            'tenant_slug' => 'cantine-inconnue',
            'email' => 'lille@example.test',
        ])->expectsOutputToContain('Tenant not found')
            ->assertExitCode(1);
    }

    public function test_the_tenant_admin_command_fails_for_an_unknown_user(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Lille']);

        $this->artisan('kantine:set-tenant-admin', [
            'tenant_slug' => $tenant->slug,
            'email' => 'inconnu@example.test',
        ])->expectsOutputToContain('User not found')
            ->assertExitCode(1);
    }
}
