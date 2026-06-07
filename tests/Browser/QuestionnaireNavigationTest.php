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

class QuestionnaireNavigationTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function makeSetup(array $qAttrs = []): array
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $token      = Str::random(48);

        $q = Questionnaire::create(array_merge([
            'client_id' => $client->id,
            'token'     => $token,
            'sections'  => ['julia_ross', 'metabolique', 'diathese', 'ayurveda', 'groupe_sanguin'],
        ], $qAttrs));

        return [$conseiller, $client, $q, $token];
    }

    // ── 1. Créer une nouvelle session depuis la fiche client ──────────────────

    public function test_conseiller_cree_nouvelle_session_depuis_fiche_client(): void
    {
        [$conseiller, $client] = $this->makeSetup(['submitted_at' => now()]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}")
                ->waitFor('[dusk="btn-open-nouvelle-session"]', 5)
                ->click('[dusk="btn-open-nouvelle-session"]')
                ->waitFor('#nouvelleSessionModal.show', 5)
                ->press('Créer la session')
                ->waitForText('session', 10);
        });

        $this->assertEquals(2, Questionnaire::where('client_id', $client->id)->count());
    }

    // ── 2. Une seule session active à la fois ────────────────────────────────

    public function test_une_seule_session_peut_etre_active_a_la_fois(): void
    {
        [$conseiller, $client] = $this->makeSetup(['submitted_at' => now()]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}")
                ->waitFor('[dusk="btn-open-nouvelle-session"]', 5)
                ->click('[dusk="btn-open-nouvelle-session"]')
                ->waitFor('#nouvelleSessionModal.show', 5)
                ->press('Créer la session')
                ->waitForText('session', 10);
        });

        // Seulement une session is_active = true
        $this->assertEquals(
            1,
            Questionnaire::where('client_id', $client->id)->where('is_active', true)->count()
        );
    }

    // ── 3. Navigation entre les sections (accordion) ──────────────────────────

    public function test_navigation_entre_sections_fonctionne(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                // Section 1 (Julia Ross) ouverte par défaut
                ->assertVisible('#s1')
                // Cliquer sur section 2 (Métabolique)
                ->click('#wrap-s2 .accordion-button')
                ->waitFor('#s2.show', 5)
                ->assertVisible('#s2')
                // Cliquer sur section 3 (Diathèses)
                ->click('#wrap-s3 .accordion-button')
                ->waitFor('#s3.show', 5)
                ->assertVisible('#s3');
        });
    }

    // ── 4. Autosave : réponses persistées après rechargement ─────────────────

    public function test_autosave_persiste_reponses_apres_rechargement(): void
    {
        [$conseiller, $client] = $this->makeSetup(['sections' => ['julia_ross']]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('#jr_3_4_heures', 5)
                ->type('#jr_3_4_heures', 'après le dîner')
                // Attendre l'autosave (debounce 2s + toast)
                ->waitFor('#saveToast.show', 8)
                ->refresh()
                ->waitFor('#jr_3_4_heures', 5)
                ->assertInputValue('#jr_3_4_heures', 'après le dîner');
        });
    }

    // ── 5. Lien public (token valide) → formulaire visible sans connexion ─────

    public function test_lien_public_token_valide_affiche_formulaire_sans_connexion(): void
    {
        [, , , $token] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->assertPresent('form')
                ->assertPresent('[data-section]');
        });
    }

    // ── 6. Token invalide → 403 ou message d'erreur ──────────────────────────

    public function test_lien_public_token_invalide_retourne_erreur(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/q/token-inexistant-bidon-xxxx')
                ->assertSee('404');
        });
    }

    // ── 7. Soumission via lien public → page merci affichée ─────────────────

    public function test_soumission_via_lien_public_affiche_page_merci(): void
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
                ->waitFor('label[for="gs_1"]', 5)
                ->click('label[for="gs_1"]')
                ->check('#rgpdConsent')
                ->press('Soumettre le questionnaire')
                ->waitForText('Merci', 10)
                ->assertSee('Merci')
                ->assertSee('Votre questionnaire a bien été soumis');
        });
    }

    // ── 8. Questionnaire déjà soumis → page merci directement ───────────────

    public function test_questionnaire_deja_soumis_affiche_directement_page_merci(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $token      = Str::random(48);

        Questionnaire::create([
            'client_id'    => $client->id,
            'token'        => $token,
            'sections'     => ['groupe_sanguin'],
            'submitted_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($token) {
            // Visiter le lien public d'un questionnaire déjà soumis → merci directement
            $browser->visit("/q/{$token}")
                ->assertSee('Merci')
                ->assertSee('Votre questionnaire a bien été soumis');
        });
    }
}
