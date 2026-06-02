<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->text('prenom')->change();
            $table->text('nom')->change();
            $table->text('tel')->change();
            $table->text('email')->nullable()->change();
            $table->text('sexe')->nullable()->change();
            $table->text('age')->nullable()->change();
            $table->text('taille')->nullable()->change();
            $table->text('poids')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('prenom')->change();
            $table->string('nom')->change();
            $table->string('tel')->change();
            $table->string('email')->nullable()->change();
            $table->string('sexe', 20)->nullable()->change();
            $table->unsignedSmallInteger('age')->nullable()->change();
            $table->unsignedSmallInteger('taille')->nullable()->change();
            $table->decimal('poids', 5, 1)->nullable()->change();
        });
    }
};
