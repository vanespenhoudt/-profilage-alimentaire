<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Votre compte a été désactivé. Veuillez contacter un administrateur.');
        }

        $userRole = $user->role instanceof \App\Enums\Role
            ? $user->role->value
            : $user->role;

        if (!empty($roles) && !in_array($userRole, $roles, true)) {
            return redirect()->route('dashboard')
                ->with('error', 'Accès non autorisé.');
        }

        return $next($request);
    }
}
