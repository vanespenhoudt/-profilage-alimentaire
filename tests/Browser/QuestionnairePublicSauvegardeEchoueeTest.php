<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class QuestionnairePublicSauvegardeEchoueeTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_reponses_conservees_quand_la_sauvegarde_echoue(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $token      = Str::random(48);
        $q          = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => $token,
            'sections'  => ['groupe_sanguin'],
        ]);

        $this->browse(function (Browser $browser) use ($token, $q) {
            $browser->visit("/q/{$token}")
                ->assertSee('Vous faites une pause ?')
                ->click('label[for="gs_1"]')
                ->waitForText('Dernière sauvegarde', 10);

            $this->assertSame('A', $q->fresh()->answers['groupe_sanguin'] ?? null);

            // Connexion perdue → la sauvegarde échoue
            $browser->script("window.fetch = () => Promise.reject(new Error('offline'));");
            $browser->click('label[for="gs_2"]')
                ->waitFor('#saveErrorBanner:not(.d-none)', 10)
                ->assertSee('vos dernières réponses ne sont pas enregistrées');

            $this->assertSame('A', $q->fresh()->answers['groupe_sanguin'] ?? null);

            // Rechargement : la réponse non enregistrée est récupérée puis sauvegardée
            $browser->click('#reloadKeepBtn')
                ->waitFor('#draftRestoredBanner:not(.d-none)', 10)
                ->assertChecked('#gs_2')
                ->waitForText('Dernière sauvegarde :', 10)
                ->pause(1500);

            $this->assertSame('B', $q->fresh()->answers['groupe_sanguin'] ?? null);
        });

        // Les réponses chiffrées empêchent le rollback (answers longtext → json) de DatabaseMigrations
        Questionnaire::query()->delete();
    }
}
