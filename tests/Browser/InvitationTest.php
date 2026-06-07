<?php

namespace Tests\Browser;

use App\Enums\Role;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class InvitationTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => Role::SuperAdmin->value]);
    }

    private function makeConseiller(): User
    {
        return User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
    }

    // ── 1. Super admin voit la liste des invitations ──────────────────────────

    public function test_super_admin_peut_acceder_a_la_liste_des_invitations(): void
    {
        $admin = $this->makeAdmin();

        Invitation::create([
            'email'      => 'invite@profilage.test',
            'token'      => Str::uuid()->toString(),
            'invited_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/conseillers')
                ->assertSee('invite@profilage.test');
        });
    }

    // ── 2. Super admin envoie une invitation ─────────────────────────────────

    public function test_super_admin_peut_envoyer_une_invitation(): void
    {
        Mail::fake();

        $admin = $this->makeAdmin();

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/conseillers')
                // Ouvrir le modal d'invitation
                ->waitFor('[dusk="btn-open-invite-modal"]')
                ->click('[dusk="btn-open-invite-modal"]')
                ->waitFor('#inviteModal.show', 5)
                ->type('#invite_email', 'nouveau@conseiller.test')
                ->within('#inviteModal', function (Browser $modal) {
                    $modal->press('Envoyer l\'invitation');
                })
                ->waitForText('Invitation envoyée', 5);
        });

        $this->assertDatabaseHas('invitations', [
            'email' => 'nouveau@conseiller.test',
        ]);
    }

    // ── 3. Token expiré → inscription bloquée ────────────────────────────────

    public function test_token_expire_bloque_inscription_avec_message_explicite(): void
    {
        $admin = $this->makeAdmin();

        $invitation = Invitation::create([
            'email'      => 'expire@conseiller.test',
            'token'      => Str::uuid()->toString(),
            'invited_by' => $admin->id,
            'expires_at' => now()->subDays(8), // Expiré il y a 8 jours
        ]);

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit("/inscription/{$invitation->token}")
                ->assertSee('expiré');
        });
    }

    // ── 4. Token déjà utilisé → inscription bloquée ──────────────────────────

    public function test_token_deja_utilise_bloque_inscription(): void
    {
        $admin = $this->makeAdmin();

        $invitation = Invitation::create([
            'email'      => 'utilise@conseiller.test',
            'token'      => Str::uuid()->toString(),
            'invited_by' => $admin->id,
            'expires_at' => now()->addDays(7),
            'used_at'    => now()->subHour(),
        ]);

        $this->browse(function (Browser $browser) use ($invitation) {
            $browser->visit("/inscription/{$invitation->token}")
                ->assertSee('invalide');
        });
    }

    // ── 5. Conseiller ne peut PAS envoyer d'invitation (403) ─────────────────

    public function test_conseiller_ne_peut_pas_envoyer_invitation_admin(): void
    {
        $conseiller = $this->makeConseiller();

        $this->browse(function (Browser $browser) use ($conseiller) {
            $browser->loginAs($conseiller)
                ->visit('/admin/conseillers')
                // Doit être redirigé ou recevoir un 403
                ->assertDontSee('Envoyer l\'invitation');

            // Tentative directe via POST — vérifier le statut HTTP
            // (réalisé via Feature test ; ici on vérifie l'absence de l'UI)
        });
    }
}
