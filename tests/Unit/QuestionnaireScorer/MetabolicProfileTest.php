<?php

namespace Tests\Unit\QuestionnaireScorer;

use App\Data\QuestionnaireData;
use App\Services\QuestionnaireScorer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Règle : seuls Cueilleur (A) et Chasseur (B) sont comparés.
 * |A − B| ≥ 5 → le plus élevé des deux, sinon Mixte. Le score Mixte (M) n'intervient pas.
 */
class MetabolicProfileTest extends TestCase
{
    public static function cases(): array
    {
        return [
            'A=5 B=16 M=19 → Chasseur (cas Patrick Lerch)' => [5, 16, 19, 'Chasseur'],
            'A=16 B=5 M=19 → Cueilleur'                    => [16, 5, 19, 'Cueilleur'],
            'A=10 B=14 M=20 → Mixte (écart 4)'             => [10, 14, 20, 'Mixte'],
            'A=10 B=15 → Chasseur (écart exactement 5)'    => [10, 15, 0, 'Chasseur'],
            'A=15 B=10 → Cueilleur'                        => [15, 10, 0, 'Cueilleur'],
            'A=0 B=0 M=0 → Mixte'                          => [0, 0, 0, 'Mixte'],
        ];
    }

    #[DataProvider('cases')]
    public function test_interpret_metabolic(int $a, int $b, int $m, string $expected): void
    {
        $result = (new QuestionnaireScorer())->interpretMetabolic([
            'cueilleur' => $a,
            'chasseur'  => $b,
            'mixte'     => $m,
        ]);

        $this->assertSame($expected, $result);
    }

    #[DataProvider('cases')]
    public function test_calcul_complet_depuis_les_reponses(int $a, int $b, int $m, string $expected): void
    {
        $scores = (new QuestionnaireScorer())->calculate($this->answers($a, $b, $m));

        $this->assertSame([$a, $b, $m], [
            $scores['metabolique']['a'],
            $scores['metabolique']['b'],
            $scores['metabolique']['m'],
        ]);
        $this->assertSame($expected, $scores['metabolic_type']);
        $this->assertSame(
            ['Cueilleur' => 'Cueilleur A', 'Chasseur' => 'Chasseur B', 'Mixte' => 'Mixte'][$expected],
            $scores['metabolique']['type']
        );
    }

    public function test_seuil_est_une_constante_nommee(): void
    {
        $this->assertSame(5, QuestionnaireScorer::METABOLIC_GAP_THRESHOLD);
    }

    /** Coche $n réponses dans chaque colonne (format v2 mb_XX_COL). */
    private function answers(int $a, int $b, int $m): array
    {
        $answers = [];
        $wanted  = ['A' => $a, 'B' => $b, 'M' => $m];

        foreach ($wanted as $col => $n) {
            foreach (QuestionnaireData::$metabolique as $q) {
                if ($n === 0) {
                    break;
                }
                if ($q['options'][$col] !== null) {
                    $answers[$q['id'] . '_' . $col] = '1';
                    $n--;
                }
            }
        }

        return $answers;
    }
}
