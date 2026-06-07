<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role'   => Role::SuperAdmin->value,
            'active' => true,
        ]);
    }

    private function makeConseiller(bool $active = true): User
    {
        return User::factory()->create([
            'role'     => Role::Conseiller->value,
            'active'   => $active,
            'password' => bcrypt('Password1234!'),
        ]);
    }

    // ── 1. Super admin voit la liste de tous les conseillers ─────────────────

    public function test_super_admin_voit_tous_les_conseillers(): void
    {
        $admin       = $this->makeAdmin();
        $conseiller1 = $this->makeConseiller();
        $conseiller2 = $this->makeConseiller();

        $this->browse(function (Browser $browser) use ($admin, $conseiller1, $conseiller2) {
            $browser->loginAs($admin)
                ->visit('/admin/conseillers')
                ->assertSee($conseiller1->name)
                ->assertSee($conseiller2->name);
        });
    }

    // ── 2. Super admin désactive un conseiller → connexion impossible ─────────

    public function test_admin_desactive_conseiller_qui_ne_peut_plus_se_connecter(): void
    {
        $admin      = $this->makeAdmin();
        $conseiller = $this->makeConseiller(active: true);

        $this->browse(function (Browser $browser) use ($admin, $conseiller) {
            // Admin désactive le conseiller
            $browser->loginAs($admin)
                ->visit('/admin/conseillers')
                ->assertSee($conseiller->name)
                ->click("[dusk=\"btn-toggle-conseiller-{$conseiller->id}\"]")
                ->waitForText('désactivé', 5);
        });

        $this->assertDatabaseHas('users', ['id' => $conseiller->id, 'active' => false]);

        // Le conseiller tente de se connecter
        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->loginAs($conseiller)
                ->visit('/dashboard')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertSee('désactivé');
        });
    }

    // ── 3. Super admin réactive un conseiller → connexion possible ───────────

    public function test_admin_reactive_conseiller_qui_peut_se_reconnecter(): void
    {
        $admin      = $this->makeAdmin();
        $conseiller = $this->makeConseiller(active: false);

        $this->browse(function (Browser $browser) use ($admin, $conseiller) {
            $browser->loginAs($admin)
                ->visit('/admin/conseillers')
                ->click("[dusk=\"btn-toggle-conseiller-{$conseiller->id}\"]")
                ->waitForText('activé', 5);
        });

        $this->assertDatabaseHas('users', ['id' => $conseiller->id, 'active' => true]);

        // Le conseiller peut maintenant se connecter
        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->visit('/login')
                ->type('email', $conseiller->email)
                ->type('password', 'Password1234!')
                ->click('.auth-btn')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');
        });
    }

    // ── 4. Conseiller modifie son mot de passe depuis son profil ─────────────

    public function test_conseiller_peut_modifier_son_mot_de_passe(): void
    {
        $conseiller = $this->makeConseiller();

        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->loginAs($conseiller)
                ->visit('/profile')
                // Traduit depuis "Update Password" → fr.json
                ->assertSee('Mettre à jour le mot de passe')
                ->type('[dusk="input-current-password"]', 'Password1234!')
                ->type('[dusk="input-new-password"]', 'NouveauMotDePasse2025!')
                ->type('[dusk="input-confirm-password"]', 'NouveauMotDePasse2025!')
                // Traduit depuis "Save" → fr.json
                ->press('Sauvegarder')
                // "Saved." → "Sauvegardé." via fr.json
                ->waitForText('Sauvegardé', 5);
        });
    }

    // ── 5. Conseiller ne peut pas accéder à la liste des conseillers (403) ───

    public function test_conseiller_ne_peut_pas_acceder_liste_conseillers(): void
    {
        $conseiller = $this->makeConseiller();

        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->loginAs($conseiller)
                ->visit('/admin/conseillers')
                // Soit un 403 affiché, soit une redirection
                ->assertDontSee('Envoyer l\'invitation');

            $currentPath = $browser->driver->getCurrentURL();
            $this->assertTrue(
                str_contains($currentPath, '/dashboard') || str_contains($currentPath, '403'),
                "Un conseiller ne devrait pas voir la liste admin. URL: {$currentPath}"
            );
        });
    }

    // ── 6. Conseiller ne peut pas désactiver un autre conseiller (403) ────────

    public function test_conseiller_ne_peut_pas_desactiver_autre_conseiller(): void
    {
        $conseiller1 = $this->makeConseiller();
        $conseiller2 = $this->makeConseiller();

        // Tentative de PATCH direct vers la route toggle
        $this->browse(function (Browser $browser) use ($conseiller1, $conseiller2) {
            $browser->loginAs($conseiller1)
                ->visit("/admin/conseillers/{$conseiller2->id}/toggle");

            // Doit être bloqué (403 ou redirection)
            $url = $browser->driver->getCurrentURL();
            $this->assertStringNotContainsString(
                'admin/conseillers',
                $url,
                'Un conseiller ne devrait pas pouvoir accéder à la route toggle admin.'
            );
        });

        // Le conseiller2 ne doit pas avoir changé de statut
        $this->assertDatabaseHas('users', [
            'id'     => $conseiller2->id,
            'active' => true,
        ]);
    }
}
