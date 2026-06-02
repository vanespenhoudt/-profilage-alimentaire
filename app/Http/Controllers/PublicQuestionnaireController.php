<?php

namespace App\Http\Controllers;

use App\Actions\Questionnaire\SubmitQuestionnaireAction;
use App\Actions\Questionnaire\ValidateQuestionnaireAction;
use App\Data\QuestionnaireData;
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
        $allSects = ['julia_ross', 'metabolique', 'diathese', 'ayurveda', 'groupe_sanguin', 'hormones', 'canaris'];
        $sections = $questionnaire->sections ?? $allSects;

        return view('questionnaire.public', compact('questionnaire', 'token', 'answers', 'sections'));
    }

    public function save(Request $request, string $token): JsonResponse
    {
        $questionnaire = Questionnaire::where('token', $token)->with('client')->firstOrFail();

        if ($questionnaire->isSubmitted()) {
            return response()->json(['error' => 'Questionnaire déjà soumis.'], 403);
        }

        $incoming = $request->except(['_token', 'menu_text', 'aliments_text']);

        if ($request->has('menu_text')) {
            $questionnaire->menu_text = $request->input('menu_text') ?: null;
        }
        if ($request->has('aliments_text')) {
            $questionnaire->aliments_text = $request->input('aliments_text') ?: null;
        }

        $questionnaire->answers    = Questionnaire::mergeAnswers($questionnaire->answers ?? [], $incoming);
        $questionnaire->updated_at = now();
        $questionnaire->save();

        $this->syncIdentityToClient($questionnaire->client, $incoming);

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

        $answers = $request->except(['_token', 'menu_file']);

        if ($request->hasFile('menu_file')) {
            $file = $request->file('menu_file');
            $path = $file->store('menus', 'public');
            $questionnaire->menu_file      = $path;
            $questionnaire->menu_file_name = $file->getClientOriginalName();
        }

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
