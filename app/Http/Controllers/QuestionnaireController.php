<?php

namespace App\Http\Controllers;

use App\Data\QuestionnaireData;
use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuestionnaireController extends Controller
{
    public function show(Request $request, Client $client): View
    {
        $this->authorizeClientAccess($request->user(), $client);

        $questionnaire = $client->questionnaire;
        $answers = $questionnaire?->answers ?? [];

        return view('questionnaire.show', compact('client', 'questionnaire', 'answers'));
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $answers = $request->except(['_token']);
        $scores  = (new QuestionnaireScorer())->calculate($answers);

        $questionnaire = Questionnaire::firstOrNew(['client_id' => $client->id]);
        $questionnaire->answers    = $answers;
        $questionnaire->scores     = $scores;
        $questionnaire->updated_at = now();
        $questionnaire->save();

        return redirect()
            ->route('questionnaire.bilan', $client)
            ->with('success', 'Questionnaire enregistré avec succès.');
    }

    public function bilan(Request $request, Client $client): View|RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $questionnaire = $client->questionnaire;

        if (! $questionnaire) {
            return redirect()
                ->route('questionnaire.show', $client)
                ->with('error', 'Aucun questionnaire enregistré pour ce client.');
        }

        $data = QuestionnaireData::class;

        return view('questionnaire.bilan', compact('client', 'questionnaire', 'data'));
    }

    public function generateToken(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $questionnaire = Questionnaire::firstOrNew(['client_id' => $client->id]);

        $questionnaire->token      = Str::random(48);
        $questionnaire->updated_at = now();
        $questionnaire->save();

        return redirect()
            ->route('clients.show', $client)
            ->with('token_generated', route('questionnaire.public.show', $questionnaire->token));
    }

    private function authorizeClientAccess(\App\Models\User $user, Client $client): void
    {
        if ($user->role !== Role::SuperAdmin && $client->conseiller_id !== $user->id) {
            abort(403, "Vous n'êtes pas autorisé à accéder à ce client.");
        }
    }
}
