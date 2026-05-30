<?php

namespace App\Http\Controllers;

use App\Data\QuestionnaireData;
use App\Enums\Role;
use App\Models\Client;
use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function autosave(Request $request, Client $client): JsonResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $answers = $request->except(['_token']);

        $questionnaire = Questionnaire::firstOrNew(['client_id' => $client->id]);
        $questionnaire->answers    = $answers;
        $questionnaire->updated_at = now();
        $questionnaire->save();

        $this->syncIdentityToClient($client, $answers);

        return response()->json([
            'saved' => true,
            'time'  => now()->format('H:i:s'),
        ]);
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

        $this->syncIdentityToClient($client, $answers);

        return redirect()
            ->route('questionnaire.bilan', $client)
            ->with('success', 'Questionnaire enregistré avec succès.');
    }

    private function syncIdentityToClient(Client $client, array $data): void
    {
        foreach (['nom', 'prenom', 'age', 'sexe', 'taille', 'poids', 'sentinelles'] as $field) {
            if (array_key_exists("identite_{$field}", $data)) {
                $value = $data["identite_{$field}"];
                $client->$field = ($value !== '' && $value !== null) ? $value : null;
            }
        }
        $client->save();
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

    public function saveMenu(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $request->validate([
            'menu_text'            => 'nullable|string',
            'menu_file'            => 'nullable|file|mimes:pdf,txt,doc,docx,jpg,jpeg|max:10240',
            'menu_visible_client'  => 'nullable|boolean',
        ]);

        $questionnaire = Questionnaire::firstOrNew(['client_id' => $client->id]);
        $questionnaire->menu_text           = $request->input('menu_text');
        $questionnaire->menu_visible_client = $request->boolean('menu_visible_client');
        $questionnaire->updated_at          = now();

        if ($request->hasFile('menu_file') && $request->file('menu_file')->isValid()) {
            if ($questionnaire->menu_file) {
                Storage::disk('public')->delete($questionnaire->menu_file);
            }
            $file = $request->file('menu_file');
            $questionnaire->menu_file      = $file->store('menus', 'public');
            $questionnaire->menu_file_name = $file->getClientOriginalName();
        }

        $questionnaire->save();

        return redirect()
            ->route('questionnaire.bilan', $client)
            ->with('success', 'Menu enregistré.');
    }

    public function generateToken(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $validated = $request->validate([
            'sections'            => 'nullable|array|min:1',
            'sections.*'          => 'in:julia_ross,metabolique,diathese,ayurveda,groupe_sanguin,hormones',
            'menu_visible_client' => 'nullable|boolean',
        ]);

        $questionnaire = Questionnaire::firstOrNew(['client_id' => $client->id]);
        $questionnaire->token               = Str::random(48);
        $questionnaire->sections            = $validated['sections'] ?? null;
        $questionnaire->menu_visible_client = $request->boolean('menu_visible_client');
        $questionnaire->submitted_at        = null;
        $questionnaire->updated_at          = now();
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
