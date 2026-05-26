<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConseillerRequest;
use App\Http\Requests\UpdateConseillerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ConseillerController extends Controller
{
    public function index(): View
    {
        $conseillers = User::conseillers()
            ->withCount('clients')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.conseillers.index', compact('conseillers'));
    }

    public function create(): View
    {
        return view('admin.conseillers.create');
    }

    public function store(StoreConseillerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => Role::Conseiller->value,
            'code'     => $data['code'] ?? null,
            'active'   => true,
        ]);

        return redirect()->route('admin.conseillers.index')
            ->with('success', 'Le conseiller a été créé avec succès.');
    }

    public function edit(User $user): View
    {
        return view('admin.conseillers.edit', compact('user'));
    }

    public function update(UpdateConseillerRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
            'code'  => $data['code'] ?? null,
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.conseillers.index')
            ->with('success', 'Le conseiller a été mis à jour avec succès.');
    }

    public function toggle(Request $request, User $user): RedirectResponse
    {
        $user->update(['active' => !$user->active]);

        $statut = $user->active ? 'activé' : 'désactivé';

        return redirect()->route('admin.conseillers.index')
            ->with('success', "Le compte de {$user->name} a été {$statut}.");
    }
}
