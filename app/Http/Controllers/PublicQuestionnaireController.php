<?php

namespace App\Http\Controllers;

use App\Actions\Questionnaire\SubmitQuestionnaireAction;
use App\Actions\Questionnaire\ValidateQuestionnaireAction;
use App\Models\Questionnaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicQuestionnaireController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $questionnaire = Questionnaire::where('token', $token)->with('client')->firstOrFail();

        if ($questionnaire->isSubmitted()) {
            return view('questionnaire.merci', compact('questionnaire'));
        }

        $answers  = $questionnaire->answers ?? [];
        $allSects = ['julia_ross', 'metabolique', 'diathese', 'ayurveda', 'groupe_sanguin', 'hormones'];
        $sections = $questionnaire->sections ?? $allSects;

        return view('questionnaire.public', compact('questionnaire', 'token', 'answers', 'sections'));
    }

    public function save(Request $request, string $token): JsonResponse
    {
        $questionnaire = Questionnaire::where('token', $token)->with('client')->firstOrFail();

        if ($questionnaire->isSubmitted()) {
            return response()->json(['error' => 'Questionnaire déjà soumis.'], 403);
        }

        $answers = $request->except(['_token']);

        $questionnaire->answers    = $answers;
        $questionnaire->updated_at = now();
        $questionnaire->save();

        $this->syncIdentityToClient($questionnaire->client, $answers);

        return response()->json([
            'saved' => true,
            'time'  => now()->format('H:i:s'),
        ]);
    }

    public function validate(string $token): JsonResponse
    {
        $questionnaire = Questionnaire::where('token', $token)->with('client')->firstOrFail();

        if ($questionnaire->isSubmitted()) {
            return response()->json(['suspectes' => []]);
        }

        $suspectes = (new ValidateQuestionnaireAction())->execute($questionnaire);

        return response()->json(['suspectes' => $suspectes]);
    }

    public function submit(Request $request, string $token): View|RedirectResponse
    {
        $questionnaire = Questionnaire::where('token', $token)->with('client')->firstOrFail();

        if ($questionnaire->isSubmitted()) {
            return view('questionnaire.merci', compact('questionnaire'));
        }

        $answers = $request->except(['_token']);

        (new SubmitQuestionnaireAction())->execute($questionnaire, $answers);

        return view('questionnaire.merci', compact('questionnaire'));
    }

    private function syncIdentityToClient(\App\Models\Client $client, array $data): void
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
