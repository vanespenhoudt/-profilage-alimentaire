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

class QuestionnairePublicTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function makeQuestionnaire(array $attrs = []): array
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $token      = Str::random(48);

        $q = Questionnaire::create(array_merge([
            'client_id' => $client->id,
            'token'     => $token,
            'sections'  => ['julia_ross', 'groupe_sanguin'],
        ], $attrs));

        return [$token, $q, $client];
    }

    public function test_client_voit_le_questionnaire_via_le_lien(): void
    {
        [$token] = $this->makeQuestionnaire();

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->assertPresent('form')
                ->assertPresent('[data-section]');
        });
    }

    public function test_page_merci_si_questionnaire_deja_soumis(): void
    {
        [$token, $q] = $this->makeQuestionnaire();
        $q->update(['submitted_at' => now()]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->assertSee('Merci');
        });
    }

    public function test_client_peut_choisir_groupe_sanguin_et_soumettre(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $token      = Str::random(48);

        Questionnaire::create([
            'client_id' => $client->id,
            'token'     => $token,
            'sections'  => ['groupe_sanguin'],
        ]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->assertPresent('input[name="groupe_sanguin"]')
                ->click('label[for="gs_1"]')
                ->check('#rgpdConsent')
                ->press('Soumettre le questionnaire')
                ->waitForText('Merci', 10)
                ->assertSee('Merci');
        });
    }

    public function test_lien_invalide_retourne_404(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/q/token-inexistant-xxxx')
                ->assertSee('404');
        });
    }
}
