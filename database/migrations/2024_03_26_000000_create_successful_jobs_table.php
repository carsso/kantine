<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('successful_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('result')->nullable();
            $table->timestamp('finished_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('successful_jobs');
    }
}; 