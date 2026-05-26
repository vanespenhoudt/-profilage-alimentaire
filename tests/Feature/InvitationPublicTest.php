<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class InvitationPublicTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makePendingInvitation(string $email = 'conseiller@example.com', string $token = null): Invitation
    {
        return Invitation::create([
            'email' => $email,
            'token' => $token ?? str_repeat('a', 48),
        ]);
    }

    private function makeUsedInvitation(string $email = 'used@example.com', string $token = null): Invitation
    {
        $invitation         = $this->makePendingInvitation($email, $token ?? str_repeat('b', 48));
        $invitation->used_at = Carbon::now();
        $invitation->save();

        return $invitation;
    }

    // ── GET /inscription/{token} — show ──────────────────────────────────────

    public function test_show_retourne_200_si_token_valide_et_non_utilise(): void
    {
        $invitation = $this->makePendingInvitation();

        $response = $this->get(route('invitation.show', $invitation->token));

        $response->assertOk();
    }

    public function test_show_retourne_404_si_token_inexistant(): void
    {
        $response = $this->get(route('invitation.show', 'token-qui-nexiste-pas'));

        $response->assertNotFound();
    }

    public function test_show_redirige_vers_login_si_token_deja_utilise(): void
    {
        $invitation = $this->makeUsedInvitation();

        $response = $this->get(route('invitation.show', $invitation->token));

        $response->assertRedirect(route('login'));
    }

    // ── POST /inscription/{token} — register ─────────────────────────────────

    public function test_register_cree_le_compte_avec_nom_et_mot_de_passe_corrects(): void
    {
        $invitation = $this->makePendingInvitation('nouveau@example.com');

        $this->post(route('invitation.register', $invitation->token), [
            'name'                  => 'Jean Dupont',
            'password'              => 'MotDePasse123!',
            'password_confirmation' => 'MotDePasse123!',
        ]);

        $this->assertDatabaseHas('users', [
            'name'  => 'Jean Dupont',
            'email' => 'nouveau@example.com',
        ]);
    }

    public function test_register_connecte_lutilisateur_automatiquement(): void
    {
        $invitation = $this->makePendingInvitation('auto-login@example.com');

        $this->post(route('invitation.register', $invitation->token), [
            'name'                  => 'Marie Martin',
            'password'              => 'MotDePasse123!',
            'password_confirmation' => 'MotDePasse123!',
        ]);

        $this->assertAuthenticated();
    }

    public function test_register_cree_user_avec_role_conseiller_et_active_true(): void
    {
        $invitation = $this->makePendingInvitation('role-check@example.com');

        $this->post(route('invitation.register', $invitation->token), [
            'name'                  => 'Pierre Conseil',
            'password'              => 'MotDePasse123!',
            'password_confirmation' => 'MotDePasse123!',
        ]);

        $user = User::where('email', 'role-check@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame(Role::Conseiller, $user->role);
        $this->assertTrue($user->active);
    }

    public function test_register_marque_invitation_used_at_apres_inscription(): void
    {
        $invitation = $this->makePendingInvitation('mark-used@example.com');

        $this->post(route('invitation.register', $invitation->token), [
            'name'                  => 'Test Used',
            'password'              => 'MotDePasse123!',
            'password_confirmation' => 'MotDePasse123!',
        ]);

        $invitation->refresh();
        $this->assertNotNull($invitation->used_at);
    }

    public function test_register_redirige_vers_login_si_token_deja_utilise(): void
    {
        $invitation = $this->makeUsedInvitation('already-used@example.com');

        $response = $this->post(route('invitation.register', $invitation->token), [
            'name'                  => 'Test',
            'password'              => 'MotDePasse123!',
            'password_confirmation' => 'MotDePasse123!',
        ]);

        $response->assertRedirect(route('login'));
        // Aucun utilisateur créé
        $this->assertDatabaseMissing('users', ['email' => 'already-used@example.com']);
    }

    public function test_register_validation_echoue_si_name_manquant(): void
    {
        $invitation = $this->makePendingInvitation('no-name@example.com');

        $response = $this->post(route('invitation.register', $invitation->token), [
            'name'                  => '',
            'password'              => 'MotDePasse123!',
            'password_confirmation' => 'MotDePasse123!',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('users', ['email' => 'no-name@example.com']);
    }

    public function test_register_validation_echoue_si_password_trop_court(): void
    {
        $invitation = $this->makePendingInvitation('short-pwd@example.com');

        $response = $this->post(route('invitation.register', $invitation->token), [
            'name'                  => 'Test Court',
            'password'              => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'short-pwd@example.com']);
    }

    public function test_register_validation_echoue_si_password_confirmation_ne_correspond_pas(): void
    {
        $invitation = $this->makePendingInvitation('mismatch@example.com');

        $response = $this->post(route('invitation.register', $invitation->token), [
            'name'                  => 'Test Mismatch',
            'password'              => 'MotDePasse123!',
            'password_confirmation' => 'AutreMotDePasse456!',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'mismatch@example.com']);
    }

    public function test_register_redirige_vers_dashboard_apres_succes(): void
    {
        $invitation = $this->makePendingInvitation('dashboard-redirect@example.com');

        $response = $this->post(route('invitation.register', $invitation->token), [
            'name'                  => 'Redirect Test',
            'password'              => 'MotDePasse123!',
            'password_confirmation' => 'MotDePasse123!',
        ]);

        $response->assertRedirect(route('dashboard'));
    }
}
