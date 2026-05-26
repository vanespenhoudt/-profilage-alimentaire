<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    // ── Admin : envoyer une invitation ───────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email', 'unique:invitations,email'],
        ], [
            'email.unique' => "Un compte ou une invitation existe déjà pour cette adresse email.",
        ]);

        Invitation::create([
            'email' => strtolower($request->email),
            'token' => Str::random(48),
        ]);

        return redirect()
            ->route('admin.conseillers.index')
            ->with('success', "Invitation envoyée à {$request->email}. Copiez le lien ci-dessous.");
    }

    public function destroy(Invitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return redirect()
            ->route('admin.conseillers.index')
            ->with('success', "Invitation supprimée.");
    }

    // ── Public : inscription via lien ────────────────────────────────────────

    public function show(string $token): View|RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isUsed()) {
            return redirect()->route('login')
                ->with('error', "Ce lien d'invitation a déjà été utilisé. Connectez-vous directement.");
        }

        return view('auth.invitation', compact('invitation', 'token'));
    }

    public function register(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->isUsed()) {
            return redirect()->route('login')
                ->with('error', "Ce lien d'invitation a déjà été utilisé.");
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $invitation->email,
            'password' => Hash::make($request->password),
            'role'     => Role::Conseiller,
            'active'   => true,
        ]);

        $invitation->used_at = now();
        $invitation->save();

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', "Bienvenue {$user->name} ! Votre compte conseiller est actif.");
    }
}
