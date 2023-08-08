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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date')->unique();
            $table->string('event_name')->nullable();
            $table->string('starters')->nullable();
            $table->string('mains')->nullable();
            $table->string('sides')->nullable();
            $table->string('cheeses')->nullable();
            $table->string('desserts')->nullable();
            $table->string('file_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
