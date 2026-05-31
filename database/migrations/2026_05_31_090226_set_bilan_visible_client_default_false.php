<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('questionnaires')->update(['bilan_visible_client' => false]);
    }

    public function down(): void
    {
        //
    }
};
