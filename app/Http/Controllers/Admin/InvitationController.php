<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InvitationController extends Controller
{
    // ── Envoyer une invitation (admin ou conseiller) ─────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'unique:users,email', 'unique:invitations,email'],
        ], [
            'email.unique' => "Un compte ou une invitation existe déjà pour cette adresse email.",
        ]);

        $invitation = Invitation::create([
            'email'      => strtolower($request->email),
            'token'      => Str::uuid()->toString(),
            'invited_by' => auth()->id(),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        $redirect = auth()->user()->role === Role::SuperAdmin
            ? route('admin.conseillers.index')
            : route('dashboard');

        return redirect($redirect)->with('success', "Invitation envoyée à {$invitation->email}.");
    }

    public function destroy(Invitation $invitation): RedirectResponse
    {
        $user = auth()->user();

        if ($user->role !== Role::SuperAdmin && $invitation->invited_by !== $user->id) {
            abort(403);
        }

        $invitation->delete();

        $redirect = $user->role === Role::SuperAdmin
            ? route('admin.conseillers.index')
            : route('dashboard');

        return redirect($redirect)->with('success', "Invitation supprimée.");
    }

    // ── Public : inscription via lien ────────────────────────────────────────

    public function show(string $token): View
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation) {
            return view('auth.invitation-error', ['reason' => 'invalid']);
        }

        if ($invitation->isUsed()) {
            return view('auth.invitation-error', ['reason' => 'used']);
        }

        if ($invitation->isExpired()) {
            return view('auth.invitation-error', ['reason' => 'expired']);
        }

        return view('auth.invitation', compact('invitation', 'token'));
    }

    public function register(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->first();

        if (!$invitation || $invitation->isUsed() || $invitation->isExpired()) {
            return redirect()->route('login')
                ->with('error', "Ce lien d'invitation est invalide ou expiré.");
        }

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'password'  => ['required', 'confirmed', Password::defaults()],
            'politique' => ['accepted'],
        ], [
            'politique.accepted' => "Vous devez accepter la politique de confidentialité pour créer votre compte.",
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
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', "Bienvenue {$user->name} ! Votre compte conseiller est actif.");
    }
}
