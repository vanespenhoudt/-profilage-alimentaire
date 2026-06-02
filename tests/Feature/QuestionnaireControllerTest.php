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

    public function test_super_admin_cannot_access_questionnaire(): void
    {
        $superAdmin = $this->makeSuperAdmin();
        $client     = $this->makeClientFor($this->makeConseiller());

        $this->actingAs($superAdmin)
            ->get(route('questionnaire.show', $client))
            ->assertForbidden();
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
    // autosave — merge partiel
    // -----------------------------------------------------------------------

    public function test_autosave_merges_partial_answers_without_erasing_other_sections(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        Questionnaire::create([
            'client_id' => $client->id,
            'is_active' => true,
            'answers'   => ['mb1' => 'a', 'jr3_0' => '1'],
        ]);

        // Autosave n'envoie que la section métabolique
        $this->actingAs($conseiller)
            ->post(route('questionnaire.autosave', $client), ['mb1' => 'b'])
            ->assertOk();

        $q = $client->questionnaire;
        $this->assertSame('b',  $q->answers['mb1']);
        $this->assertSame('1',  $q->answers['jr3_0']); // section julia_ross préservée
    }

    public function test_autosave_removes_unchecked_checkbox_of_same_section(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        Questionnaire::create([
            'client_id' => $client->id,
            'is_active' => true,
            'answers'   => ['mb1' => 'a', 'mb2' => 'b'],
        ]);

        // mb2 absent = décoché dans le POST
        $this->actingAs($conseiller)
            ->post(route('questionnaire.autosave', $client), ['mb1' => 'a'])
            ->assertOk();

        $q = $client->questionnaire;
        $this->assertArrayHasKey('mb1', $q->answers);
        $this->assertArrayNotHasKey('mb2', $q->answers);
    }

    // -----------------------------------------------------------------------
    // Sessions — nouvelle session
    // -----------------------------------------------------------------------

    public function test_nouvelle_session_creates_new_questionnaire_and_deactivates_previous(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        // Session initiale avec réponses
        $this->actingAs($conseiller)
            ->post(route('questionnaire.store', $client), $this->minimalAnswers());

        // Créer une nouvelle session
        $this->actingAs($conseiller)
            ->post(route('questionnaire.nouvelle-session', $client), ['session_label' => 'Suivi 1'])
            ->assertRedirect();

        $this->assertDatabaseCount('questionnaires', 2);

        $sessions = \App\Models\Questionnaire::where('client_id', $client->id)->get();
        $active   = $sessions->where('is_active', true);
        $inactive = $sessions->where('is_active', false);

        $this->assertCount(1, $active);
        $this->assertCount(1, $inactive);
        $this->assertSame('Suivi 1', $active->first()->session_label);
    }

    public function test_nouvelle_session_prefills_answers_from_previous(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        $this->actingAs($conseiller)
            ->post(route('questionnaire.store', $client), $this->minimalAnswers());

        $this->actingAs($conseiller)
            ->post(route('questionnaire.nouvelle-session', $client), [
                'session_label'   => 'Suivi 1',
                'previous_answers' => '1',
            ]);

        $newSession = $client->questionnaire;
        $this->assertNotNull($newSession->answers);
        $this->assertArrayHasKey('mb1', $newSession->answers);
    }

    // -----------------------------------------------------------------------
    // Sessions — comparer
    // -----------------------------------------------------------------------

    public function test_comparer_returns_200_for_two_sessions(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        // Créer deux questionnaires pour ce client
        $q1 = Questionnaire::create(['client_id' => $client->id, 'is_active' => false]);
        $q2 = Questionnaire::create(['client_id' => $client->id, 'is_active' => true]);

        $url = route('questionnaire.comparer', $client) . "?session_a={$q1->id}&session_b={$q2->id}";

        $this->actingAs($conseiller)
            ->get($url)
            ->assertOk();
    }

    public function test_comparer_retourne_404_si_session_appartient_a_autre_client(): void
    {
        $conseiller = $this->makeConseiller();
        $client1    = $this->makeClientFor($conseiller);
        $client2    = $this->makeClientFor($conseiller);

        $q1 = Questionnaire::create(['client_id' => $client1->id, 'is_active' => false]);
        $q2 = Questionnaire::create(['client_id' => $client2->id, 'is_active' => true]);

        $url = route('questionnaire.comparer', $client1) . "?session_a={$q1->id}&session_b={$q2->id}";

        $this->actingAs($conseiller)
            ->get($url)
            ->assertNotFound();
    }

    // -----------------------------------------------------------------------
    // activeQuestionnaire — relation
    // -----------------------------------------------------------------------

    public function test_active_questionnaire_relation_returns_only_active_session(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        $old = Questionnaire::create(['client_id' => $client->id, 'is_active' => false]);
        $new = Questionnaire::create(['client_id' => $client->id, 'is_active' => true]);

        $this->assertSame($new->id, $client->questionnaire->id);
    }

    public function test_active_questionnaire_returns_null_when_all_inactive(): void
    {
        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        Questionnaire::create(['client_id' => $client->id, 'is_active' => false]);

        $this->assertNull($client->questionnaire);
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
