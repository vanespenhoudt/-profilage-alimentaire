<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthTest extends DuskTestCase
{
    use DatabaseMigrations;

    // ── 1. Visiteur non authentifié redirigé vers /login ─────────────────────

    public function test_visiteur_non_authentifie_est_redirige_vers_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->assertPathIs('/login');
        });
    }

    // ── 2. Connexion valide → dashboard ──────────────────────────────────────

    public function test_connexion_avec_identifiants_valides_redirige_vers_dashboard(): void
    {
        $user = User::factory()->create([
            'role'     => Role::Conseiller->value,
            'active'   => true,
            'password' => bcrypt('Password1234!'),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'Password1234!')
                ->click('.auth-btn')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');
        });
    }

    // ── 3. Mauvais mot de passe → message d'erreur ───────────────────────────

    public function test_mauvais_mot_de_passe_affiche_message_erreur(): void
    {
        $user = User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'mauvais_mdp_xxxx')
                ->click('.auth-btn')
                ->assertSee('Ces identifiants ne correspondent pas');
        });
    }

    // ── 4. Déconnexion → /login, session détruite ────────────────────────────

    public function test_deconnexion_redirige_vers_login_et_detruit_session(): void
    {
        $user = User::factory()->create(['role' => Role::Conseiller->value]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->click('.btn-topbar-logout')
                ->waitForLocation('/login')
                ->assertPathIs('/login');

            // Vérifier que la session est bien détruite
            $browser->visit('/dashboard')
                ->assertPathIs('/login');
        });
    }

    // ── 5. /inscription sans token valide → erreur ───────────────────────────

    public function test_acces_inscription_avec_token_bidon_affiche_erreur(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/inscription/token-bidon-inexistant-xxxxx')
                ->assertSee('invalide');
        });
    }

    // ── 6. Token aléatoire inconnu → message token invalide ──────────────────

    public function test_token_invitation_inexistant_affiche_erreur(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/inscription/' . Str::random(64))
                ->assertSee('invalide');
        });
    }

    // ── 7. Inscription complète via token valide ──────────────────────────────

    public function test_inscription_via_token_valide_cree_compte_et_redirige_vers_dashboard(): void
    {
        $admin = User::factory()->create(['role' => Role::SuperAdmin->value]);

        $invitation = Invitation::create([
            'email'      => 'nouveau@profilage.test',
            'token'      => Str::uuid()->toString(),
            'invited_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit("/inscription/{$invitation->token}")
                ->assertSee('Créez votre compte conseiller')
                ->type('name', 'Nouveau Conseiller')
                ->type('password', 'Password1234!')
                ->type('password_confirmation', 'Password1234!')
                ->press('Créer mon compte')
                ->waitForLocation('/dashboard', 10)
                ->assertPathIs('/dashboard');
        });

        $this->assertDatabaseHas('users', [
            'email' => 'nouveau@profilage.test',
            'role'  => Role::Conseiller->value,
        ]);

        $this->assertNotNull(
            Invitation::where('token', $invitation->token)->value('used_at')
        );
    }
}
