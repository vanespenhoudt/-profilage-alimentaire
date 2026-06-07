<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use App\Services\QuestionnaireScorer;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BilanTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Crée un conseiller, client et questionnaire soumis avec des scores complets.
     */
    private function makeSubmittedQuestionnaire(array $overrideAnswers = []): array
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);

        $answers = array_merge($this->buildMinimalAnswers(), $overrideAnswers);
        $scores  = (new QuestionnaireScorer())->calculate($answers);

        $q = Questionnaire::create([
            'client_id'            => $client->id,
            'token'                => Str::random(48),
            'sections'             => ['julia_ross', 'metabolique', 'diathese', 'ayurveda', 'hormones', 'canaris'],
            'answers'              => $answers,
            'scores'               => $scores,
            'submitted_at' => now(),
        ]);

        return [$conseiller, $client, $q, $scores];
    }

    /**
     * Ensemble minimal de réponses pour obtenir des scores dans toutes les sections.
     * Profil : Chasseur B, Pitta dominant, jr3 dépassé, D1 dominant, h1 élevé, Canari grade 1.
     */
    private function buildMinimalAnswers(): array
    {
        $a = [];

        // Métabolique — Chasseur B fort
        for ($i = 1; $i <= 37; $i++) {
            $a["mb{$i}"] = 'b';
        }

        // Ayurveda — Pitta dominant
        for ($i = 0; $i < 19; $i++) { $a["v{$i}"] = '2'; }
        for ($i = 0; $i < 20; $i++) { $a["p{$i}"] = '6'; }
        for ($i = 0; $i < 20; $i++) { $a["k{$i}"] = '1'; }

        // Julia Ross — jr3 Glycémie dépassé (seuil 15)
        $a['jr3_0'] = '1'; $a['jr3_1'] = '1'; $a['jr3_2'] = '1';
        $a['jr3_3'] = '1'; $a['jr3_5'] = '1'; $a['jr3_8'] = '1';

        // Diathèses — D1 dominant
        foreach (['d1a', 'd1b', 'd1c', 'd1d', 'd1e', 'd1f', 'd1g'] as $k) { $a[$k] = 'd1'; }
        foreach (['d2a', 'd2b', 'd2c', 'd2d', 'd2e', 'd2f', 'd2g'] as $k) { $a[$k] = 'd1'; }

        // Hormones — Progestérone élevée
        $a['h1_0'] = '1'; $a['h1_1'] = '1'; $a['h1_2'] = '1';
        $a['h1_4'] = '1'; $a['h1_6'] = '1';

        // Canaris — grade 1
        $a['ca1'] = '1'; $a['ca2'] = '1'; $a['ca3'] = '1';

        return $a;
    }

    // ── 1. Après soumission, le conseiller peut accéder au bilan ────────────

    public function test_conseiller_peut_acceder_au_bilan_apres_soumission(): void
    {
        [$conseiller, $client] = $this->makeSubmittedQuestionnaire();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertPathIs("/clients/{$client->id}/bilan")
                ->assertPresent('.section-header');
        });
    }

    // ── 2. Typage Métabolique affiché ────────────────────────────────────────

    public function test_bilan_affiche_typage_metabolique(): void
    {
        [$conseiller, $client] = $this->makeSubmittedQuestionnaire();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertSee('Typage Métabolique')
                // Avec les réponses b dominantes → Chasseur
                ->assertSee('Chasseur');
        });
    }

    // ── 3. Doshas Ayurveda affichés ──────────────────────────────────────────

    public function test_bilan_affiche_doshas_ayurveda(): void
    {
        [$conseiller, $client] = $this->makeSubmittedQuestionnaire();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertSee('Ayurveda')
                ->assertSee('Vâta')
                ->assertSee('Pitta')
                ->assertSee('Kapha')
                // Avec Pitta dominant → type Pitta affiché
                ->assertSee('Pitta');
        });
    }

    // ── 4. 8 classes Julia Ross affichées ───────────────────────────────────

    public function test_bilan_affiche_les_8_classes_julia_ross(): void
    {
        [$conseiller, $client] = $this->makeSubmittedQuestionnaire();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertSee('Julia Ross');

            // Vérifier qu'au moins 8 lignes de classes sont présentes dans le tableau
            $rowCount = (int) $browser->script(
                "return document.querySelectorAll('table tr').length"
            )[0];

            $this->assertGreaterThanOrEqual(8, $rowCount);
        });
    }

    // ── 5. Diathèses affichées ───────────────────────────────────────────────

    public function test_bilan_affiche_les_diatheses_de_menetrier(): void
    {
        [$conseiller, $client] = $this->makeSubmittedQuestionnaire();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertSee('Diathèse')
                // D1 dominant → Terrain robuste
                ->assertSee('Terrain');
        });
    }

    // ── 6. 8 catégories hormonales affichées ────────────────────────────────

    public function test_bilan_affiche_les_8_categories_hormonales(): void
    {
        [$conseiller, $client] = $this->makeSubmittedQuestionnaire();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertSee('Hormones')
                ->assertSee('Progestérone')
                ->assertSee('Cortisol')
                ->assertSee('Œstradiol');

            // Vérifier que les 8 catégories sont présentes via les éléments .hormones-cat-name
            $count = $browser->script(
                "return document.querySelectorAll('.hormones-cat-name').length"
            )[0];

            $this->assertEquals(8, (int) $count);
        });
    }

    // ── 7. Grade Canaris (I, II ou III) affiché ──────────────────────────────

    public function test_bilan_affiche_grade_canaris(): void
    {
        [$conseiller, $client] = $this->makeSubmittedQuestionnaire();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertSee('Canaris')
                // Le badge de grade doit être présent (grade_1 → Grade I, etc.)
                ->assertPresent('.canaris-badge');
        });
    }

    // ── 8. Comparaison de 2 sessions côte à côte ─────────────────────────────

    public function test_comparaison_de_deux_sessions_affiche_bilans_cote_a_cote(): void
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);

        $answers = $this->buildMinimalAnswers();
        $scores  = (new QuestionnaireScorer())->calculate($answers);

        // Session A (archivée)
        $q1 = Questionnaire::create([
            'client_id'    => $client->id,
            'token'        => Str::random(48),
            'sections'     => ['metabolique'],
            'answers'      => $answers,
            'scores'       => $scores,
            'submitted_at' => now()->subMonth(),
            'session_label' => 'Session 1',
            'is_active'    => false,
        ]);

        // Session B (active)
        $q2 = Questionnaire::create([
            'client_id'    => $client->id,
            'token'        => Str::random(48),
            'sections'     => ['metabolique'],
            'answers'      => $answers,
            'scores'       => $scores,
            'submitted_at' => now(),
            'session_label' => 'Session 2',
            'is_active'    => true,
        ]);

        $this->browse(function (Browser $browser) use ($conseiller, $client, $q1, $q2) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertPresent('select[name="session_a"]')
                ->select('session_a', (string) $q1->id)
                ->select('session_b', (string) $q2->id)
                ->press('Comparer')
                ->waitForText('Session 1', 8)
                ->assertSee('Session 1')
                ->assertSee('Session 2');
        });
    }

    // ── 9. Notes d'interprétation visibles dans le bilan ────────────────────

    public function test_bilan_affiche_notes_interpretation_du_conseiller(): void
    {
        [$conseiller, $client, $q] = $this->makeSubmittedQuestionnaire();

        // Pré-remplir les notes
        $q->update([
            'interpretation_notes' => [
                'metabolique' => 'Note de test Métabolique',
                'ayurveda'    => 'Note de test Ayurveda',
            ],
        ]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertSee('Note de test Métabolique')
                ->assertSee('Note de test Ayurveda');
        });
    }
}
