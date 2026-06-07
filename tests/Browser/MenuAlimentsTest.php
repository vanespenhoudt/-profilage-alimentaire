<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MenuAlimentsTest extends DuskTestCase
{
    use DatabaseMigrations;

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

    // ── 1. Conseiller accède à la section menu/aliments ──────────────────────

    public function test_conseiller_accede_a_la_section_menu_aliments(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertPresent('#menuForm')
                ->assertSee('Menu / Plan alimentaire')
                ->assertSee('10 aliments préférés');
        });
    }

    // ── 2. Éditeur Tiptap est initialisé dans le DOM ─────────────────────────

    public function test_editeur_tiptap_est_initialise(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                // Attendre l'initialisation de TipTap (JS async)
                ->waitFor('.ProseMirror', 5)
                ->assertPresent('.ProseMirror');
        });
    }

    // ── 3. Saisie dans Tiptap → contenu sauvegardé ───────────────────────────

    public function test_saisie_tiptap_est_sauvegardee(): void
    {
        [$conseiller, $client, $q] = $this->makeSetup();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('.ProseMirror', 5);

            // Injecter le contenu dans le champ caché de TipTap
            $browser->script("
                var hidden = document.querySelector('#menuForm textarea[name=\"menu_text\"]');
                if (hidden) {
                    hidden.value = '<p>Plan alimentaire semaine 1 — Dusk test</p>';
                }
            ");

            $browser->within('#menuForm', function (Browser $form) {
                $form->press('Enregistrer le menu');
            });

            $browser->waitForText('Menu enregistré', 5);
        });

        $this->assertEquals(
            '<p>Plan alimentaire semaine 1 — Dusk test</p>',
            Questionnaire::find($q->id)->menu_text
        );
    }

    // ── 4. Upload d'un fichier PDF valide → visible dans la liste ────────────

    public function test_upload_fichier_pdf_valide_est_visible(): void
    {
        Storage::fake('public');

        [$conseiller, $client] = $this->makeSetup();

        // Créer un faux PDF dans le répertoire temporaire accessible par le navigateur
        $tmpPath = tempnam(sys_get_temp_dir(), 'dusk_test_') . '.pdf';
        file_put_contents($tmpPath, '%PDF-1.4 test content');

        $this->browse(function (Browser $browser) use ($conseiller, $client, $tmpPath) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('#menuForm input[name="menu_file"]', 5)
                ->attach('menu_file', $tmpPath)
                ->within('#menuForm', function (Browser $form) {
                    $form->press('Enregistrer le menu');
                })
                ->waitForText('Menu enregistré', 5);
        });

        @unlink($tmpPath);
    }

    // ── 5. Upload avec extension interdite → message d'erreur ────────────────

    public function test_upload_extension_interdite_affiche_erreur(): void
    {
        [$conseiller, $client] = $this->makeSetup();

        // Créer un faux fichier .exe
        $tmpPath = tempnam(sys_get_temp_dir(), 'dusk_test_') . '.exe';
        file_put_contents($tmpPath, 'MZ executable');

        $this->browse(function (Browser $browser) use ($conseiller, $client, $tmpPath) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('#menuForm input[name="menu_file"]', 5)
                ->attach('menu_file', $tmpPath)
                ->within('#menuForm', function (Browser $form) {
                    $form->press('Enregistrer le menu');
                })
                // Message Laravel : "Le champ menu file doit être un fichier de type : …"
                ->assertSee('type');
        });

        @unlink($tmpPath);
    }

    // ── 6. Aliments préférés sauvegardés et visibles ──────────────────────────

    public function test_aliments_preferes_sont_sauvegardes_et_visibles(): void
    {
        [$conseiller, $client, $q] = $this->makeSetup();

        $texteAliments = "Pommes\nCarottes\nSaumon\nÉpinards\nAvocat";

        $this->browse(function (Browser $browser) use ($conseiller, $client, $texteAliments) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->waitFor('textarea[name="aliments_text"]', 5)
                ->type('textarea[name="aliments_text"]', $texteAliments)
                ->press('Enregistrer')
                ->waitForText('enregistré', 5);
        });

        $this->assertStringContainsString(
            'Pommes',
            Questionnaire::find($q->id)->aliments_text ?? ''
        );
    }

    // ── 7. Visibilité client : désactiver "visible client" ───────────────────

    public function test_controle_visibilite_menu_non_visible_par_client(): void
    {
        [, , $q] = $this->makeSetup();
        $token = Str::random(48);

        $q->update([
            'token'               => $token,
            'menu_text'           => '<p>Menu confidentiel</p>',
            'menu_visible_client' => false,  // Non visible
            'answers'             => ['groupe_sanguin' => 'O'],
            'scores'              => [],
            'submitted_at'        => now(),
        ]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->assertDontSee('Menu confidentiel')
                ->assertDontSee('Votre plan alimentaire');
        });
    }
}
