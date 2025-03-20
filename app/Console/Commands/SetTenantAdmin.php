<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Illuminate\Console\Command;

class SetTenantAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:set-tenant-admin {tenant_slug} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets Tenant Admin role to user';

    /**
     * @var TenantRolesAndPermissionsService
     */
    protected $rolesAndPermissionsService;

    /**
     * Create a new command instance.
     *
     * @param TenantRolesAndPermissionsService $rolesAndPermissionsService
     */
    public function __construct(TenantRolesAndPermissionsService $rolesAndPermissionsService)
    {
        parent::__construct();
        $this->rolesAndPermissionsService = $rolesAndPermissionsService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenant = Tenant::where('slug', $this->argument('tenant_slug'))->first();
        if(!$tenant) {
            $this->error('Tenant not found');
            return 1;
        }

        $this->info('Creating roles and permissions for all tenants');
        // Create roles and permissions using the service
        $this->rolesAndPermissionsService->createTenantRolesAndPermissions();

        $this->info('Setting Tenant Admin role to user for tenant ' . $tenant->name);
        // load user from email in argument and assign role
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('User not found');
            return 1;
        }
        $user->assignRole('Tenant Admin ' . $tenant->slug);
        $this->info('Tenant Admin role set to user for tenant ' . $tenant->name);
        return 0;
    }
}
