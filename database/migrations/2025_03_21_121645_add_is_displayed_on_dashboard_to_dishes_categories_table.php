<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dishes_categories', function (Blueprint $table) {
            $table->boolean('hidden_from_dashboard')->default(false)->after('emoji');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dishes_categories', function (Blueprint $table) {
            $table->dropColumn('hidden_from_dashboard');
        });
    }
};
