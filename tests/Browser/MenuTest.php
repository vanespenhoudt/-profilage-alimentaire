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

class MenuTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function makeConseiller(): array
    {
        $conseiller = User::factory()->create(['role' => Role::Conseiller->value]);
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);
        $q          = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => Str::random(48),
        ]);

        return [$conseiller, $client, $q];
    }

    public function test_conseiller_voit_section_menu_dans_questionnaire(): void
    {
        [$conseiller, $client] = $this->makeConseiller();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire")
                ->assertSee('Menu 5 jours')
                ->assertPresent('#menuForm');
        });
    }

    public function test_conseiller_peut_sauvegarder_menu_texte(): void
    {
        [$conseiller, $client] = $this->makeConseiller();

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/questionnaire");

            $browser->script("
                var hidden = document.querySelector('[data-tiptap-hidden]');
                if (hidden) hidden.value = '<p>Menu test Dusk</p>';
            ");

            $browser->within('#menuForm', function (Browser $form) {
                $form->press('Enregistrer le menu');
            })->assertSee('Menu enregistré');
        });
    }

    public function test_menu_sauvegarde_apparait_dans_bilan(): void
    {
        [$conseiller, $client, $q] = $this->makeConseiller();

        $q->update([
            'menu_text'    => '<p>Plan alimentaire semaine 1</p>',
            'answers'      => ['groupe_sanguin' => 'O'],
            'scores'       => [],
            'submitted_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/bilan")
                ->assertSee('Menu 5 jours')
                ->assertSee('Plan alimentaire semaine 1');
        });
    }

    public function test_menu_visible_par_client_sur_page_merci(): void
    {
        [, , $q] = $this->makeConseiller();
        $token    = Str::random(48);

        $q->update([
            'token'               => $token,
            'menu_text'           => '<p>Votre menu personnalisé</p>',
            'menu_visible_client' => true,
            'answers'             => ['groupe_sanguin' => 'O'],
            'scores'              => [],
            'submitted_at'        => now(),
        ]);

        $this->browse(function (Browser $browser) use ($token) {
            $browser->visit("/q/{$token}")
                ->assertSee('Votre plan alimentaire')
                ->assertSee('Votre menu personnalisé');
        });
    }
}
