<?php

namespace Tests\Feature;

use App\Actions\Questionnaire\SubmitQuestionnaireAction;
use App\Enums\Role;
use App\Mail\QuestionnaireCompletedClient;
use App\Mail\QuestionnaireCompletedConseiller;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubmitQuestionnaireActionTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeConseiller(string $email = 'conseiller@test.com'): User
    {
        return User::factory()->create([
            'role'   => Role::Conseiller->value,
            'active' => true,
            'email'  => $email,
        ]);
    }

    private function makeClientFor(User $conseiller, ?string $email = 'client@test.com'): Client
    {
        return Client::factory()->create([
            'conseiller_id' => $conseiller->id,
            'email'         => $email,
        ]);
    }

    private function makeQuestionnaire(Client $client, array $answers = []): Questionnaire
    {
        $q = Questionnaire::create([
            'client_id' => $client->id,
            'token'     => 'test-token-' . uniqid(),
            'answers'   => $answers,
        ]);
        // charger la relation client pour que l'action puisse y accéder
        return $q->load('client.conseiller');
    }

    private function minimalAnswers(): array
    {
        return [
            'mb1'   => 'a',
            'v0'    => '3',
            'jr1_0' => '1',
            'd1a'   => 'd1',
            'h1_0'  => '1',
        ];
    }

    // -----------------------------------------------------------------------
    // Persistance
    // -----------------------------------------------------------------------

    public function test_definit_submitted_at_et_sauvegarde_les_scores(): void
    {
        Mail::fake();

        $conseiller    = $this->makeConseiller();
        $client        = $this->makeClientFor($conseiller);
        $questionnaire = $this->makeQuestionnaire($client);

        (new SubmitQuestionnaireAction())->execute($questionnaire, $this->minimalAnswers());

        $questionnaire->refresh();

        $this->assertNotNull($questionnaire->submitted_at);
        $this->assertNotNull($questionnaire->scores);
        $this->assertArrayHasKey('metabolique', $questionnaire->scores);
        $this->assertArrayHasKey('ayurveda', $questionnaire->scores);
        $this->assertArrayHasKey('julia_ross', $questionnaire->scores);
        $this->assertArrayHasKey('diathese', $questionnaire->scores);
        $this->assertArrayHasKey('hormones', $questionnaire->scores);
    }

    public function test_sauvegarde_les_reponses_fournies(): void
    {
        Mail::fake();

        $conseiller    = $this->makeConseiller();
        $client        = $this->makeClientFor($conseiller);
        $questionnaire = $this->makeQuestionnaire($client);
        $answers       = ['mb1' => 'b', 'mb2' => 'a'];

        (new SubmitQuestionnaireAction())->execute($questionnaire, $answers);

        $questionnaire->refresh();
        $this->assertSame('b', $questionnaire->answers['mb1']);
        $this->assertSame('a', $questionnaire->answers['mb2']);
    }

    public function test_submit_preserve_sections_non_soumises(): void
    {
        Mail::fake();

        $conseiller = $this->makeConseiller();
        $client     = $this->makeClientFor($conseiller);

        // Questionnaire avec réponses Julia Ross déjà sauvegardées
        $questionnaire = $this->makeQuestionnaire($client, [
            'jr1_0' => '1',
            'jr3_3' => '1',
        ]);

        // Submit ne contient que la section métabolique (pas Julia Ross)
        (new SubmitQuestionnaireAction())->execute($questionnaire, [
            'mb1' => 'a',
            'mb2' => 'b',
        ]);

        $questionnaire->refresh();
        $this->assertSame('a',  $questionnaire->answers['mb1']);
        $this->assertSame('1',  $questionnaire->answers['jr1_0']); // préservé
        $this->assertSame('1',  $questionnaire->answers['jr3_3']); // préservé
    }

    // -----------------------------------------------------------------------
    // Synchronisation identité client
    // -----------------------------------------------------------------------

    public function test_synchronise_les_champs_identite_vers_le_client(): void
    {
        Mail::fake();

        $conseiller    = $this->makeConseiller();
        $client        = $this->makeClientFor($conseiller);
        $questionnaire = $this->makeQuestionnaire($client);

        (new SubmitQuestionnaireAction())->execute($questionnaire, [
            'identite_prenom' => 'Alice',
            'identite_nom'    => 'Martin',
            'identite_age'    => '32',
        ]);

        $client->refresh();
        $this->assertSame('Alice', $client->prenom);
        $this->assertSame('Martin', $client->nom);
        $this->assertEquals('32', $client->age); // entier en DB, comparaison souple
    }

    // -----------------------------------------------------------------------
    // Emails
    // -----------------------------------------------------------------------

    public function test_envoie_email_au_conseiller_apres_soumission(): void
    {
        Mail::fake();

        $conseiller    = $this->makeConseiller('conseiller@example.com');
        $client        = $this->makeClientFor($conseiller);
        $questionnaire = $this->makeQuestionnaire($client);

        (new SubmitQuestionnaireAction())->execute($questionnaire, $this->minimalAnswers());

        Mail::assertQueued(QuestionnaireCompletedConseiller::class, function ($mail) {
            return $mail->hasTo('conseiller@example.com');
        });
    }

    public function test_envoie_email_au_client_quand_il_a_un_email(): void
    {
        Mail::fake();

        $conseiller    = $this->makeConseiller();
        $client        = $this->makeClientFor($conseiller, 'client@example.com');
        $questionnaire = $this->makeQuestionnaire($client);

        (new SubmitQuestionnaireAction())->execute($questionnaire, $this->minimalAnswers());

        Mail::assertQueued(QuestionnaireCompletedClient::class, function ($mail) {
            return $mail->hasTo('client@example.com');
        });
    }

    public function test_nenvoie_pas_email_client_quand_pas_demail(): void
    {
        Mail::fake();

        $conseiller    = $this->makeConseiller();
        $client        = $this->makeClientFor($conseiller, null);
        $questionnaire = $this->makeQuestionnaire($client);

        (new SubmitQuestionnaireAction())->execute($questionnaire, $this->minimalAnswers());

        Mail::assertNotQueued(QuestionnaireCompletedClient::class);
    }

    public function test_envoie_quand_meme_email_conseiller_si_client_sans_email(): void
    {
        Mail::fake();

        $conseiller    = $this->makeConseiller('conseiller@example.com');
        $client        = $this->makeClientFor($conseiller, null);
        $questionnaire = $this->makeQuestionnaire($client);

        (new SubmitQuestionnaireAction())->execute($questionnaire, $this->minimalAnswers());

        Mail::assertQueued(QuestionnaireCompletedConseiller::class);
        Mail::assertNotQueued(QuestionnaireCompletedClient::class);
    }

    public function test_nenvoie_pas_email_conseiller_quand_pas_demail(): void
    {
        Mail::fake();

        // Conseiller sans email (edge case)
        $conseiller    = $this->makeConseiller('');
        $client        = $this->makeClientFor($conseiller);
        $questionnaire = $this->makeQuestionnaire($client);

        (new SubmitQuestionnaireAction())->execute($questionnaire, $this->minimalAnswers());

        Mail::assertNotQueued(QuestionnaireCompletedConseiller::class);
    }
}
