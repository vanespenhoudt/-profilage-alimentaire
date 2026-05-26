<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->role === Role::SuperAdmin) {
            $totalClients     = Client::count();
            $totalConseillers = User::conseillers()->where('active', true)->count();
            $clientsRecents   = Client::with('conseiller')
                ->latest()
                ->take(10)
                ->get();

            return view('dashboard', compact('totalClients', 'totalConseillers', 'clientsRecents'));
        }

        $totalClients   = $user->clients()->count();
        $clientsRecents = $user->clients()->latest()->take(10)->get();

        return view('dashboard', compact('totalClients', 'clientsRecents'));
    }
}
