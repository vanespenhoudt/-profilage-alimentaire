<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role === Role::SuperAdmin) {
            return redirect()->route('admin.conseillers.index');
        }

        $totalClients   = $user->clients()->count();
        $clientsRecents = $user->clients()->latest()->take(10)->get();

        return view('dashboard', compact('totalClients', 'clientsRecents'));
    }
}
