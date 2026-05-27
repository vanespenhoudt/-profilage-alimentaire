<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->unsignedSmallInteger('age')->nullable()->after('nom');
            $table->string('sexe', 20)->nullable()->after('age');
            $table->unsignedSmallInteger('taille')->nullable()->after('sexe');
            $table->decimal('poids', 5, 1)->nullable()->after('taille');
            $table->text('sentinelles')->nullable()->after('poids');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['age', 'sexe', 'taille', 'poids', 'sentinelles']);
        });
    }
};
