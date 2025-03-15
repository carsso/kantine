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
        Schema::create('dishes_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->nullable();
            $table->string('name_slug')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->string('emoji')->nullable();
            $table->string('type')->nullable();
            $table->boolean('hidden')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('dishes_categories')->onDelete('set null');
            $table->integer('sort_order')->default(0);
        });
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date');
            $table->foreignId('dishes_category_id')
                  ->nullable()
                  ->constrained('dishes_categories')
                  ->onDelete('set null');
            $table->string('name')->nullable();
            $table->string('tags')->nullable();
        });
        Schema::create('informations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date');
            $table->string('event_name')->nullable();
            $table->string('information')->nullable();
            $table->string('style')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishes');
        Schema::dropIfExists('dishes_categories');
        Schema::dropIfExists('informations');
    }
};
