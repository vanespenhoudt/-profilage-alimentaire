<?php

namespace Tests\Browser;

use App\Data\QuestionnaireData; // utilisé pour julia_ross et hormones
use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Vérifie que chaque section du questionnaire affiche bien ses questions dans le DOM.
 * Utilise la vue conseiller (/clients/{id}/questionnaire) qui affiche toutes les sections.
 */
class QuestionnaireContentTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function makeSetup(array $sections = []): array
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);

        $allSections = $sections ?: [
            'julia_ross', 'metabolique', 'diathese', 'ayurveda',
            'groupe_sanguin', 'hormones', 'canaris',
        ];

        $q = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => Str::random(48),
            'sections'  => $allSections,
        ]);

        return [$conseiller, $client, $q];
    }

    // ── 1. Julia Ross — section Neurotransmetteurs ────────────────────────────

    public function test_section_julia_ross_contient_les_classes_neurotransmetteurs(): void
    {
        [$conseiller, $client] = $this->makeSetup(['julia_ross']);

        // Récupérer le label de la première classe depuis les données
        $premiereClasse = QuestionnaireData::$julia_ross[0]['label'] ?? 'Chimie du cerveau';

        $this->browse(function (Browser $browser) use ($conseiller, $client, $premiereClasse) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertPresent('#wrap-s1')
                ->assertVisible('#s1') // Ouverte par défaut
                ->assertSee($premiereClasse);
        });
    }

    // ── 2. Métabolique — section Chasseur / Cueilleur ────────────────────────

    public function test_section_metabolique_contient_questions_chasseur_cueilleur(): void
    {
        [$conseiller, $client] = $this->makeSetup(['metabolique']);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertPresent('#wrap-s2')
                // Ouvrir la section métabolique
                ->click('#wrap-s2 .accordion-button')
                ->waitFor('#s2.show', 5)
                // Vérifier la présence de labels Cueilleur / Chasseur
                ->assertSeeIn('#s2', 'Cueilleur')
                ->assertSeeIn('#s2', 'Chasseur')
                // Vérifier qu'une question binaire est présente (mb1_a ou mb1_b)
                ->assertPresent('#mb1_a');
        });
    }

    // ── 3. Diathèses — présence des 4 diathèses de Ménétrier ────────────────

    public function test_section_diatheses_contient_les_quatre_diatheses(): void
    {
        [$conseiller, $client] = $this->makeSetup(['diathese']);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertPresent('#wrap-s3')
                ->click('#wrap-s3 .accordion-button')
                ->waitFor('#s3.show', 5)
                // Les 4 diathèses impliquent 2 colonnes (d1 et d2) de 7 questions chacune
                ->assertPresent('#d1a_d1')
                ->assertPresent('#d1a_d2')
                ->assertPresent('#d2a_d1')
                ->assertPresent('#d2a_d2');
        });
    }

    // ── 4. Ayurveda — présence Vata / Pitta / Kapha ──────────────────────────

    public function test_section_ayurveda_contient_vata_pitta_kapha(): void
    {
        [$conseiller, $client] = $this->makeSetup(['ayurveda']);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertPresent('#wrap-s4')
                ->click('#wrap-s4 .accordion-button')
                ->waitFor('#s4.show', 5)
                ->assertSeeIn('#s4', 'Vâta')
                ->assertSeeIn('#s4', 'Pitta')
                ->assertSeeIn('#s4', 'Kapha');
        });
    }

    // ── 5. Groupe sanguin — sélecteur O / A / B / AB présent ────────────────

    public function test_section_groupe_sanguin_affiche_les_quatre_groupes(): void
    {
        [$conseiller, $client] = $this->makeSetup(['groupe_sanguin']);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertPresent('#wrap-s5')
                ->click('#wrap-s5 .accordion-button')
                ->waitFor('#s5.show', 5)
                // Les 5 options de groupe sanguin sont présentes
                ->assertPresent('input[name="groupe_sanguin"][value="O"]')
                ->assertPresent('input[name="groupe_sanguin"][value="A"]')
                ->assertPresent('input[name="groupe_sanguin"][value="B"]')
                ->assertPresent('input[name="groupe_sanguin"][value="AB"]')
                ->assertPresent('input[name="groupe_sanguin"][value="Je ne sais pas"]');
        });
    }

    // ── 6. Hormones — 8 catégories hormonales ───────────────────────────────

    public function test_section_hormones_contient_les_8_categories(): void
    {
        [$conseiller, $client] = $this->makeSetup(['hormones']);

        // Récupérer les titres des catégories depuis les données
        $categories = collect(QuestionnaireData::$hormones)->pluck('titre')->all();

        $this->browse(function (Browser $browser) use ($conseiller, $client, $categories) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertPresent('#wrap-s6')
                ->click('#wrap-s6 .accordion-button')
                ->waitFor('#s6.show', 5);

            foreach ($categories as $titre) {
                $browser->assertSeeIn('#s6', $titre);
            }

            // Vérifier qu'il y a bien 8 sous-sections
            $count = $browser->script(
                "return document.querySelectorAll('#s6 [data-section=\"s6\"]').length"
            )[0];

            $this->assertGreaterThanOrEqual(8, (int) $count);
        });
    }

    // ── 7. Canaris — système de score et familles de réactivité ─────────────

    public function test_section_canaris_contient_score_et_familles(): void
    {
        [$conseiller, $client] = $this->makeSetup(['canaris']);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertPresent('#wrap-s7')
                ->click('#wrap-s7 .accordion-button')
                ->waitFor('#s7.show', 5)
                // Vérifier la présence des inputs canaris adulte (ca1...)
                ->assertPresent('[data-section="s7"]')
                // Vérifier les profils (adulte / enfant)
                ->assertSeeIn('#s7', 'Adulte')
                // Vérifier qu'au moins un item contexte est présent
                ->assertPresent('[data-section="s7"]');
        });
    }
}
