<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationAdminTest extends TestCase
{
    use RefreshDatabase;

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'role'   => Role::SuperAdmin->value,
            'active' => true,
        ]);
    }

    private function makeConseiller(): User
    {
        return User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => true,
        ]);
    }

    private function makeInvitation(array $overrides = []): Invitation
    {
        return Invitation::create(array_merge([
            'email' => 'invite@example.com',
            'token' => str_repeat('x', 48),
        ], $overrides));
    }

    // ── POST /admin/invitations ───────────────────────────────────────────────

    public function test_guest_est_redirige_vers_login_pour_store(): void
    {
        $response = $this->post(route('admin.invitations.store'), [
            'email' => 'nouveau@example.com',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_conseiller_est_redirige_pour_store_car_pas_super_admin(): void
    {
        // Le middleware CheckRole redirige vers dashboard (pas 403)
        // quand le rôle requis n'est pas satisfait.
        $conseiller = $this->makeConseiller();

        $response = $this->actingAs($conseiller)->post(route('admin.invitations.store'), [
            'email' => 'nouveau@example.com',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_super_admin_peut_creer_une_invitation(): void
    {
        $admin = $this->makeSuperAdmin();
        $email = 'conseiller-nouveau@example.com';

        $response = $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'email' => $email,
        ]);

        $response->assertRedirect(route('admin.conseillers.index'));
        $this->assertDatabaseHas('invitations', ['email' => $email]);

        $invitation = Invitation::where('email', $email)->first();
        $this->assertNotNull($invitation);
        $this->assertNotEmpty($invitation->token);
        $this->assertNull($invitation->used_at);
    }

    public function test_store_echoue_si_email_deja_utilise_par_un_user(): void
    {
        $admin = $this->makeSuperAdmin();
        $existingUser = User::factory()->create([
            'email' => 'existant@example.com',
            'role'  => Role::Conseiller->value,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'email' => 'existant@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('invitations', ['email' => 'existant@example.com']);
    }

    public function test_store_echoue_si_email_deja_en_attente_dinvitation(): void
    {
        $admin = $this->makeSuperAdmin();
        $this->makeInvitation(['email' => 'deja-invite@example.com', 'token' => str_repeat('y', 48)]);

        $response = $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'email' => 'deja-invite@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        // Vérifie qu'une seule invitation existe pour cet email
        $this->assertSame(1, Invitation::where('email', 'deja-invite@example.com')->count());
    }

    // ── DELETE /admin/invitations/{invitation} ────────────────────────────────

    public function test_super_admin_peut_supprimer_une_invitation_en_attente(): void
    {
        $admin      = $this->makeSuperAdmin();
        $invitation = $this->makeInvitation();

        $response = $this->actingAs($admin)->delete(route('admin.invitations.destroy', $invitation));

        $response->assertRedirect(route('admin.conseillers.index'));
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
    }

    public function test_conseiller_ne_peut_pas_supprimer_une_invitation(): void
    {
        $conseiller = $this->makeConseiller();
        $invitation = $this->makeInvitation();

        $response = $this->actingAs($conseiller)->delete(route('admin.invitations.destroy', $invitation));

        // Le middleware CheckRole redirige vers dashboard (pas 403)
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
    }

    public function test_guest_est_redirige_vers_login_pour_destroy(): void
    {
        $invitation = $this->makeInvitation();

        $response = $this->delete(route('admin.invitations.destroy', $invitation));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('invitations', ['id' => $invitation->id]);
    }
}
