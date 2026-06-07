<?php

namespace App\Console\Commands;

use App\Models\Questionnaire;
use App\Services\QuestionnaireScorer;
use Illuminate\Console\Command;

/**
 * Convertit les réponses Métabolique v1 (mb1='a', ms1='1', …)
 * au format v2 (mb_01_A='1', mb_03_B='1', …) et recalcule les scores.
 *
 * Usage : php artisan metabolique:migrate-v2 [--dry-run]
 */
class MigrateMetaboliqueV2 extends Command
{
    protected $signature   = 'metabolique:migrate-v2 {--dry-run : Aperçu sans modification}';
    protected $description = 'Migre les réponses Métabolique v1 → v2 et recalcule les scores';

    /**
     * Correspondance ancienne question binaire → nouvelle clé v2.
     * Format : 'ancien_id' => ['nouveau_id', 'colonne_A' => 'A', 'colonne_B' => 'B']
     */
    private const BINARY_MAP = [
        'mb1'  => ['id' => 'mb_01', 'a' => 'A', 'b' => 'B'],
        'mb2'  => ['id' => 'mb_02', 'a' => 'A', 'b' => 'B'],
        'mb3'  => ['id' => 'mb_04', 'a' => 'A', 'b' => 'B'], // Climat (Oppression thoracique mb_03 inséré)
        'mb4'  => ['id' => 'mb_06', 'a' => 'A', 'b' => 'B'], // Appétit
        'mb5'  => ['id' => 'mb_10', 'a' => 'A', 'b' => 'B'], // Digestion
        'mb6'  => ['id' => 'mb_11', 'a' => 'A', 'b' => 'B'], // Nourriture
        'mb7'  => ['id' => 'mb_12', 'a' => 'A', 'b' => 'B'], // Émotions expression
        'mb8'  => ['id' => 'mb_13', 'a' => 'A', 'b' => 'B'], // Émotions profil
        'mb9'  => ['id' => 'mb_14', 'a' => 'A', 'b' => 'B'], // Yeux
        'mb10' => ['id' => 'mb_15', 'a' => 'A', 'b' => 'B'], // Coloration visage
        'mb11' => ['id' => 'mb_16', 'a' => 'A', 'b' => 'B'], // Teint
        'mb12' => ['id' => 'mb_17', 'a' => 'A', 'b' => 'B'], // Aliments gras
        'mb13' => ['id' => 'mb_18', 'a' => 'A', 'b' => 'B'], // Réaction gras
        'mb14' => ['id' => 'mb_19', 'a' => 'A', 'b' => 'B'], // Ongles
        'mb15' => ['id' => 'mb_20', 'a' => 'A', 'b' => 'B'], // 4h sans manger
        'mb16' => ['id' => 'mb_23', 'a' => 'A', 'b' => 'B'], // Gencives couleur
        'mb17' => ['id' => 'mb_24', 'a' => 'A', 'b' => 'B'], // Sensation de faim
        'mb18' => ['id' => 'mb_25', 'a' => 'A', 'b' => 'B'], // Piqûres insectes
        'mb19' => ['id' => 'mb_28', 'a' => 'A', 'b' => 'B'], // Jeûne
        'mb20' => ['id' => 'mb_29', 'a' => 'A', 'b' => 'B'], // Portions
        'mb21' => ['id' => 'mb_30', 'a' => 'A', 'b' => 'B'], // Jus d'orange
        'mb22' => ['id' => 'mb_31', 'a' => 'A', 'b' => 'B'], // Pommes de terre
        'mb23' => ['id' => 'mb_32', 'a' => 'A', 'b' => 'B'], // Viande rouge
        'mb24' => ['id' => 'mb_33', 'a' => 'A', 'b' => 'B'], // Salive quantité
        'mb25' => ['id' => 'mb_34', 'a' => 'A', 'b' => 'B'], // Salive texture
        'mb26' => ['id' => 'mb_35', 'a' => 'A', 'b' => 'B'], // Aliments salés
        'mb27' => ['id' => 'mb_36', 'a' => 'A', 'b' => 'B'], // Cicatrisation
        'mb28' => ['id' => 'mb_37', 'a' => 'A', 'b' => 'B'], // Peau
        'mb29' => ['id' => 'mb_38', 'a' => 'A', 'b' => 'B'], // Sauter repas
        'mb30' => ['id' => 'mb_39', 'a' => 'A', 'b' => 'B'], // Collations
        'mb31' => ['id' => 'mb_41', 'a' => 'A', 'b' => 'B'], // Aliments acides
        'mb32' => ['id' => 'mb_42', 'a' => 'A', 'b' => 'B'], // Sucreries
        'mb33' => ['id' => 'mb_43', 'a' => 'A', 'b' => 'B'], // Repas végétarien
        'mb34' => ['id' => 'mb_45', 'a' => 'A', 'b' => 'B'], // Protéines petit-déjeuner
        'mb35' => ['id' => 'mb_46', 'a' => 'A', 'b' => 'B'], // Protéines midi
        'mb36' => ['id' => 'mb_47', 'a' => 'A', 'b' => 'B'], // Coup de pouce PM
        'mb37' => ['id' => 'mb_48', 'a' => 'A', 'b' => 'B'], // En société
    ];

