<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireControllerTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeConseiller(): User
    {
        return User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => true,
        ]);
    }

    private function makeSuperAdmin(): User
    {
        return User::factory()->create([
            'role'   => Role::SuperAdmin->value,
            'active' => true,
        ]);
    }

    private function makeClientFor(User $conseiller): Client
    {
        return Client::factory()->create(['conseiller_id' => $conseiller->id]);
    }

    private function minimalAnswers(): array
    {
        return ['mb1' => 'a', 'mb2' => 'b'];
    }

    // -----------------------------------------------------------------------
    // Guest redirections
    // -----------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_on_show(): void
    {
        $client = $this->makeClientFor($this->makeConseiller());

        $this->get(route('questionnaire.show', $client))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_on_store(): void
    {
        $client = $this->makeClientFor($this->makeConseiller());

        $this->post(route('questionnaire.store', $client))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_on_bilan(): void
    {
        $client = $this->makeClientFor($this->makeConseiller());

        $this->get(route('questionnaire.bilan', $client))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_on_generate_token(): void
    {
        $client = $this->makeClientFor($this->makeConseiller());

        $this->post(route('questionnaire.generate-token', $client))
            ->assertRedirect(route('login'));
    }

    // -----------------------------------------------------------------------
    // show
    // -----------------------------------------------------------------------

    public function test_conseiller_can_view_questionnaire_of_own_client(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        $this->actingAs($conseiller)
            ->get(route('questionnaire.show', $client))
            ->assertOk();
    }

    public function test_conseiller_cannot_view_questionnaire_of_another_conseiller_client(): void
    {
        $conseiller1 = $this->makeConseiller();
        $conseiller2 = $this->makeConseiller();
        $client      = $this->makeClientFor($conseiller2);

        $this->actingAs($conseiller1)
            ->get(route('questionnaire.show', $client))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_any_questionnaire(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $client     = $this->makeClientFor($this->makeConseiller());

        $this->actingAs($superAdmin)
            ->get(route('questionnaire.show', $client))
            ->assertOk();
    }

    // -----------------------------------------------------------------------
    // store
    // -----------------------------------------------------------------------

    public function test_store_saves_answers_and_scores_then_redirects_to_bilan(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        $this->actingAs($conseiller)
            ->post(route('questionnaire.store', $client), $this->minimalAnswers())
            ->assertRedirect(route('questionnaire.bilan', $client));

        $questionnaire = Questionnaire::where('client_id', $client->id)->first();
        $this->assertNotNull($questionnaire);
        $this->assertNotNull($questionnaire->answers);
        $this->assertNotNull($questionnaire->scores);
    }

    public function test_store_upsert_updates_existing_questionnaire(): void
    {
        $conseiller    = $this->makeConseiller();
        $client        = $this->makeClientFor($conseiller);
        $questionnaire = Questionnaire::create([
            'client_id' => $client->id,
            'answers'   => ['mb1' => 'a'],
        ]);

        $this->actingAs($conseiller)
            ->post(route('questionnaire.store', $client), ['mb1' => 'b']);

        $this->assertDatabaseCount('questionnaires', 1);

        $questionnaire->refresh();
        $this->assertSame('b', $questionnaire->answers['mb1']);
    }

    // -----------------------------------------------------------------------
    // generateToken
    // -----------------------------------------------------------------------

    public function test_generate_token_creates_unique_token_and_redirects_to_clients_show(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        $this->actingAs($conseiller)
            ->post(route('questionnaire.generate-token', $client))
            ->assertRedirect(route('clients.show', $client));

        $questionnaire = Questionnaire::where('client_id', $client->id)->first();
        $this->assertNotNull($questionnaire->token);
        $this->assertSame(48, strlen($questionnaire->token));
    }

    public function test_generate_token_regenerates_token_if_one_already_existed(): void
    {
        $conseiller    = $this->makeConseiller();
        $client        = $this->makeClientFor($conseiller);
        $questionnaire = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => 'old-token-value',
        ]);

        $this->actingAs($conseiller)
            ->post(route('questionnaire.generate-token', $client));

        $questionnaire->refresh();
        $this->assertNotSame('old-token-value', $questionnaire->token);
    }

    // -----------------------------------------------------------------------
    // bilan
    // -----------------------------------------------------------------------

    public function test_bilan_returns_200_when_questionnaire_exists(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        // On passe par store pour créer le questionnaire avec des scores complets
        // et un updated_at correctement rempli (comme le fait le contrôleur)
        $this->actingAs($conseiller)
            ->post(route('questionnaire.store', $client), $this->minimalAnswers());

        $this->actingAs($conseiller)
            ->get(route('questionnaire.bilan', $client))
            ->assertOk();
    }

    public function test_bilan_redirects_to_show_when_no_questionnaire_exists(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        $this->actingAs($conseiller)
            ->get(route('questionnaire.bilan', $client))
            ->assertRedirect(route('questionnaire.show', $client));
    }

    // -----------------------------------------------------------------------
    // Rôle client — accès interdit aux routes conseiller
    // -----------------------------------------------------------------------

    public function test_user_avec_role_client_obtient_403_sur_routes_conseiller(): void
    {
        $userClient = User::factory()->create([
            'role'   => Role::Client->value,
            'active' => true,
        ]);
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        $this->actingAs($userClient)
            ->get(route('dashboard'))
            ->assertForbidden();

        $this->actingAs($userClient)
            ->get(route('clients.index'))
            ->assertForbidden();

        $this->actingAs($userClient)
            ->get(route('questionnaire.show', $client))
            ->assertForbidden();
    }
}
