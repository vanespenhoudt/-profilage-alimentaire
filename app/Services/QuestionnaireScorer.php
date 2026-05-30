<?php

namespace App\Services;

use App\Data\QuestionnaireData;

class QuestionnaireScorer
{
    public function calculate(array $answers): array
    {
        return [
            'metabolique' => $this->scoreMetabolique($answers),
            'ayurveda'    => $this->scoreAyurveda($answers),
            'julia_ross'  => $this->scoreJuliaRoss($answers),
            'diathese'    => $this->scoreDiathese($answers),
            'hormones'    => $this->scoreHormones($answers),
            'canaris'     => $this->scoreCanaris($answers),
        ];
    }

    private function scoreMetabolique(array $answers): array
    {
        $met_a = 0;
        $met_b = 0;

        foreach (QuestionnaireData::$metabolique_binaire as $q) {
            $val = $answers[$q['id']] ?? null;
            if ($val === 'a') {
                $met_a++;
            } elseif ($val === 'b') {
                $met_b++;
            }
        }

        foreach (QuestionnaireData::$metabolique_symptomes as $q) {
            if (!empty($answers[$q['id']])) {
                $met_b++;
            }
        }

        $diff = abs($met_a - $met_b);
        $met_type = $diff >= 5
            ? ($met_a > $met_b ? 'Cueilleur A' : 'Chasseur B')
            : 'Mixte';

        return ['a' => $met_a, 'b' => $met_b, 'type' => $met_type];
    }

    private function scoreAyurveda(array $answers): array
    {
        $vata = 0;
        $pitta = 0;
        $kapha = 0;

        foreach (QuestionnaireData::$vata as $i => $_) {
            $vata += (int) ($answers['v' . $i] ?? 0);
        }

        foreach (QuestionnaireData::$pitta as $i => $_) {
            $pitta += (int) ($answers['p' . $i] ?? 0);
        }

        foreach (QuestionnaireData::$kapha as $i => $_) {
            $kapha += (int) ($answers['k' . $i] ?? 0);
        }

        return ['vata' => $vata, 'pitta' => $pitta, 'kapha' => $kapha];
    }

    private function scoreJuliaRoss(array $answers): array
    {
        $scores = [];

        foreach (QuestionnaireData::$julia_ross as $classe) {
            $total = 0;
            foreach ($classe['questions'] as $qi => $q) {
                if (!empty($answers[$classe['id'] . '_' . $qi])) {
                    $total += $q['w'];
                }
            }
            $scores[$classe['id']] = [
                'total'   => $total,
                'seuil'   => $classe['seuil'],
                'depasse' => $total > $classe['seuil'],
            ];
        }

        return $scores;
    }

    private function scoreDiathese(array $answers): array
    {
        $c1_d1 = 0;
        $c1_d2 = 0;
        $c2_d1 = 0;
        $c2_d2 = 0;

        foreach (QuestionnaireData::$diathese_col1 as $q) {
            $val = $answers[$q['id']] ?? null;
            if ($val === 'd1') {
                $c1_d1++;
            } elseif ($val === 'd2') {
                $c1_d2++;
            }
        }

        foreach (QuestionnaireData::$diathese_col2 as $q) {
            $val = $answers[$q['id']] ?? null;
            if ($val === 'd1') {
                $c2_d1++;
            } elseif ($val === 'd2') {
                $c2_d2++;
            }
        }

        return [
            'c1_d1' => $c1_d1,
            'c1_d2' => $c1_d2,
            'c2_d1' => $c2_d1,
            'c2_d2' => $c2_d2,
        ];
    }

    private function scoreHormones(array $answers): array
    {
        $scores = [];

        foreach (QuestionnaireData::$hormones as $cat) {
            $total = 0;
            foreach ($cat['questions'] as $qi => $_) {
                if (!empty($answers[$cat['id'] . '_' . $qi])) {
                    $total++;
                }
            }
            $scores[$cat['id']] = ['total' => $total, 'max' => $cat['max']];
        }

        return $scores;
    }

    private function scoreCanaris(array $answers): array
    {
        $profil = $answers['ctx_profil'] ?? 'adulte';
        $profils = $profil === 'les_deux'
            ? ['adulte', 'enfant']
            : [$profil === 'enfant' ? 'enfant' : 'adulte'];

        $score = 0;
        foreach ($profils as $p) {
            foreach (QuestionnaireData::$canaris[$p] as $q) {
                if (!empty($answers[$q['id']])) {
                    $score += $q['poids'];
                }
            }
        }

        $niveau = match(true) {
            $score >= 10 => 'probable',
            $score >= 5  => 'suspicion',
            default      => 'non_canari',
        };

        $contexte = [];
        foreach (QuestionnaireData::$canaris_contexte as $q) {
            $contexte[$q['id']] = $answers[$q['id']] ?? null;
        }

        return [
            'score'   => $score,
            'niveau'  => $niveau,
            'profil'  => $profil,
            'contexte'=> $contexte,
        ];
    }
}
