<?php

namespace App\Actions\Questionnaire;

use App\Mail\QuestionnaireCompletedClient;
use App\Mail\QuestionnaireCompletedConseiller;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;
use Illuminate\Support\Facades\Mail;

final class SubmitQuestionnaireAction
{
    public function execute(Questionnaire $questionnaire, array $answers): void
    {
        $scores = (new QuestionnaireScorer())->calculate($answers);

        $questionnaire->answers      = $answers;
        $questionnaire->scores       = $scores;
        $questionnaire->submitted_at = now();
        $questionnaire->updated_at   = now();
        $questionnaire->save();

        $client = $questionnaire->client;

        $this->syncIdentity($client, $answers);

        $conseiller = $client->conseiller;

        if ($conseiller?->email) {
            Mail::to($conseiller->email)
                ->send(new QuestionnaireCompletedConseiller($conseiller, $client, $questionnaire));
        }

        if ($client->email) {
            Mail::to($client->email)
                ->send(new QuestionnaireCompletedClient($client, $questionnaire));
        }
    }

    private function syncIdentity(Client $client, array $data): void
    {
        foreach (['nom', 'prenom', 'age', 'sexe', 'taille', 'poids'] as $field) {
            if (array_key_exists("identite_{$field}", $data)) {
                $value          = $data["identite_{$field}"];
                $client->$field = ($value !== '' && $value !== null) ? $value : null;
            }
        }
        $client->save();
    }
}
