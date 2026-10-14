<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DuskTestSeeder;
use Database\Seeders\TestQuestionnaireSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedersProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_de_test_ne_creent_rien_en_production(): void
    {
        $this->app['env'] = 'production';

        foreach ([DatabaseSeeder::class, TestQuestionnaireSeeder::class, DuskTestSeeder::class] as $seeder) {
            $this->app->make($seeder)->__invoke();
        }

        $this->assertSame(0, User::count());
    }

    public function test_database_seeder_fonctionne_hors_production(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(User::where('email', 'admin@profilage.local')->exists());
    }
}
