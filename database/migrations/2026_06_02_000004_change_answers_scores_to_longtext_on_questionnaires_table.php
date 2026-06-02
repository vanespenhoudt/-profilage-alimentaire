<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            // json type enforces valid JSON — encrypted:array stores base64, not JSON
            $table->longText('answers')->nullable()->change();
            $table->longText('scores')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->json('answers')->nullable()->change();
            $table->json('scores')->nullable()->change();
        });
    }
};