    /**
     * Correspondance anciens symptômes → nouvelle clé v2.
     * Valeur '1' → colonne indiquée (B ou A selon le PDF).
     */
    private const SYMPTOME_MAP = [
        'ms1'  => 'mb_03_B', // Oppression thoracique → B
        'ms2'  => 'mb_05_B', // Cicatrices → B
        'ms3'  => 'mb_07_B', // Toux → B
        'ms4'  => 'mb_08_B', // Pellicules → B
        'ms5'  => 'mb_09_B', // Peau crevasse → B
        'ms6'  => 'mb_21_A', // Chair de poule → A (PDF)
        'ms7'  => 'mb_22_B', // Gencives saignent → B
        'ms8'  => 'mb_26_B', // Irritation des yeux → B
        'ms9'  => 'mb_27_B', // Démangeaisons de peau → B
        'ms10' => 'mb_40_B', // Éternuements → B
        'ms11' => 'mb_44_B', // Respiration sifflante → B
    ];

    public function handle(): int
    {
        $dryRun    = $this->option('dry-run');
        $migrated  = 0;
        $skipped   = 0;
        $scorer    = new QuestionnaireScorer();

        $this->info($dryRun ? '--- DRY RUN (aucune modification) ---' : 'Migration en cours…');

        Questionnaire::whereNotNull('answers')->chunk(100, function ($questionnaires) use (
            $dryRun, $scorer, &$migrated, &$skipped
        ) {
            foreach ($questionnaires as $q) {
                $answers = $q->answers ?? [];

                // Vérifier si des données v1 sont présentes
                $hasV1 = collect(array_keys($answers))->contains(
                    fn ($k) => preg_match('/^mb\d+$/', $k) || preg_match('/^ms\d+$/', $k)
                );

                // Vérifier si des données v2 sont déjà présentes
                $hasV2 = collect(array_keys($answers))->contains(
                    fn ($k) => str_starts_with($k, 'mb_')
                );

                if (! $hasV1 || $hasV2) {
                    $skipped++;
                    continue;
                }

                // Convertir les questions binaires
                $newAnswers = $answers;
                foreach (self::BINARY_MAP as $oldId => $map) {
                    $val = $answers[$oldId] ?? null;
                    if ($val === 'a') {
                        $newAnswers[$map['id'] . '_A'] = '1';
                    } elseif ($val === 'b') {
                        $newAnswers[$map['id'] . '_B'] = '1';
                    }
                    unset($newAnswers[$oldId]);
                }

                // Convertir les symptômes
                foreach (self::SYMPTOME_MAP as $oldId => $newKey) {
                    if (! empty($answers[$oldId])) {
                        $newAnswers[$newKey] = '1';
                    }
                    unset($newAnswers[$oldId]);
                }

                if ($dryRun) {
                    $this->line("  Q#{$q->id} client#{$q->client_id} : v1 → v2 ({$q->client->nom_complet})");
                    $migrated++;
                    continue;
                }

                $q->answers    = $newAnswers;
                $q->scores     = $scorer->calculate($newAnswers);
                $q->updated_at = $q->updated_at; // ne pas changer le timestamp
                $q->saveQuietly();

                $migrated++;
            }
        });

        $this->info("Migrés : {$migrated} | Ignorés (pas v1 ou déjà v2) : {$skipped}");

        return self::SUCCESS;
    }
}
