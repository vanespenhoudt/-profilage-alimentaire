<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->string('token', 64)->nullable()->unique()->after('client_id');
            $table->timestamp('submitted_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn(['token', 'submitted_at']);
        });
    }
};
