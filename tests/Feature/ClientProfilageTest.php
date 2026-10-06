<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientProfilageTest extends TestCase
{
    use RefreshDatabase;

    private function makeConseiller(): User
    {
        return User::factory()->create(['role' => Role::Conseiller->value, 'active' => true]);
    }

    public function test_conseiller_can_mark_profilage_as_done(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id]);

        $this->actingAs($conseiller)
            ->patchJson(route('clients.profilage', $client), ['fait' => true])
            ->assertOk()
            ->assertJson(['fait' => true, 'date' => now()->format('d/m/Y')]);

        $this->assertNotNull($client->fresh()->profilage_fait_at);
    }

    public function test_conseiller_can_unmark_profilage(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = Client::factory()->create(['conseiller_id' => $conseiller->id, 'profilage_fait_at' => now()]);

        $this->actingAs($conseiller)
            ->patchJson(route('clients.profilage', $client), ['fait' => false])
            ->assertOk()
            ->assertJson(['fait' => false]);

        $this->assertNull($client->fresh()->profilage_fait_at);
    }

    public function test_other_conseiller_cannot_toggle_profilage(): void
    {
        $client = Client::factory()->create(['conseiller_id' => $this->makeConseiller()->id]);

        $this->actingAs($this->makeConseiller())
            ->patchJson(route('clients.profilage', $client), ['fait' => true])
            ->assertForbidden();

        $this->assertNull($client->fresh()->profilage_fait_at);
    }

    public function test_index_shows_profilage_checkbox_checked(): void
    {
        $conseiller = $this->makeConseiller();
        Client::factory()->create(['conseiller_id' => $conseiller->id, 'profilage_fait_at' => now()]);

        $this->actingAs($conseiller)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertSee('Profilage fait')
            ->assertSee('Fait le ' . now()->format('d/m/Y'));
    }

    public function test_dashboard_shows_profilage_checkbox(): void
    {
        $conseiller = $this->makeConseiller();
        Client::factory()->create(['conseiller_id' => $conseiller->id, 'profilage_fait_at' => now()]);

        $this->actingAs($conseiller)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Profilage fait')
            ->assertSee('Fait le ' . now()->format('d/m/Y'));
    }
}
