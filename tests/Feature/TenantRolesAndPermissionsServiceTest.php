<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantRolesAndPermissionsServiceTest extends TestCase
{
    private TenantRolesAndPermissionsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TenantRolesAndPermissionsService::class);
    }

    public function test_it_creates_the_super_admin_role_and_the_admin_permission(): void
    {
        $this->service->createTenantRolesAndPermissions();

        $this->assertTrue(Role::where('name', 'Super Admin')->exists());
        $this->assertTrue(Permission::where('name', 'admin')->exists());
    }

    public function test_it_creates_a_role_and_a_permission_per_tenant(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Lille']);

        $this->service->createTenantRolesAndPermissions();

        $this->assertTrue(Role::where('name', 'Tenant Admin '.$tenant->slug)->exists());
        $this->assertTrue(Permission::where('name', 'tenant-admin-'.$tenant->slug)->exists());
    }

    public function test_a_tenant_admin_gets_both_the_tenant_and_the_admin_permission(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Lille']);
        $this->service->createTenantRolesAndPermissions();

        $user = User::factory()->create();
        $user->assignRole('Tenant Admin '.$tenant->slug);

        $this->assertTrue($user->hasPermissionTo('tenant-admin-'.$tenant->slug));
        $this->assertTrue($user->hasPermissionTo('admin'));
    }

    public function test_a_tenant_admin_has_no_rights_on_another_tenant(): void
    {
        $lille = Tenant::factory()->create(['name' => 'Lille']);
        $lens = Tenant::factory()->create(['name' => 'Lens']);
        $this->service->createTenantRolesAndPermissions();

        $user = User::factory()->create();
        $user->assignRole('Tenant Admin '.$lille->slug);

        $this->assertFalse($user->hasPermissionTo('tenant-admin-'.$lens->slug));
    }

    public function test_the_super_admin_gets_every_tenant_permission(): void
    {
        $lille = Tenant::factory()->create(['name' => 'Lille']);
        $lens = Tenant::factory()->create(['name' => 'Lens']);
        $this->service->createTenantRolesAndPermissions();

        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->assertTrue($user->hasPermissionTo('tenant-admin-'.$lille->slug));
        $this->assertTrue($user->hasPermissionTo('tenant-admin-'.$lens->slug));
        $this->assertTrue($user->hasPermissionTo('admin'));
    }

    public function test_running_it_twice_does_not_duplicate_anything(): void
    {
        Tenant::factory()->create(['name' => 'Lille']);

        $this->service->createTenantRolesAndPermissions();
        $rolesAfterFirstRun = Role::count();
        $permissionsAfterFirstRun = Permission::count();

        $this->service->createTenantRolesAndPermissions();

        $this->assertSame($rolesAfterFirstRun, Role::count());
        $this->assertSame($permissionsAfterFirstRun, Permission::count());
    }

    public function test_a_tenant_created_later_gets_its_role_on_the_next_run(): void
    {
        $this->service->createTenantRolesAndPermissions();
        $newTenant = Tenant::factory()->create(['name' => 'Lens']);

        $this->assertFalse(Role::where('name', 'Tenant Admin '.$newTenant->slug)->exists());

        $this->service->createTenantRolesAndPermissions();

        $this->assertTrue(Role::where('name', 'Tenant Admin '.$newTenant->slug)->exists());
    }
}
