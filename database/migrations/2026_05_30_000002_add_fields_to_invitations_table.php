<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->foreignId('invited_by')->nullable()->after('email')
                  ->constrained('users')->nullOnDelete();
            $table->string('role', 32)->default('conseiller')->after('token');
            $table->timestamp('expires_at')->nullable()->after('used_at');
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['invited_by']);
            $table->dropColumn(['invited_by', 'role', 'expires_at']);
        });
    }
};
