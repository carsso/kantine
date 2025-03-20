<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::create([
            'name' => 'Roubaix',
            'slug' => 'roubaix',
            'description' => 'Cantine de Roubaix',
        ]);
    }
} 