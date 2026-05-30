<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->text('aliments_text')->nullable()->after('bilan_visible_client');
            $table->boolean('aliments_visible_client')->default(false)->after('aliments_text');
        });
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn(['aliments_text', 'aliments_visible_client']);
        });
    }
};
