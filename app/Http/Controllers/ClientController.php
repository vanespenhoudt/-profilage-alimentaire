<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $user   = $request->user();
        $search = $request->input('search');

        $query = $user->role === Role::SuperAdmin
            ? Client::with('conseiller')
            : $user->clients();

        $allClients = $query->orderBy('created_at', 'desc')->get();

        if ($search) {
            $lower = mb_strtolower($search);
            $allClients = $allClients->filter(function (Client $c) use ($lower): bool {
                return str_contains(mb_strtolower((string) $c->prenom), $lower)
                    || str_contains(mb_strtolower((string) $c->nom), $lower)
                    || str_contains(mb_strtolower((string) $c->code), $lower);
            })->values();
        }

        $page    = $request->input('page', 1);
        $perPage = 15;
        $clients = new \Illuminate\Pagination\LengthAwarePaginator(
            $allClients->forPage($page, $perPage),
            $allClients->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $isSuperAdmin = $user->role === Role::SuperAdmin;

        return view('clients.index', compact('clients', 'search', 'isSuperAdmin'));
    }

    public function create(Request $request): View
    {
        $conseillers = [];
        $isSuperAdmin = $request->user()->role === Role::SuperAdmin;

        if ($isSuperAdmin) {
            $conseillers = User::conseillers()->where('active', true)->orderBy('name')->get();
        }

        return view('clients.create', compact('conseillers', 'isSuperAdmin'));
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($user->role === Role::SuperAdmin && !empty($data['conseiller_id'])) {
            $conseillerId = $data['conseiller_id'];
        } else {
            $conseillerId = $user->id;
        }

        $client = Client::create([
            'conseiller_id' => $conseillerId,
            'prenom'        => $data['prenom'],
            'nom'           => $data['nom'],
            'tel'           => $data['tel'],
            'email'         => $data['email'] ?? null,
            'adresse'       => $data['adresse'] ?? null,
            'bt'            => $data['bt'] ?? null,
            'rgpd'          => true,
            'notes'         => $data['notes'] ?? null,
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', "Le client {$client->nom_complet} a été créé avec succès (code : {$client->code}).");
    }

    public function show(Request $request, Client $client): View
    {
        $this->authorizeClientAccess($request->user(), $client);

        $client->load('conseiller', 'questionnaire');

        return view('clients.show', compact('client'));
    }

    public function edit(Request $request, Client $client): View
    {
        $this->authorizeClientAccess($request->user(), $client);

        $conseillers  = [];
        $isSuperAdmin = $request->user()->role === Role::SuperAdmin;

        if ($isSuperAdmin) {
            $conseillers = User::conseillers()->where('active', true)->orderBy('name')->get();
        }

        return view('clients.edit', compact('client', 'conseillers', 'isSuperAdmin'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $data = $request->validated();

        if ($request->user()->role === Role::SuperAdmin && !empty($data['conseiller_id'])) {
            $data['conseiller_id'] = $data['conseiller_id'];
        } else {
            unset($data['conseiller_id']);
        }

        $client->update($data);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Le client a été mis à jour avec succès.');
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $nomComplet = $client->nom_complet;
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Le client {$nomComplet} a été supprimé.");
    }

    public function anonymize(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClientAccess($request->user(), $client);

        $client->update([
            'prenom'     => 'Anonymisé',
            'nom'        => 'Anonymisé',
            'tel'        => null,
            'email'      => null,
            'adresse'    => null,
            'bt'         => null,
            'notes'      => null,
            'sexe'       => null,
            'sentinelles' => null,
            'age'        => null,
            'taille'     => null,
            'poids'      => null,
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Les données personnelles du client ont été anonymisées.');
    }

    private function authorizeClientAccess(\App\Models\User $user, Client $client): void
    {
        if ($user->role !== Role::SuperAdmin && $client->conseiller_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à ce client.');
        }
    }
}
