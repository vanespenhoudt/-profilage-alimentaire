<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Mail\QuestionnaireCompletedClient;
use App\Mail\QuestionnaireCompletedConseiller;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicQuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeQuestionnaire(array $overrides = []): Questionnaire
    {
        $conseiller = User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => true,
        ]);
        $client = Client::factory()->create(['conseiller_id' => $conseiller->id]);

        // submitted_at et updated_at ne sont pas dans fillable : on les sépare
        // pour les assigner directement après la création.
        $datetimeFields = array_intersect_key($overrides, array_flip(['submitted_at', 'updated_at']));
        $fillableData   = array_diff_key($overrides, $datetimeFields);

        $questionnaire = Questionnaire::create(array_merge([
            'client_id' => $client->id,
            'token'     => 'valid-test-token-' . uniqid(),
        ], $fillableData));

        if ($datetimeFields) {
            foreach ($datetimeFields as $field => $value) {
                $questionnaire->$field = $value;
            }
            $questionnaire->save();
        }

        return $questionnaire;
    }

    // -----------------------------------------------------------------------
    // GET /q/{token} — show
    // -----------------------------------------------------------------------

    public function test_show_returns_200_for_valid_token_not_yet_submitted(): void
    {
        $questionnaire = $this->makeQuestionnaire();

        $this->get(route('questionnaire.public.show', $questionnaire->token))
            ->assertOk();
    }

    public function test_show_returns_404_for_invalid_token(): void
    {
        $this->get(route('questionnaire.public.show', 'token-qui-nexiste-pas'))
            ->assertNotFound();
    }

    public function test_show_displays_merci_view_when_already_submitted(): void
    {
        $questionnaire = $this->makeQuestionnaire([
            'submitted_at' => Carbon::now(),
        ]);

        $this->get(route('questionnaire.public.show', $questionnaire->token))
            ->assertOk()
            ->assertViewIs('questionnaire.merci');
    }

    public function test_show_prefills_answers_if_already_saved(): void
    {
        $savedAnswers  = ['mb1' => 'a', 'mb2' => 'b'];
        $questionnaire = $this->makeQuestionnaire(['answers' => $savedAnswers]);

        $response = $this->get(route('questionnaire.public.show', $questionnaire->token));

        $response->assertOk();
        $response->assertViewHas('answers', $savedAnswers);
    }

    // -----------------------------------------------------------------------
    // POST /q/{token}/save
    // -----------------------------------------------------------------------

    public function test_save_stores_answers_as_json_and_returns_saved_true(): void
    {
        $questionnaire = $this->makeQuestionnaire();
        $answers       = ['mb1' => 'a', 'mb2' => 'b', 'v0' => '3'];

        $response = $this->postJson(
            route('questionnaire.public.save', $questionnaire->token),
            $answers
        );

        $response->assertOk()->assertJson(['saved' => true]);

        $questionnaire->refresh();
        $this->assertSame('a', $questionnaire->answers['mb1']);
        $this->assertSame('b', $questionnaire->answers['mb2']);
    }

    public function test_save_returns_403_when_questionnaire_already_submitted(): void
    {
        $questionnaire = $this->makeQuestionnaire([
            'submitted_at' => Carbon::now(),
        ]);

        $this->postJson(
            route('questionnaire.public.save', $questionnaire->token),
            ['mb1' => 'a']
        )->assertForbidden();
    }

    // -----------------------------------------------------------------------
    // POST /q/{token}/submit
    // -----------------------------------------------------------------------

    public function test_submit_calculates_scores_and_sets_submitted_at(): void
    {
        $questionnaire = $this->makeQuestionnaire();
        $answers       = ['mb1' => 'a', 'mb2' => 'b'];

        $this->post(
            route('questionnaire.public.submit', $questionnaire->token),
            $answers
        )->assertOk();

        $questionnaire->refresh();
        $this->assertNotNull($questionnaire->submitted_at);
        $this->assertNotNull($questionnaire->scores);
        $this->assertArrayHasKey('metabolique', $questionnaire->scores);
        $this->assertArrayHasKey('ayurveda', $questionnaire->scores);
        $this->assertArrayHasKey('julia_ross', $questionnaire->scores);
        $this->assertArrayHasKey('diathese', $questionnaire->scores);
        $this->assertArrayHasKey('hormones', $questionnaire->scores);
    }

    public function test_submit_shows_merci_view_without_recalculating_if_already_submitted(): void
    {
        $originalScores = ['metabolique' => ['a' => 5, 'b' => 10, 'type' => 'Chasseur B']];
        $submittedAt    = Carbon::now()->subHour();

        $questionnaire = $this->makeQuestionnaire([
            'submitted_at' => $submittedAt,
            'scores'       => $originalScores,
        ]);

        $this->post(
            route('questionnaire.public.submit', $questionnaire->token),
            ['mb1' => 'a']
        )->assertOk()->assertViewIs('questionnaire.merci');

        // Les scores ne doivent pas avoir changé
        $questionnaire->refresh();
        $this->assertSame(
            $originalScores['metabolique']['type'],
            $questionnaire->scores['metabolique']['type']
        );
    }

    // -----------------------------------------------------------------------
    // GET /q/{token}/validate
    // -----------------------------------------------------------------------

    public function test_validate_retourne_tableau_vide_quand_toutes_sections_ont_des_reponses(): void
    {
        $questionnaire = $this->makeQuestionnaire([
            'answers' => [
                'mb1'   => 'a',
                'v0'    => '3',
                'jr1_0' => '1',
                'd1a'   => 'd1',
                'h1_0'  => '1',
            ],
        ]);

        $this->getJson(route('questionnaire.public.validate', $questionnaire->token))
            ->assertOk()
            ->assertJson(['suspectes' => []]);
    }

    public function test_validate_retourne_toutes_sections_suspectes_quand_aucune_reponse(): void
    {
        $questionnaire = $this->makeQuestionnaire();

        $response = $this->getJson(route('questionnaire.public.validate', $questionnaire->token));

        $response->assertOk();
        $data = $response->json('suspectes');
        $this->assertCount(5, $data);
        $this->assertContains('Typage Métabolique', $data);
        $this->assertContains('Ayurveda', $data);
        $this->assertContains('Julia Ross', $data);
        $this->assertContains('Diathèse de Ménétrier', $data);
        $this->assertContains('Bilan Hormonal', $data);
    }

    public function test_validate_respecte_le_filtre_sections_du_questionnaire(): void
    {
        $questionnaire = $this->makeQuestionnaire([
            'sections' => ['metabolique', 'ayurveda'],
            'answers'  => ['mb1' => 'a'],
        ]);

        $response = $this->getJson(route('questionnaire.public.validate', $questionnaire->token));

        $response->assertOk();
        $suspectes = $response->json('suspectes');
        // Metabolique OK, Ayurveda vide → 1 suspect
        $this->assertCount(1, $suspectes);
        $this->assertContains('Ayurveda', $suspectes);
        $this->assertNotContains('Julia Ross', $suspectes);
    }

    public function test_validate_retourne_404_pour_token_invalide(): void
    {
        $this->getJson(route('questionnaire.public.validate', 'token-inexistant'))
            ->assertNotFound();
    }

    public function test_validate_retourne_tableau_vide_si_questionnaire_deja_soumis(): void
    {
        $questionnaire = $this->makeQuestionnaire([
            'submitted_at' => Carbon::now(),
        ]);

        $this->getJson(route('questionnaire.public.validate', $questionnaire->token))
            ->assertOk()
            ->assertJson(['suspectes' => []]);
    }

    // -----------------------------------------------------------------------
    // POST /q/{token}/submit — emails
    // -----------------------------------------------------------------------

    public function test_submit_envoie_email_au_conseiller(): void
    {
        Mail::fake();

        $questionnaire = $this->makeQuestionnaire();

        $this->post(
            route('questionnaire.public.submit', $questionnaire->token),
            ['mb1' => 'a']
        )->assertOk();

        Mail::assertQueued(QuestionnaireCompletedConseiller::class);
    }

    public function test_submit_envoie_email_au_client_quand_il_a_un_email(): void
    {
        Mail::fake();

        $questionnaire = $this->makeQuestionnaire();

        // Le helper makeQuestionnaire crée un client avec un email (ClientFactory)
        $this->assertNotNull($questionnaire->client->email);

        $this->post(
            route('questionnaire.public.submit', $questionnaire->token),
            ['mb1' => 'a']
        )->assertOk();

        Mail::assertQueued(QuestionnaireCompletedClient::class);
    }

    public function test_submit_nenvoie_pas_email_client_quand_pas_demail(): void
    {
        Mail::fake();

        $conseiller = User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => true,
        ]);
        $client = Client::factory()->create([
            'conseiller_id' => $conseiller->id,
            'email'         => null,
        ]);
        $questionnaire = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => 'token-no-email-' . uniqid(),
        ]);

        $this->post(
            route('questionnaire.public.submit', $questionnaire->token),
            ['mb1' => 'a']
        )->assertOk();

        Mail::assertNotQueued(QuestionnaireCompletedClient::class);
        Mail::assertQueued(QuestionnaireCompletedConseiller::class);
    }

    public function test_submit_nenvoie_aucun_email_si_deja_soumis(): void
    {
        Mail::fake();

        $questionnaire = $this->makeQuestionnaire([
            'submitted_at' => Carbon::now(),
        ]);

        $this->post(
            route('questionnaire.public.submit', $questionnaire->token),
            ['mb1' => 'a']
        )->assertOk()->assertViewIs('questionnaire.merci');

        Mail::assertNothingQueued();
    }
}
