<?php

namespace App\Services;

use App\Data\QuestionnaireData;

class QuestionnaireScorer
{
    public function calculate(array $answers): array
    {
        $scores = [
            'metabolique' => $this->scoreMetabolique($answers),
            'ayurveda'    => $this->scoreAyurveda($answers),
            'julia_ross'  => $this->scoreJuliaRoss($answers),
            'diathese'    => $this->scoreDiathese($answers),
            'hormones'    => $this->scoreHormones($answers),
            'canaris'     => $this->scoreCanaris($answers),
        ];

        $scores['metabolic_type'] = $this->interpretMetabolic($scores['metabolique']);
        $scores['ayurveda_type']  = $this->interpretAyurveda($scores['ayurveda']);

        return $scores;
    }

    public function interpretMetabolic(array $scores): string
    {
        $chasseur  = $scores['chasseur']  ?? 0;
        $cueilleur = $scores['cueilleur'] ?? 0;
        $mixte     = $scores['mixte']     ?? 0;

        if ($chasseur >= $cueilleur + 5 && $chasseur >= $mixte + 5) {
            return 'Chasseur';
        }

        if ($cueilleur >= $chasseur + 5 && $cueilleur >= $mixte + 5) {
            return 'Cueilleur';
        }

        return 'Mixte';
    }

    public function interpretAyurveda(array $scores): string
    {
        $doshas = [
            'Vata'  => $scores['vata']  ?? 0,
            'Pitta' => $scores['pitta'] ?? 0,
            'Kapha' => $scores['kapha'] ?? 0,
        ];

        arsort($doshas);
        $sorted = array_keys($doshas);
        $vals   = array_values($doshas);

        [$first, $second] = $sorted;
        [$s1, $s2, $s3]           = $vals;

        if (($s1 - $s3) <= 12) {
            return 'Tridosha';
        }

        if (($s1 - $s2) <= 12) {
            return "{$first}-{$second}";
        }

        return $first;
    }

    private function scoreMetabolique(array $answers): array
    {
        $met_a = 0;
        $met_b = 0;
        $met_m = 0;

        // Mapping des symptômes ms* vers leur colonne correcte (source : Taty Lauwers)
        $msMapping = [
            'ms2'  => 'B',  // Cicatrices, marques
            'ms3'  => 'M',  // Oppression thoracique / Toux
            'ms5'  => 'M',  // Peau crevasse
            'ms6'  => 'A',  // Chair de poule
            'ms7'  => 'B',  // Gencives saignent
            'ms9'  => 'M',  // Démangeaisons de peau
            'ms10' => 'M',  // Éternuements
            'ms11' => 'M',  // Respiration sifflante
        ];

        // Détection du format : v1 si des clés mb1..mb37 sont présentes
        $isV1 = collect(array_keys($answers))->contains(
            fn ($k) => (bool) preg_match('/^mb\d+$/', $k)
        );

        if ($isV1) {
            // Format v1 : mb1..mb37 valeur 'a'/'b' + symptômes ms* valeur '1'
            foreach ($answers as $key => $value) {
                if (preg_match('/^mb\d+$/', $key)) {
                    if ($value === 'a') $met_a++;
                    elseif ($value === 'b') $met_b++;
                }
                if (isset($msMapping[$key]) && $value == '1') {
                    match ($msMapping[$key]) {
                        'A' => $met_a++,
                        'B' => $met_b++,
                        'M' => $met_m++,
                    };
                }
            }
        } else {
            // Format v2 : mb_XX_A / mb_XX_B / mb_XX_M
            foreach ($answers as $key => $value) {
                if (! $value) continue;
                if (preg_match('/^mb_\d+_A$/', $key)) $met_a++;
                elseif (preg_match('/^mb_\d+_B$/', $key)) $met_b++;
                elseif (preg_match('/^mb_\d+_M$/', $key)) $met_m++;
            }
        }

        $met_type = match(true) {
            $met_a >= $met_b + 5 && $met_a >= $met_m + 5 => 'Cueilleur A',
            $met_b >= $met_a + 5 && $met_b >= $met_m + 5 => 'Chasseur B',
            default                                        => 'Mixte',
        };

        return [
            'a'         => $met_a,
            'b'         => $met_b,
            'm'         => $met_m,
            'cueilleur' => $met_a,
            'chasseur'  => $met_b,
            'mixte'     => $met_m,
            'type'      => $met_type,
        ];
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
        $profil = $answers['ctx1'] ?? 'adulte';

        $sous_sections = match($profil) {
            'adulte'   => ['adulte'],
            'enfant'   => ['enfant'],
            'les_deux' => ['adulte', 'enfant'],
            default    => ['adulte'],
        };

        $score = 0;
        foreach ($sous_sections as $section) {
            $items = $section === 'enfant'
                ? QuestionnaireData::$canaris_enfant
                : QuestionnaireData::$canaris_adulte;
            foreach ($items as $q) {
                if (!empty($answers[$q['id']])) {
                    $score += $q['poids'];
                }
            }
        }

        foreach (['ctx2', 'ctx3', 'ctx4'] as $ctx) {
            if (($answers[$ctx] ?? null) === 'oui') {
                $score += 2;
            }
        }

        $grade = match(true) {
            $score >= 12 => 'grade_3',
            $score >= 8  => 'grade_2',
            $score >= 5  => 'grade_1',
            default      => 'non_canari',
        };

        $familles = ['additifs'];
        if (($answers['ctx5'] ?? null) === 'souvent') {
            $familles[] = 'amines';
        }
        if (($answers['ctx7'] ?? null) !== 'non') {
            $familles[] = 'cosmetiques';
        }

        $contexte = [];
        foreach (QuestionnaireData::$canaris_contexte as $q) {
            $contexte[$q['id']] = $answers[$q['id']] ?? null;
        }

        return [
            'score'    => $score,
            'grade'    => $grade,
            'profil'   => $profil,
            'familles' => $familles,
            'contexte' => $contexte,
        ];
    }
}
