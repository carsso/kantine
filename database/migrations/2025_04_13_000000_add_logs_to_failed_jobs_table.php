<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->longText('logs')->nullable()->after('exception');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->dropColumn('logs');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}; 