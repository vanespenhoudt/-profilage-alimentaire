<?php

namespace Tests\Unit;

use App\Models\Invitation;
use Carbon\Carbon;
use Tests\TestCase;

class InvitationModelTest extends TestCase
{
    // ── Méthode makeInvitation ────────────────────────────────────────────────
    //
    // On utilise setRawAttributes() pour éviter toute connexion à la base de
    // données : les tests unitaires testent la logique du modèle seule.

    private function makeInvitation(array $attributes = []): Invitation
    {
        $invitation = new Invitation();
        $invitation->setRawAttributes(array_merge([
            'email' => 'test@example.com',
            'token' => str_repeat('a', 48),
        ], $attributes), true);

        return $invitation;
    }

    // ── isUsed() ─────────────────────────────────────────────────────────────

    public function test_is_used_returns_false_when_used_at_is_null(): void
    {
        $invitation = $this->makeInvitation(['used_at' => null]);

        $this->assertFalse($invitation->isUsed());
    }

    public function test_is_used_returns_true_when_used_at_is_set(): void
    {
        $invitation = $this->makeInvitation(['used_at' => Carbon::now()->toDateTimeString()]);

        $this->assertTrue($invitation->isUsed());
    }

    // ── isPending() ───────────────────────────────────────────────────────────

    public function test_is_pending_returns_true_when_used_at_is_null(): void
    {
        $invitation = $this->makeInvitation(['used_at' => null]);

        $this->assertTrue($invitation->isPending());
    }

    public function test_is_pending_returns_false_when_used_at_is_set(): void
    {
        $invitation = $this->makeInvitation(['used_at' => Carbon::now()->toDateTimeString()]);

        $this->assertFalse($invitation->isPending());
    }
}
