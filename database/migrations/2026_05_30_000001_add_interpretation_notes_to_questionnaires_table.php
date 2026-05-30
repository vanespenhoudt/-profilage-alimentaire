<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->json('interpretation_notes')->nullable()->after('menu_visible_client');
        });
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('interpretation_notes');
        });
    }
};
