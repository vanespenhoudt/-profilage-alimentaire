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

/**
 * Vérifie les 3 corrections JS dans la vue publique (/q/{token}) :
 *  - Bug 1 : Métaboltyping — exclusivité (1 seule réponse par ligne)
 *  - Bug 2 : Ayurveda — validation des réponses manquantes avant soumission
 *  - Bug 3 : Aliments & Menu — validation du contenu avant soumission
 */
class QuestionnairePublicValidationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function runDatabaseMigrations(): void
    {
        $this->artisan('migrate:fresh');
        $this->app[\Illuminate\Contracts\Console\Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('db:wipe');
        });
    }

    private function makeSetup(array $sections = ['groupe_sanguin'], array $qAttrs = []): array
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $token      = Str::random(48);

        $q = Questionnaire::create(array_merge([
            'client_id'           => $client->id,
            'token'               => $token,
            'sections'            => $sections,
            'menu_visible_client' => false,
        ], $qAttrs));

        return [$token, $q];
    }

    private function clickSubmit(Browser $browser): void
    {
        $browser->script('document.getElementById("submitBtn").click();');
        $browser->pause(500);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Bug 1 — Métaboltyping : exclusivité par groupe
    // ═══════════════════════════════════════════════════════════════════════════

    public function test_pub_metaboltyping_cocher_b_decoche_a(): void
    {
        // 'metabolique' seul → s2 est la première section, déjà ouverte (class="show")
        [$token] = $this->makeSetup(['metabolique']);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->waitFor('#s2.show', 5); // déjà ouverte, pas besoin de cliquer

            $qid = $browser->script('
                var els = document.querySelectorAll("[data-section=\'s2\']");
                return els[0] ? els[0].dataset.qid : null;
            ')[0];

            $this->assertNotNull($qid, 'La section s2 doit contenir des questions');

            // Cocher A
            $browser->script("
                var cbA = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"_A\"]');
                if (cbA) { cbA.checked = true; cbA.dispatchEvent(new Event('change', {bubbles:true})); }
            ");
            $browser->pause(150);

            // Cocher B → A doit se décocher
            $browser->script("
                var cbB = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"_B\"]');
                if (cbB) { cbB.checked = true; cbB.dispatchEvent(new Event('change', {bubbles:true})); }
            ");
            $browser->pause(150);

            $result = $browser->script("
                var cbA = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"_A\"]');
                var cbB = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"_B\"]');
                return [cbA ? cbA.checked : null, cbB ? cbB.checked : null];
            ")[0];

            $this->assertFalse($result[0], 'A doit être décoché après avoir coché B');
            $this->assertTrue($result[1],  'B doit rester coché');
        });
    }

    public function test_pub_metaboltyping_cycle_a_b_a(): void
    {
        [$token] = $this->makeSetup(['metabolique']);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->waitFor('#s2.show', 5);

            $qid = $browser->script('
                var els = document.querySelectorAll("[data-section=\'s2\']");
                return els[0] ? els[0].dataset.qid : null;
            ')[0];

            foreach (['_A', '_B', '_A'] as $suffix) {
                $browser->script("
                    var cb = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"{$suffix}\"]');
                    if (cb) { cb.checked = true; cb.dispatchEvent(new Event('change', {bubbles:true})); }
                ");
                $browser->pause(100);
            }

            $result = $browser->script("
                var cbA = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"_A\"]');
                var cbB = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"_B\"]');
                return [cbA ? cbA.checked : null, cbB ? cbB.checked : null];
            ")[0];

            $this->assertTrue($result[0],  'A doit être coché (dernier coché du cycle)');
            $this->assertFalse($result[1], 'B doit être décoché');
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Bug 2 — Ayurveda : validation avant soumission
    // ═══════════════════════════════════════════════════════════════════════════

    public function test_pub_soumission_bloquee_si_ayurveda_incomplet(): void
    {
        [$token] = $this->makeSetup(['ayurveda']);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}");

            // Soumettre sans aucune réponse Ayurveda
            $this->clickSubmit($browser);

            $alertText = $browser->script('
                var el = document.getElementById("ayurveda-validation-alert");
                return el ? el.textContent : "";
            ')[0];

            $this->assertStringContainsString('question(s) sans réponse', $alertText);
            $this->assertStringContainsString('59', $alertText);

            // Pas de navigation — toujours sur la même page
            $browser->assertPathContains('/q/');
        });
    }

    public function test_pub_alerte_ayurveda_masquee_si_tout_rempli(): void
    {
        [$token] = $this->makeSetup(['ayurveda']);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}");

            // Remplir les 59 questions
            $browser->script('
                var names = [];
                for (var i = 0; i < 19; i++) names.push("v" + i);
                for (var i = 0; i < 20; i++) names.push("p" + i);
                for (var i = 0; i < 20; i++) names.push("k" + i);
                names.forEach(function(n) {
                    var inp = document.querySelector("input[name=\"" + n + "\"][value=\"3\"]");
                    if (inp) inp.checked = true;
                });
            ');

            $this->clickSubmit($browser);

            $display = $browser->script('
                var el = document.getElementById("ayurveda-validation-alert");
                return el ? el.style.display : "none";
            ')[0];

            $this->assertEquals('none', $display, 'L\'alerte Ayurveda doit être masquée quand tout est rempli');
        });
    }

    public function test_pub_s4_souvre_automatiquement_si_ayurveda_incomplet(): void
    {
        // metabolique en premier → s2 ouvert par défaut, s4 fermé
        [$token] = $this->makeSetup(['metabolique', 'ayurveda']);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}");

            $isShownBefore = $browser->script('return document.getElementById("s4").classList.contains("show");')[0];
            $this->assertFalse($isShownBefore, 'S4 doit être fermée avant la soumission');

            $this->clickSubmit($browser);

            $browser->waitFor('#s4.show', 5);

            $isShownAfter = $browser->script('return document.getElementById("s4").classList.contains("show");')[0];
            $this->assertTrue($isShownAfter, 'S4 doit s\'ouvrir si Ayurveda est incomplet');
        });
    }

    public function test_pub_ayurveda_non_valide_si_section_absente(): void
    {
        // Sans la section ayurveda → pas de validation Ayurveda
        [$token] = $this->makeSetup(['groupe_sanguin']);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->check('#rgpdConsent');

            $this->clickSubmit($browser);

            // Pas d'alerte Ayurveda (section absente)
            $alertEl = $browser->script('return document.getElementById("ayurveda-validation-alert");')[0];
            $this->assertNull($alertEl, 'Aucune alerte Ayurveda si la section n\'est pas activée');
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Bug 3 — Validation aliments & menu (menu_visible_client = true)
    // ═══════════════════════════════════════════════════════════════════════════

    public function test_pub_alerte_si_moins_de_10_aliments(): void
    {
        [$token] = $this->makeSetup(['groupe_sanguin'], ['menu_visible_client' => true]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->waitFor('#aliments_text', 5);

            // 5 aliments seulement
            $browser->script('document.getElementById("aliments_text").value = "A\\nB\\nC\\nD\\nE";');
            // Menu rempli pour isoler le test aliments
            $browser->script('document.getElementById("menu_text").value = "Lundi : poulet, riz, salade.";');

            $this->clickSubmit($browser);

            $alertText = $browser->script('
                var el = document.getElementById("aliments-validation-alert");
                return el ? el.textContent : "";
            ')[0];

            $this->assertStringContainsString('5 renseigné(s) sur 10', $alertText);
        });
    }

    public function test_pub_pas_d_alerte_aliments_si_10_lignes(): void
    {
        [$token] = $this->makeSetup(['groupe_sanguin'], ['menu_visible_client' => true]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->waitFor('#aliments_text', 5);

            $browser->script('document.getElementById("aliments_text").value = "A\\nB\\nC\\nD\\nE\\nF\\nG\\nH\\nI\\nJ";');
            $browser->script('document.getElementById("menu_text").value = "Lundi : poulet, riz, salade.";');

            $this->clickSubmit($browser);

            $display = $browser->script('
                var el = document.getElementById("aliments-validation-alert");
                return el ? el.style.display : "none";
            ')[0];

            $this->assertEquals('none', $display, 'Pas d\'alerte aliments si 10 lignes renseignées');
        });
    }

    public function test_pub_alerte_si_menu_vide(): void
    {
        [$token] = $this->makeSetup(['groupe_sanguin'], ['menu_visible_client' => true]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->waitFor('#menu_text', 5);

            // Aliments remplis, menu vide
            $browser->script('document.getElementById("aliments_text").value = "A\\nB\\nC\\nD\\nE\\nF\\nG\\nH\\nI\\nJ";');
            $browser->script('document.getElementById("menu_text").value = "";');

            $this->clickSubmit($browser);

            $alertText = $browser->script('
                var el = document.getElementById("menu-validation-alert");
                return el ? el.textContent : "";
            ')[0];

            $this->assertStringContainsString('Veuillez décrire le menu', $alertText);
        });
    }

    public function test_pub_pas_d_alerte_menu_si_contenu_present(): void
    {
        [$token] = $this->makeSetup(['groupe_sanguin'], ['menu_visible_client' => true]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->waitFor('#menu_text', 5);

            $browser->script('document.getElementById("aliments_text").value = "A\\nB\\nC\\nD\\nE\\nF\\nG\\nH\\nI\\nJ";');
            $browser->script('document.getElementById("menu_text").value = "Lundi : poulet grillé, riz complet.";');

            $this->clickSubmit($browser);

            $display = $browser->script('
                var el = document.getElementById("menu-validation-alert");
                return el ? el.style.display : "none";
            ')[0];

            $this->assertEquals('none', $display, 'Pas d\'alerte menu si le contenu est renseigné');
        });
    }

    public function test_pub_pas_d_alerte_aliments_menu_si_section_absente(): void
    {
        // Sans menu_visible_client → textareas absents → validation ignorée
        [$token] = $this->makeSetup(['groupe_sanguin'], ['menu_visible_client' => false]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->check('#rgpdConsent');

            $this->clickSubmit($browser);

            $alimentsEl = $browser->script('return document.getElementById("aliments-validation-alert");')[0];
            $menuEl     = $browser->script('return document.getElementById("menu-validation-alert");')[0];

            $this->assertNull($alimentsEl, 'Pas d\'alerte aliments si la section est masquée');
            $this->assertNull($menuEl,     'Pas d\'alerte menu si la section est masquée');
        });
    }
}
