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
 * Vérifie les 3 corrections JS du formulaire questionnaire :
 *  - Bug 1 : Métaboltyping — exclusivité (1 seule réponse par ligne)
 *  - Bug 2 : Ayurveda — validation des réponses manquantes avant soumission
 *  - Bug 3 : Aliments & Menu — validation du contenu avant soumission
 */
class QuestionnaireValidationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Override pour remplacer migrate:rollback (incompatible SQLite/dropColumn+index)
     * par db:wipe qui supprime les tables directement.
     */
    public function runDatabaseMigrations(): void
    {
        $this->artisan('migrate:fresh');
        $this->app[\Illuminate\Contracts\Console\Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('db:wipe');
        });
    }

    private function makeSetup(): array
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $q          = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => Str::random(48),
        ]);

        return [$conseiller, $client, $q];
    }

    private function prefillAliments(Browser $browser): void
    {
        // \\n dans PHP double-quote → \n dans JS → séparateur de ligne dans le textarea
        $browser->script('document.querySelector(\'textarea[name="aliments_text"]\').value = "A\\nB\\nC\\nD\\nE\\nF\\nG\\nH\\nI\\nJ";');
    }

    private function prefillMenu(Browser $browser): void
    {
        $browser->script('var mn = document.querySelector(\'#menuForm textarea[name="menu_text"]\'); if (mn) mn.value = "<p>Menu test</p>";');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Bug 1 — Métaboltyping : exclusivité par groupe
    // ═══════════════════════════════════════════════════════════════════════════

    public function test_metaboltyping_cocher_b_decoche_a_sur_meme_ligne(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->click('[data-bs-target="#s2"]')
                ->waitFor('#s2.show', 5);

            // Récupérer le data-qid de la première question s2
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

            // Cocher B sur la même ligne → A doit se décocher
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

            $this->assertFalse($result[0], 'L\'option A doit être décochée après avoir coché B');
            $this->assertTrue($result[1],  'L\'option B doit rester cochée');
        });
    }

    public function test_metaboltyping_recoch_a_decoche_b(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->click('[data-bs-target="#s2"]')
                ->waitFor('#s2.show', 5);

            $qid = $browser->script('
                var els = document.querySelectorAll("[data-section=\'s2\']");
                return els[0] ? els[0].dataset.qid : null;
            ')[0];

            // Cocher A → B → A (cycle A-B-A, sans M qui peut ne pas exister)
            foreach (['_A', '_B', '_A'] as $suffix) {
                $browser->script("
                    var cb = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"{$suffix}\"]');
                    if (cb) { cb.checked = true; cb.dispatchEvent(new Event('change', {bubbles:true})); }
                ");
                $browser->pause(100);
            }

            // Après A→B→A : seul A doit être coché, B décoché
            $result = $browser->script("
                var cbA = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"_A\"]');
                var cbB = document.querySelector('[data-section=\"s2\"][data-qid=\"{$qid}\"][name\$=\"_B\"]');
                return [cbA ? cbA.checked : null, cbB ? cbB.checked : null];
            ")[0];

            $this->assertTrue($result[0],  'A doit être coché (dernier coché)');
            $this->assertFalse($result[1], 'B doit être décoché');
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Bug 2 — Ayurveda : validation avant soumission
    // ═══════════════════════════════════════════════════════════════════════════

    public function test_soumission_bloquee_et_alerte_si_ayurveda_incomplet(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire");

            // Pré-remplir aliments et menu pour isoler le test Ayurveda
            $this->prefillAliments($browser);
            $this->prefillMenu($browser);

            // Soumettre sans aucune réponse Ayurveda
            $browser->script('document.querySelector("button[form=\'questForm\']").click();');
            $browser->pause(500);

            $alertText = $browser->script('
                var el = document.getElementById("ayurveda-validation-alert");
                return el ? el.textContent : "";
            ')[0];

            $this->assertStringContainsString('question(s) sans réponse', $alertText);
            $this->assertStringContainsString('59', $alertText);

            // Rester sur la page questionnaire (formulaire non soumis)
            $browser->assertPathContains('/questionnaire');
        });
    }

    public function test_alerte_ayurveda_masquee_quand_toutes_reponses_fournies(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire");

            // Remplir les 59 questions Ayurveda via JS
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

            $this->prefillAliments($browser);
            $this->prefillMenu($browser);

            $browser->script('document.querySelector("button[form=\'questForm\']").click();');
            $browser->pause(500);

            $display = $browser->script('
                var el = document.getElementById("ayurveda-validation-alert");
                return el ? el.style.display : "none";
            ')[0];

            $this->assertEquals('none', $display, 'L\'alerte Ayurveda doit être masquée quand tout est rempli');
        });
    }

    public function test_section_s4_souvre_automatiquement_si_ayurveda_incomplet(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire");

            $isShownBefore = $browser->script('return document.getElementById("s4").classList.contains("show");')[0];
            $this->assertFalse($isShownBefore, 'S4 doit être fermée avant la soumission');

            $this->prefillAliments($browser);
            $this->prefillMenu($browser);

            $browser->script('document.querySelector("button[form=\'questForm\']").click();');

            $browser->waitFor('#s4.show', 5);

            $isShownAfter = $browser->script('return document.getElementById("s4").classList.contains("show");')[0];
            $this->assertTrue($isShownAfter, 'S4 doit s\'ouvrir automatiquement si Ayurveda est incomplet');
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Bug 3 — Validation aliments
    // ═══════════════════════════════════════════════════════════════════════════

    public function test_alerte_si_moins_de_10_aliments_a_la_soumission_principale(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('textarea[name="aliments_text"]', 5);

            // 5 aliments seulement (\\n → \n en JS)
            $browser->script('document.querySelector(\'textarea[name="aliments_text"]\').value = "Pommes\\nCarottes\\nSaumon\\nEpinards\\nAvocat";');
            $this->prefillMenu($browser);

            $browser->script('document.querySelector("button[form=\'questForm\']").click();');
            $browser->pause(400);

            $alertText = $browser->script('
                var el = document.getElementById("aliments-validation-alert");
                return el ? el.textContent : "";
            ')[0];

            $this->assertStringContainsString('5 renseigné(s) sur 10', $alertText);
        });
    }

    public function test_alerte_si_moins_de_10_aliments_sur_formulaire_dedie(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('textarea[name="aliments_text"]', 5);

            // 3 aliments seulement
            $browser->script('document.querySelector(\'textarea[name="aliments_text"]\').value = "Pomme\\nCarotte\\nSaumon";');

            // Soumettre le formulaire dédié aliments
            $browser->script('
                var form = document.querySelector(\'textarea[name="aliments_text"]\').closest("form");
                form.querySelector("button[type=\'submit\']").click();
            ');
            $browser->pause(400);

            $alertText = $browser->script('
                var el = document.getElementById("aliments-validation-alert");
                return el ? el.textContent : "";
            ')[0];

            $this->assertStringContainsString('3 renseigné(s) sur 10', $alertText);
        });
    }

    public function test_pas_d_alerte_aliments_si_10_lignes_ou_plus(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('textarea[name="aliments_text"]', 5);

            // Exactement 10 aliments
            $browser->script('document.querySelector(\'textarea[name="aliments_text"]\').value = "A\\nB\\nC\\nD\\nE\\nF\\nG\\nH\\nI\\nJ";');

            $browser->script('
                var form = document.querySelector(\'textarea[name="aliments_text"]\').closest("form");
                form.querySelector("button[type=\'submit\']").click();
            ');
            $browser->pause(400);

            $display = $browser->script('
                var el = document.getElementById("aliments-validation-alert");
                return el ? el.style.display : "none";
            ')[0];

            $this->assertEquals('none', $display, 'Pas d\'alerte si 10 aliments sont renseignés');
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Bug 3 — Validation menu
    // ═══════════════════════════════════════════════════════════════════════════

    public function test_alerte_si_menu_vide_a_la_soumission_principale(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('#menuForm', 5);

            $this->prefillAliments($browser);

            // Menu explicitement vide
            $browser->script('var mn = document.querySelector(\'#menuForm textarea[name="menu_text"]\'); if (mn) mn.value = "";');

            $browser->script('document.querySelector("button[form=\'questForm\']").click();');
            $browser->pause(400);

            $alertText = $browser->script('
                var el = document.getElementById("menu-validation-alert");
                return el ? el.textContent : "";
            ')[0];

            $this->assertStringContainsString('Veuillez décrire le menu', $alertText);
        });
    }

    public function test_pas_d_alerte_menu_si_contenu_present(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('#menuForm', 5);

            $this->prefillAliments($browser);
            $this->prefillMenu($browser);

            $browser->script('document.querySelector("button[form=\'questForm\']").click();');
            $browser->pause(400);

            $display = $browser->script('
                var el = document.getElementById("menu-validation-alert");
                return el ? el.style.display : "none";
            ')[0];

            $this->assertEquals('none', $display, 'Pas d\'alerte menu si le contenu est renseigné');
        });
    }
}
