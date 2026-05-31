<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ConseillerAdminTest extends DuskTestCase
{
    use DatabaseMigrations;

    // ── 1. Inscription via lien d'invitation ─────────────────────────────────

    public function test_nouveau_conseiller_sinscrire_via_invitation(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin->value]);

        $invitation = Invitation::create([
            'email'      => 'nouveau@conseiller.com',
            'token'      => Str::uuid()->toString(),
            'invited_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit("/inscription/{$invitation->token}")
                ->assertSee('Créez votre compte conseiller')
                ->type('name', 'Sophie Martin')
                ->type('password', 'Password1234!')
                ->type('password_confirmation', 'Password1234!')
                ->press('Créer mon compte')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('Bienvenue');
        });

        $this->assertDatabaseHas('users', [
            'email' => 'nouveau@conseiller.com',
            'role'  => Role::Conseiller->value,
        ]);

        $this->assertNotNull(Invitation::where('token', $invitation->token)->value('used_at'));
    }

    public function test_lien_invitation_invalide_redirige_vers_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/inscription/token-bidon-inexistant')
                ->assertSee('invalide');
        });
    }

    public function test_lien_invitation_expiree_affiche_erreur(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin->value]);

        $invitation = Invitation::create([
            'email'      => 'expire@conseiller.com',
            'token'      => Str::uuid()->toString(),
            'invited_by' => $admin->id,
            'expires_at' => now()->subDay(),
        ]);

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit("/inscription/{$invitation->token}")
                ->assertSee('expiré');
        });
    }

    // ── 2. Login conseiller ──────────────────────────────────────────────────

    public function test_conseiller_peut_se_connecter_et_voir_son_dashboard(): void
    {
        $conseiller = User::factory()->create([
            'email'    => 'conseiller@test.com',
            'password' => bcrypt('Password1234!'),
            'role'     => Role::Conseiller->value,
            'active'   => true,
        ]);

        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->visit('/login')
                ->type('email', $conseiller->email)
                ->type('password', 'Password1234!')
                ->click('.auth-btn')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertSee('Tableau de bord');
        });
    }

    public function test_conseiller_inactif_ne_peut_pas_acceder_au_dashboard(): void
    {
        $conseiller = User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => false,
        ]);

        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->loginAs($conseiller)
                ->visit('/dashboard')
                ->waitForLocation('/login')
                ->assertPathIs('/login')
                ->assertSee('désactivé');
        });
    }

    // ── 3. Admin désactive / supprime un conseiller ──────────────────────────

    public function test_admin_peut_desactiver_un_conseiller(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin->value]);
        $conseiller = User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $conseiller) {
            $browser->loginAs($admin)
                ->visit('/admin/conseillers')
                ->assertSee($conseiller->name)
                ->press('.btn-outline-warning')
                ->waitForText('désactivé')
                ->assertSee('désactivé');
        });

        $this->assertDatabaseHas('users', [
            'id'     => $conseiller->id,
            'active' => false,
        ]);
    }

    public function test_admin_peut_supprimer_une_invitation_en_attente(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin->value]);

        $invitation = Invitation::create([
            'email'      => 'asupprimer@conseiller.com',
            'token'      => Str::uuid()->toString(),
            'invited_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->browse(function (Browser $browser) use ($admin, $invitation) {
            $browser->loginAs($admin)
                ->visit('/admin/conseillers')
                ->assertSee($invitation->email);

            $browser->script('window.confirm = () => true');

            $browser->click('.btn-danger-outline')
                ->waitForText('supprimée', 5)
                ->assertDontSee($invitation->email);
        });

        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }
}
