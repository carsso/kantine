<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Illuminate\Console\Command;

class SetSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:set-super-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets Super Admin role to user';

    /**
     * @var TenantRolesAndPermissionsService
     */
    protected $rolesAndPermissionsService;

    /**
     * Create a new command instance.
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
        $this->info('Creating roles and permissions for all tenants');
        // Create roles and permissions using the service
        $this->rolesAndPermissionsService->createTenantRolesAndPermissions();

        $this->info('Setting Super Admin role to user');

        // load user from email in argument and assign role
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('User not found');

            return 1;
        }
        $user->assignRole('Super Admin');
        $this->info('Super Admin role set to user');

        return 0;
    }
}
