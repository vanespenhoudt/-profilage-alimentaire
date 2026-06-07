<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ClientTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function makeConseiller(): User
    {
        return User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
    }

    // ── 1. Création d'un client avec tous les champs ──────────────────────────

    public function test_conseiller_cree_un_client_avec_tous_les_champs(): void
    {
        $conseiller = $this->makeConseiller();

        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->loginAs($conseiller)
                ->visit('/clients/create')
                ->type('#prenom', 'Jean-Paul')
                ->type('#nom', 'Durand')
                ->type('#tel', '+32 470 11 22 33')
                ->type('#email', 'jeanpaul.durand@exemple.com')
                ->type('#adresse', 'Rue de la Loi 1, 1000 Bruxelles')
                ->type('#bt', 'Bilan terrain initial.')
                ->type('#notes', 'Note de suivi interne.')
                ->check('#rgpd')
                ->press('Créer le client')
                ->waitForText('Jean-Paul', 10)
                ->assertSee('Jean-Paul');
        });

        $this->assertDatabaseHas('clients', [
            'conseiller_id' => $conseiller->id,
        ]);
    }

    // ── 2. Données affichées correspondent aux données saisies (déchiffrement) ─

    public function test_donnees_affichees_correspondent_aux_donnees_saisies(): void
    {
        $conseiller = $this->makeConseiller();

        $client = Client::create([
            'conseiller_id' => $conseiller->id,
            'prenom'        => 'Alice',
            'nom'           => 'Martin',
            'tel'           => '+32 475 99 88 77',
            'email'         => 'alice.martin@test.com',
            'adresse'       => 'Avenue de la Couronne 42, 1050 Ixelles',
            'rgpd'          => true,
        ]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}")
                ->assertSee('Alice')
                ->assertSee('Martin')
                ->assertSee('+32 475 99 88 77')
                ->assertSee('alice.martin@test.com')
                ->assertSee('Avenue de la Couronne 42');
        });
    }

    // ── 3. Édition d'un client → modifications sauvegardées ──────────────────

    public function test_conseiller_edite_un_client_et_modifications_sont_sauvegardees(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}/edit")
                ->clear('#prenom')
                ->type('#prenom', 'Prénom Modifié')
                ->clear('#notes')
                ->type('#notes', 'Note mise à jour.')
                ->press('Enregistrer')
                ->waitForText('Prénom Modifié', 10)
                ->assertSee('Prénom Modifié');
        });
    }

    // ── 4. Suppression d'un client → absent de la liste ──────────────────────

    public function test_conseiller_supprime_un_client_qui_disparait_de_la_liste(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = Client::factory()->create([
            'conseiller_id' => $conseiller->id,
            'prenom'        => 'Client',
            'nom'           => 'ASupprimer',
        ]);

        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit('/clients')
                ->assertSee('ASupprimer');

            // Accepter la confirmation JS
            $browser->script('window.confirm = () => true');

            $browser->click("[dusk=\"btn-delete-client-{$client->id}\"]")
                ->waitUntilMissing("[dusk=\"btn-delete-client-{$client->id}\"]", 5)
                ->assertDontSee('ASupprimer');
        });

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    // ── 5. Recherche filtre les résultats correctement ────────────────────────

    public function test_recherche_clients_filtre_les_resultats(): void
    {
        $conseiller = $this->makeConseiller();

        Client::factory()->create([
            'conseiller_id' => $conseiller->id,
            'prenom'        => 'Zénobia',
            'nom'           => 'Unique',
        ]);
        Client::factory()->create([
            'conseiller_id' => $conseiller->id,
            'prenom'        => 'Bernard',
            'nom'           => 'Dupont',
        ]);

        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->loginAs($conseiller)
                ->visit('/clients')
                ->type('.input-search', 'Zénobia')
                ->press('Rechercher')
                ->waitForText('Zénobia', 5)
                ->assertSee('Zénobia')
                ->assertDontSee('Bernard');
        });
    }

    // ── 6. Conseiller ne voit pas les clients d'un autre conseiller ───────────

    public function test_conseiller_ne_voit_pas_les_clients_dun_autre_conseiller(): void
    {
        $conseiller1 = $this->makeConseiller();
        $conseiller2 = $this->makeConseiller();

        Client::factory()->create([
            'conseiller_id' => $conseiller2->id,
            'prenom'        => 'ClientPrive',
            'nom'           => 'Confidentiel',
        ]);

        $this->browse(function (Browser $browser) use ($conseiller1) {
            $browser->loginAs($conseiller1)
                ->visit('/clients')
                ->assertDontSee('ClientPrive');
        });
    }

    // ── 7. Données en base sont chiffrées (valeur brute ≠ valeur affichée) ───

    public function test_donnees_client_sont_chiffrees_en_base(): void
    {
        $conseiller = $this->makeConseiller();

        $client = Client::create([
            'conseiller_id' => $conseiller->id,
            'prenom'        => 'Test',
            'nom'           => 'Chiffrement',
            'email'         => 'chiffre@test.com',
            'tel'           => '+32 400 00 00 00',
            'rgpd'          => true,
        ]);

        // La valeur brute en base doit être différente de la valeur en clair
        $rawEmail = DB::table('clients')->where('id', $client->id)->value('email');

        $this->assertNotEquals(
            'chiffre@test.com',
            $rawEmail,
            'L\'email ne doit pas être stocké en clair en base.'
        );

        // Mais le déchiffrement via le modèle doit retourner la valeur correcte
        $this->assertEquals('chiffre@test.com', Client::find($client->id)->email);

        // Vérifier visuellement que la valeur affichée est correcte
        $this->browse(function (Browser $browser) use ($conseiller, $client) {
            $browser->loginAs($conseiller)
                ->visit("/clients/{$client->id}")
                ->assertSee('chiffre@test.com');
        });
    }
}
