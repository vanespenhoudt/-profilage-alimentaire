<?php

namespace App\Console\Commands;

use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;
use Illuminate\Console\Command;

/**
 * Recalcule uniquement le profil métabolique stocké (scores.metabolique.type et
 * scores.metabolic_type) avec la règle |A − B| ≥ seuil. Aucun autre score n'est modifié.
 *
 * Usage : php artisan metabolique:recalculer-profil [--dry-run]
 */
class RecalculerProfilMetabolique extends Command
{
    protected $signature   = 'metabolique:recalculer-profil
                                {--dry-run : Affiche les changements sans sauvegarder}';
    protected $description = 'Recalcule le profil métabolique (Cueilleur/Chasseur/Mixte) des bilans existants';

    public function handle(): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $rows    = [];
        $checked = 0;

        $this->info($dryRun ? '--- DRY RUN (rien n\'est enregistré) ---' : 'Recalcul en cours…');

        Questionnaire::with('client')->whereNotNull('scores')->orderBy('id')
            ->chunkById(100, function ($questionnaires) use ($dryRun, &$rows, &$checked) {
                foreach ($questionnaires as $q) {
                    $scores = $q->scores ?? [];
                    $met    = $scores['metabolique'] ?? null;

                    if (! is_array($met) || ! isset($met['a'], $met['b'])) {
                        continue;
                    }
                    $checked++;

                    $profil  = QuestionnaireScorer::metabolicProfile((int) $met['a'], (int) $met['b']);
                    $newType = ['Cueilleur' => 'Cueilleur A', 'Chasseur' => 'Chasseur B', 'Mixte' => 'Mixte'][$profil];
                    $oldType = $scores['metabolic_type'] ?? $met['type'] ?? '—';

                    if (($met['type'] ?? null) === $newType && ($scores['metabolic_type'] ?? null) === $profil) {
                        continue;
                    }

                    $rows[] = [
                        $q->client?->id ?? '—',
                        $q->client?->code ?? '—',
                        $q->client?->nom_complet ?? '—',
                        $q->session_label ?? ($q->is_active ? 'active' : 'archivée'),
                        "A={$met['a']} B={$met['b']} M=" . ($met['m'] ?? 0),
                        $oldType,
                        $profil,
                    ];

                    if (! $dryRun) {
                        $scores['metabolique']['type'] = $newType;
                        $scores['metabolic_type']      = $profil;
                        $q->scores = $scores;
                        $q->save();
                    }
                }
            });

        if ($rows === []) {
            $this->info("Aucun profil ne change ({$checked} bilans vérifiés).");
            return self::SUCCESS;
        }

        $this->table(['ID client', 'Code', 'Client', 'Session', 'Scores', 'Avant', 'Après'], $rows);
        $this->info(count($rows) . " bilan(s) " . ($dryRun ? 'à modifier' : 'modifié(s)') . " sur {$checked} vérifiés.");

        return self::SUCCESS;
    }
}
