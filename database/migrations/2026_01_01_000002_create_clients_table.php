<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('conseiller_id')->constrained('users')->onDelete('cascade');
            $table->string('prenom');
            $table->string('nom');
            $table->string('tel');
            $table->string('email')->nullable();
            $table->text('adresse')->nullable();
            $table->text('bt')->nullable();
            $table->boolean('rgpd')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
