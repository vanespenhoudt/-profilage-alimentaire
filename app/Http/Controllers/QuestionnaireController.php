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
    // -----------------------------------------------------------------------
    // Affichage du formulaire (session active)
    // -----------------------------------------------------------------------

    public function show(Request $request, Client $client): View
    {
        $this->authorizeClientAccess($request->user(), $client);

        $questionnaire = $client->questionnaire;
        $answers       = \App\Services\QuestionnaireScorer::normalizeMetaboliqueAnswers(
            $questionnaire?->answers ?? []
        );

        return view('questionnaire.show', compact('client', 'questionnaire', 'answers'));
    }

    // -----------------------------------------------------------------------
    // Autosave AJAX (merge partiel)
    // -----------------------------------------------------------------------

    public function autosave(Request $request, Client $client): JsonResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $incoming = $request->except(['_token', 'menu_text', 'aliments_text']);

        $questionnaire = $client->questionnaire
            ?? Questionnaire::create([
                'client_id' => $client->id,
                'is_active' => true,
                'answers'   => [],
            ]);

        $merged                    = Questionnaire::mergeAnswers($questionnaire->answers ?? [], $incoming);
        $questionnaire->answers    = $merged;
        $questionnaire->scores     = (new QuestionnaireScorer())->calculate($merged);
        $questionnaire->updated_at = now();

        if ($request->has('menu_text')) {
            $questionnaire->menu_text = $request->input('menu_text') ?: null;
        }
        if ($request->has('aliments_text')) {
            $questionnaire->aliments_text = $request->input('aliments_text') ?: null;
        }

        $questionnaire->save();

        $this->syncIdentityToClient($client, $incoming);

        return response()->json([
            'saved' => true,
            'time'  => now()->format('H:i:s'),
        ]);
    }

    // -----------------------------------------------------------------------
    // Soumission finale (merge partiel + recalcul scores)
    // -----------------------------------------------------------------------

    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $incoming = $request->except(['_token']);

        $questionnaire = $client->questionnaire
            ?? Questionnaire::create([
                'client_id' => $client->id,
                'is_active' => true,
                'answers'   => [],
            ]);

        $merged = Questionnaire::mergeAnswers($questionnaire->answers ?? [], $incoming);
        $scores = (new QuestionnaireScorer())->calculate($merged);

        $questionnaire->answers    = $merged;
        $questionnaire->scores     = $scores;
        $questionnaire->updated_at = now();
        $questionnaire->save();

        $this->syncIdentityToClient($client, $incoming);

        return redirect()
            ->route('questionnaire.bilan', $client)
            ->with('success', 'Questionnaire enregistré avec succès.');
    }

    // -----------------------------------------------------------------------
    // Bilan (session active)
    // -----------------------------------------------------------------------

    public function bilan(Request $request, Client $client): View|RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $questionnaire = $client->questionnaire;

        if (! $questionnaire) {
            return redirect()
                ->route('questionnaire.show', $client)
                ->with('error', 'Aucun questionnaire enregistré pour ce client.');
        }

        if (!empty($questionnaire->answers)) {
            $questionnaire->scores = (new QuestionnaireScorer())->calculate($questionnaire->answers);
            $questionnaire->saveQuietly();
        }

        $allSessions = $client->questionnaires()->get(['id', 'session_label', 'updated_at', 'is_active']);
        $data        = QuestionnaireData::class;

        return view('questionnaire.bilan', compact('client', 'questionnaire', 'allSessions', 'data'));
    }

    // -----------------------------------------------------------------------
    // Nouvelle session
    // -----------------------------------------------------------------------

    public function nouvelleSession(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $request->validate([
            'session_label' => 'nullable|string|max:100',
        ]);

        $previousAnswers = $client->questionnaire?->answers ?? [];

        // Désactiver toutes les sessions existantes
        $client->questionnaires()->update(['is_active' => false]);

        Questionnaire::create([
            'client_id'     => $client->id,
            'session_label' => $request->input('session_label') ?: 'Session du ' . now()->format('d/m/Y'),
            'is_active'     => true,
            'answers'       => $previousAnswers,
            'scores'        => [],
        ]);

        return redirect()
            ->route('questionnaire.show', $client)
            ->with('success', 'Nouvelle session créée. Les réponses précédentes sont conservées et pré-remplies.');
    }

    // -----------------------------------------------------------------------
    // Bilan comparatif
    // -----------------------------------------------------------------------

    public function comparer(Request $request, Client $client): View
    {
        $this->authorizeClientAccess($request->user(), $client);

        $request->validate([
            'session_a' => 'required|integer|exists:questionnaires,id',
            'session_b' => 'required|integer|exists:questionnaires,id',
        ]);

        $sessionA = Questionnaire::where('client_id', $client->id)
            ->findOrFail($request->integer('session_a'));
        $sessionB = Questionnaire::where('client_id', $client->id)
            ->findOrFail($request->integer('session_b'));

        return view('questionnaire.comparer', compact('client', 'sessionA', 'sessionB'));
    }

    // -----------------------------------------------------------------------
    // Notes d'interprétation
    // -----------------------------------------------------------------------

    public function saveNotes(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $questionnaire = $client->questionnaire;
        $questionnaire->interpretation_notes = array_map('trim', $request->input('notes', []));
        $questionnaire->save();

        return back()->with('success', 'Notes d\'interprétation enregistrées.');
    }

    // -----------------------------------------------------------------------
    // Menu
    // -----------------------------------------------------------------------

    public function saveMenu(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $request->validate([
            'menu_text'               => 'nullable|string',
            'menu_file'               => 'nullable|file|mimes:pdf,txt,doc,docx,jpg,jpeg|max:10240',
            'menu_visible_client'     => 'nullable|boolean',
            'aliments_text'           => 'nullable|string|max:2000',
            'aliments_visible_client' => 'nullable|boolean',
        ]);

        $questionnaire = $client->questionnaire
            ?? Questionnaire::create(['client_id' => $client->id, 'is_active' => true]);

        $questionnaire->menu_text               = $request->input('menu_text');
        $questionnaire->menu_visible_client     = $request->boolean('menu_visible_client');
        $questionnaire->aliments_text           = $request->input('aliments_text');
        $questionnaire->aliments_visible_client = $request->boolean('aliments_visible_client');
        $questionnaire->updated_at              = now();

        if ($request->hasFile('menu_file') && $request->file('menu_file')->isValid()) {
            if ($questionnaire->menu_file) {
                Storage::disk('local')->delete($questionnaire->menu_file);
            }
            $file = $request->file('menu_file');
            $questionnaire->menu_file      = $file->store('menus', 'local');
            $questionnaire->menu_file_name = $file->getClientOriginalName();
        }

        $questionnaire->save();

        return redirect()
            ->route('questionnaire.bilan', $client)
            ->with('success', 'Menu enregistré.');
    }

    // -----------------------------------------------------------------------
    // Téléchargement sécurisé du fichier menu
    // -----------------------------------------------------------------------

    public function downloadMenu(Request $request, Client $client): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $questionnaire = $client->questionnaire;

        if (! $questionnaire || ! $questionnaire->menu_file) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($questionnaire->menu_file)) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $questionnaire->menu_file,
            $questionnaire->menu_file_name ?? basename($questionnaire->menu_file)
        );
    }

    // -----------------------------------------------------------------------
    // Aliments
    // -----------------------------------------------------------------------

    public function saveAliments(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $request->validate([
            'aliments_text'           => 'nullable|string|max:2000',
            'aliments_visible_client' => 'nullable|boolean',
        ]);

        $questionnaire = $client->questionnaire
            ?? Questionnaire::create(['client_id' => $client->id, 'is_active' => true]);

        $questionnaire->aliments_text           = $request->input('aliments_text');
        $questionnaire->aliments_visible_client = $request->boolean('aliments_visible_client');
        $questionnaire->updated_at              = now();
        $questionnaire->save();

        return redirect()
            ->route('questionnaire.bilan', $client)
            ->with('success', 'Aliments préférés enregistrés.');
    }

    // -----------------------------------------------------------------------
    // Génération du lien token (session active)
    // -----------------------------------------------------------------------

    public function generateToken(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $validated = $request->validate([
            'sections'                => 'nullable|array|min:1',
            'sections.*'              => 'in:julia_ross,metabolique,diathese,ayurveda,groupe_sanguin,hormones,canaris',
            'menu_visible_client'     => 'nullable|boolean',
            'bilan_visible_client'    => 'nullable|boolean',
            'aliments_visible_client' => 'nullable|boolean',
        ]);

        $questionnaire = $client->questionnaire
            ?? Questionnaire::create(['client_id' => $client->id, 'is_active' => true]);

        $questionnaire->token                   = Str::random(48);
        $questionnaire->sections                = $validated['sections'] ?? null;
        $questionnaire->menu_visible_client     = $request->boolean('menu_visible_client');
        $questionnaire->bilan_visible_client    = $request->boolean('bilan_visible_client');
        $questionnaire->aliments_visible_client = $request->boolean('aliments_visible_client');
        $questionnaire->submitted_at            = null;
        $questionnaire->updated_at              = now();
        $questionnaire->save();

        return redirect()
            ->route('clients.show', $client)
            ->with('token_generated', route('questionnaire.public.show', $questionnaire->token));
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

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

    private function authorizeClientAccess(\App\Models\User $user, Client $client): void
    {
        if ($user->role !== Role::SuperAdmin && $client->conseiller_id !== $user->id) {
            abort(403, "Vous n'êtes pas autorisé à accéder à ce client.");
        }
    }
}
