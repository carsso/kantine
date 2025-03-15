<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use App\Models\User;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Setting Super Admin role to user');
        // create super admin role if not exists
        if (!Role::where('name', 'Super Admin')->exists()) {
            Role::create(['name' => 'Super Admin']);
        }
        // load user from email in argument and assign role
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('User not found');
            return 1;
        }
        $user->assignRole('Super Admin');
        $this->info('Super Admin role set to user');
        return 0;
    }
}
