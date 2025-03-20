<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('webex_bearer_token')->nullable();
            $table->string('webex_bot_name')->nullable();
            $table->timestamps();
        });

        DB::table('tenants')->insert([
            'name' => 'Roubaix',
            'slug' => 'roubaix',
            'description' => 'Cantine de Roubaix',
            'webex_bearer_token' => config('services.webex.bearer_token'),
            'webex_bot_name' => config('services.webex.bot_name'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('dishes', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->nullable();
        });

        Schema::table('dishes_categories', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->nullable();
        });

        Schema::table('informations', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->nullable();
        });

        DB::table('dishes')->update(['tenant_id' => 1]);
        DB::table('dishes_categories')->update(['tenant_id' => 1]);
        DB::table('informations')->update(['tenant_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('dishes_categories', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::table('informations', function (Blueprint $table) {
            $table->dropColumn('tenant_id');
        });

        Schema::dropIfExists('tenants');
    }
};
