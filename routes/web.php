<?php

use App\Http\Controllers\Admin\ConseillerController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestionnaireController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'role'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Clients
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

    // Questionnaires
    Route::get('/clients/{client}/questionnaire', [QuestionnaireController::class, 'show'])->name('questionnaire.show');
    Route::post('/clients/{client}/questionnaire', [QuestionnaireController::class, 'store'])->name('questionnaire.store');
    Route::get('/clients/{client}/bilan', [QuestionnaireController::class, 'bilan'])->name('questionnaire.bilan');

    // Admin - Conseillers
    Route::middleware('role:super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/conseillers', [ConseillerController::class, 'index'])->name('conseillers.index');
        Route::get('/conseillers/create', [ConseillerController::class, 'create'])->name('conseillers.create');
        Route::post('/conseillers', [ConseillerController::class, 'store'])->name('conseillers.store');
        Route::get('/conseillers/{user}/edit', [ConseillerController::class, 'edit'])->name('conseillers.edit');
        Route::put('/conseillers/{user}', [ConseillerController::class, 'update'])->name('conseillers.update');
        Route::patch('/conseillers/{user}/toggle', [ConseillerController::class, 'toggle'])->name('conseillers.toggle');
    });
});

require __DIR__.'/auth.php';
