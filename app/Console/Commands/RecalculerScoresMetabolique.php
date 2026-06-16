<?php

namespace App\Console\Commands;

use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;
use Illuminate\Console\Command;

/**
 * Recalcule les scores métaboliques des questionnaires v1 existants
 * sans modifier les réponses stockées en base.
 *
 * Usage : php artisan metabolique:recalculer-scores [--dry-run] [--client=CLI-65066]
 */
class RecalculerScoresMetabolique extends Command
{
    protected $signature   = 'metabolique:recalculer-scores
                                {--dry-run : Affiche les changements sans sauvegarder}
                                {--client= : Recalculer uniquement pour ce code client (ex: CLI-65066)}';
    protected $description = 'Recalcule les scores métaboliques v1 avec le mapping Mixte corrigé';

    public function handle(): int
    {
        $dryRun     = $this->option('dry-run');
        $clientCode = $this->option('client');
        $scorer     = new QuestionnaireScorer();
        $updated    = 0;
        $skipped    = 0;

        $this->info($dryRun ? '--- DRY RUN ---' : 'Recalcul en cours…');

        $query = Questionnaire::whereNotNull('answers');

        if ($clientCode) {
            $query->whereHas('client', fn ($q) => $q->where('code', $clientCode));
        }

        $query->chunk(100, function ($questionnaires) use (
            $dryRun, $scorer, &$updated, &$skipped
        ) {
            foreach ($questionnaires as $q) {
                $answers = $q->answers ?? [];

                // Traiter uniquement les questionnaires v1
                $isV1 = collect(array_keys($answers))->contains(
                    fn ($k) => (bool) preg_match('/^mb\d+$/', $k)
                );

                if (! $isV1) {
                    $skipped++;
                    continue;
                }

                $newScores = $scorer->calculate($answers);
                $oldMet    = ($q->scores ?? [])['metabolique'] ?? null;
                $newMet    = $newScores['metabolique'];

                $changed = $oldMet === null
                    || $oldMet['a'] !== $newMet['a']
                    || $oldMet['b'] !== $newMet['b']
                    || ($oldMet['m'] ?? 0) !== $newMet['m'];

                $client = $q->client;
                $label  = ($client?->nom_complet ?? "Q#{$q->id}") . " (Q#{$q->id})";

                if ($dryRun) {
                    if ($changed) {
                        $this->line("  {$label}");
                        $this->line("    Avant : A={$oldMet['a']} B={$oldMet['b']} M=" . ($oldMet['m'] ?? 0) . " → {$oldMet['type']}");
                        $this->line("    Après : A={$newMet['a']} B={$newMet['b']} M={$newMet['m']} → {$newScores['metabolic_type']}");
                    }
                    $updated++;
                    continue;
                }

                if ($changed) {
                    $q->scores     = $newScores;
                    $q->updated_at = $q->updated_at; // préserver le timestamp
                    $q->saveQuietly();
                    $this->line("  ✓ {$label} : {$newScores['metabolic_type']} (A={$newMet['a']} B={$newMet['b']} M={$newMet['m']})");
                }

                $updated++;
            }
        });

        $this->info("Traités : {$updated} | Ignorés (v2 ou sans réponses) : {$skipped}");

        return self::SUCCESS;
    }
}
