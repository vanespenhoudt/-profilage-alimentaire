<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->string('session_label')->nullable()->after('client_id');
            $table->boolean('is_active')->default(true)->after('session_label');
        });

        // Marquer toutes les sessions existantes comme actives (rétrocompatibilité)
        \Illuminate\Support\Facades\DB::table('questionnaires')->update(['is_active' => true]);
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn(['session_label', 'is_active']);
        });
    }
};
