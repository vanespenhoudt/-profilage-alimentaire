<?php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;
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

        $answers = $questionnaire->answers ?? [];

        return view('questionnaire.public', compact('questionnaire', 'token', 'answers'));
    }

    public function save(Request $request, string $token): JsonResponse
    {
        $questionnaire = Questionnaire::where('token', $token)->firstOrFail();

        if ($questionnaire->isSubmitted()) {
            return response()->json(['error' => 'Questionnaire déjà soumis.'], 403);
        }

        $answers = $request->except(['_token']);

        $questionnaire->answers    = $answers;
        $questionnaire->updated_at = now();
        $questionnaire->save();

        return response()->json([
            'saved' => true,
            'time'  => now()->format('H:i:s'),
        ]);
    }

    public function submit(Request $request, string $token): View|RedirectResponse
    {
        $questionnaire = Questionnaire::where('token', $token)->with('client')->firstOrFail();

        if ($questionnaire->isSubmitted()) {
            return view('questionnaire.merci', compact('questionnaire'));
        }

        $answers = $request->except(['_token']);
        $scores  = (new QuestionnaireScorer())->calculate($answers);

        $questionnaire->answers      = $answers;
        $questionnaire->scores       = $scores;
        $questionnaire->submitted_at = now();
        $questionnaire->updated_at   = now();
        $questionnaire->save();

        return view('questionnaire.merci', compact('questionnaire'));
    }
}
