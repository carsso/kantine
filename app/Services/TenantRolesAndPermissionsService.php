<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Tenant;

class TenantRolesAndPermissionsService
{
    public function createTenantRolesAndPermissions()
    {
        if (!Role::where('name', 'Super Admin')->exists()) {
            Role::create(['name' => 'Super Admin']);
        }
        // create permission for super admin
        if (!Permission::where('name', 'admin')->exists()) {
            Permission::create(['name' => 'admin']);
        }

        $tenants = Tenant::all();
        foreach($tenants as $tenant) {
            // create tenant admin role if not exists
            if (!Role::where('name', 'Tenant Admin ' . $tenant->slug)->exists()) {
                Role::create(['name' => 'Tenant Admin ' . $tenant->slug]);
            }

            // create permission for tenant admin
            if (!Permission::where('name', 'tenant-admin-' . $tenant->slug)->exists()) {
                Permission::create(['name' => 'tenant-admin-' . $tenant->slug]);
            }
            
            // assign permissions to tenant admin role
            $role = Role::where('name', 'Tenant Admin ' . $tenant->slug)->first();
            $role->givePermissionTo('tenant-admin-' . $tenant->slug);
            $role->givePermissionTo('admin');
        
            // assign permissions to Super Admin role
            $superAdminRole = Role::where('name', 'Super Admin')->first();
            $superAdminRole->givePermissionTo('tenant-admin-' . $tenant->slug);
            $superAdminRole->givePermissionTo('admin');
        }
    }
} 